<?php

declare(strict_types=1);

namespace App\Services\Kredit;

use App\Enums\LedgerEvent;
use App\Enums\SistemSukuBunga;
use App\Enums\StatusKredit;
use App\Enums\StatusPermohonan;
use App\Models\Cabang;
use App\Models\DebiturAset;
use App\Models\JadwalAngsuran;
use App\Models\Kredit;
use App\Models\MemoPencairan;
use App\Models\PermohonanKredit;
use App\Models\User;
use App\Services\Calculation\AngsuranCalculator;
use App\Services\Ledger\LedgerService;
use App\Services\Notifikasi\NotifikasiService;
use App\Services\Register\BukuBesarService;
use App\Services\Workflow\NomorService;
use App\Support\Terbilang;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class KreditService
{
    public function __construct(
        private readonly NomorService $nomor,
        private readonly LedgerService $ledger,
        private readonly NotifikasiService $notifikasi,
        private readonly AngsuranCalculator $angsuran,
        private readonly Terbilang $terbilang,
        private readonly BukuBesarService $bukuBesar,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function cairkan(PermohonanKredit $permohonan, User $user, array $data = []): Kredit
    {
        $status = (string) $permohonan->status;

        abort_if(
            ! in_array($status, [StatusPermohonan::APPROVED->value, StatusPermohonan::SIGNED->value], true),
            409,
            'Permohonan belum disetujui.',
        );

        abort_if($permohonan->kredit()->exists(), 409, 'Kredit sudah dicairkan.');

        return DB::transaction(function () use ($permohonan, $user, $data): Kredit {
            $cabang = Cabang::query()->findOrFail($permohonan->cabang_id);
            $sistem = SistemSukuBunga::from((string) $permohonan->sistem_suku_bunga);
            $plafon = (float) ($permohonan->plafon_rekomendasi ?? $permohonan->plafon_diajukan);
            $rate = (float) $permohonan->suku_bunga_tahunan;
            $tenor = (int) ($permohonan->tenor_rekomendasi ?? $permohonan->tenor_diajukan);
            $tanggalCair = Carbon::parse((string) ($data['tanggal_cair'] ?? now()->toDateString()));
            $rekening = (string) ($data['rekening_pencairan'] ?? '');
            $jadwal = $this->angsuran->generateJadwal($plafon, $rate, $tenor, $sistem, $tanggalCair);

            $kredit = Kredit::query()->create([
                'cabang_id' => $permohonan->cabang_id,
                'permohonan_id' => $permohonan->id,
                'no_rekening_kredit' => $this->nomor->kredit($cabang),
                'no_akad' => $data['no_akad'] ?? null,
                'tanggal_akad' => $data['tanggal_akad'] ?? $tanggalCair->toDateString(),
                'tanggal_cair' => $tanggalCair->toDateString(),
                'plafon' => $plafon,
                'rate' => $rate,
                'tenor' => $tenor,
                'sistem_suku_bunga' => $sistem->value,
                'angsuran_bulanan' => $jadwal[0]['total_angsuran'] ?? 0,
                'total_bunga' => array_sum(array_column($jadwal, 'bunga')),
                'total_pembayaran' => array_sum(array_column($jadwal, 'total_angsuran')),
                'sumber_pelunasan_balloon' => $permohonan->sumber_pelunasan_balloon,
                'rekening_pencairan' => $rekening,
                'tujuan_penggunaan' => $permohonan->tujuan_penggunaan,
                'sektor' => $permohonan->sektor,
                'status' => StatusKredit::ACTIVE->value,
            ]);

            foreach ($jadwal as $baris) {
                JadwalAngsuran::query()->create([
                    'kredit_id' => $kredit->id,
                    'periode' => $baris['periode'],
                    'tanggal_jatuh_tempo' => $baris['tanggal_jatuh_tempo'],
                    'saldo_awal' => $baris['saldo_awal'],
                    'angsuran_pokok' => $baris['angsuran_pokok'],
                    'bunga' => $baris['bunga'],
                    'total_angsuran' => $baris['total_angsuran'],
                    'saldo_akhir' => $baris['saldo_akhir'],
                ]);
            }

            $debitur = $permohonan->debitur;
            $atasNama = $debitur === null ? '' : (string) $debitur->nama_ktp;

            MemoPencairan::query()->create([
                'kredit_id' => $kredit->id,
                'tanggal_memo' => $tanggalCair->toDateString(),
                'dana_bersih' => $plafon,
                'terbilang' => $this->terbilang->rupiah($plafon),
                'rekening_tujuan' => $rekening,
                'atas_nama' => $atasNama,
            ]);

            $permohonan->update(['status' => StatusPermohonan::ACTIVE->value]);

            $this->ledger->record(
                LedgerEvent::AkadDicatat,
                'kredit',
                (string) $kredit->id,
                ['no_rekening_kredit' => $kredit->no_rekening_kredit, 'plafon' => $plafon, 'tenor' => $tenor],
                $user,
            );

            $this->ledger->record(
                LedgerEvent::PencairanDicatat,
                'kredit',
                (string) $kredit->id,
                ['dana_bersih' => $plafon, 'rekening' => $rekening],
                $user,
            );

            $this->bukuBesar->catat('REALISASI', $permohonan, $user, 'Kredit dicairkan', [
                'kredit' => $kredit->only([
                    'id', 'no_rekening_kredit', 'tanggal_cair', 'plafon', 'rate',
                    'tenor', 'sistem_suku_bunga', 'angsuran_bulanan', 'status',
                ]),
                'agunan' => DebiturAset::query()
                    ->where('debitur_id', $permohonan->debitur_id)
                    ->get()
                    ->map(static fn (DebiturAset $aset): array => $aset->only([
                        'id', 'jenis_aset', 'no_dokumen', 'nama_pemilik',
                        'nilai_taksasi', 'persen_diperhitungkan', 'nilai_diperhitungkan',
                    ]))
                    ->all(),
            ], $kredit);

            $this->notifikasi->kirim(
                (int) $permohonan->ao_id,
                'KREDIT',
                'Kredit dicairkan',
                'Kredit '.$kredit->no_rekening_kredit.' telah dicairkan.',
                'kredit',
                (string) $kredit->id,
                (string) $kredit->cabang_id,
            );

            return $kredit->fresh() ?? $kredit;
        });
    }
}
