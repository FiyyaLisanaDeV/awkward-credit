<?php

declare(strict_types=1);

namespace App\Services\Kredit;

use App\Enums\LedgerEvent;
use App\Enums\MetodePembayaran;
use App\Models\JadwalAngsuran;
use App\Models\Kredit;
use App\Models\PembayaranAngsuran;
use App\Models\User;
use App\Services\Ledger\LedgerService;
use App\Support\Konstanta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class PembayaranService
{
    public function __construct(private readonly LedgerService $ledger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function catat(Kredit $kredit, User $user, array $data): PembayaranAngsuran
    {
        $periode = (int) $data['periode'];

        $jadwal = JadwalAngsuran::query()
            ->where('kredit_id', $kredit->id)
            ->where('periode', $periode)
            ->firstOrFail();

        abort_if($jadwal->status === Konstanta::JADWAL_SUDAH_BAYAR, 409, 'Periode ini sudah dibayar.');

        $denda = (float) ($data['denda'] ?? 0);
        $pokok = (float) $jadwal->angsuran_pokok;
        $bunga = (float) $jadwal->bunga;
        $total = $pokok + $bunga + $denda;
        $tanggalBayar = Carbon::parse((string) ($data['tanggal_bayar'] ?? now()->toDateString()));

        return DB::transaction(function () use ($kredit, $user, $jadwal, $data, $periode, $pokok, $bunga, $denda, $total, $tanggalBayar): PembayaranAngsuran {
            $pembayaran = PembayaranAngsuran::query()->create([
                'cabang_id' => $kredit->cabang_id,
                'kredit_id' => $kredit->id,
                'jadwal_angsuran_id' => $jadwal->id,
                'periode' => $periode,
                'tanggal_bayar' => $tanggalBayar->toDateString(),
                'pokok' => $pokok,
                'bunga' => $bunga,
                'denda' => $denda,
                'total' => $total,
                'metode' => MetodePembayaran::from((string) ($data['metode'] ?? MetodePembayaran::Tunai->value))->value,
                'no_bukti' => $data['no_bukti'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            $jadwal->update([
                'status' => Konstanta::JADWAL_SUDAH_BAYAR,
                'tanggal_bayar' => $tanggalBayar->toDateString(),
                'nominal_dibayar' => $total,
                'denda' => $denda,
            ]);

            $this->ledger->record(
                LedgerEvent::AngsuranDibayar,
                'kredit',
                (string) $kredit->id,
                ['periode' => $periode, 'pokok' => $pokok, 'bunga' => $bunga, 'denda' => $denda, 'total' => $total],
                $user,
                null,
                'angsuran:'.$kredit->id.':'.$periode,
            );

            return $pembayaran->fresh() ?? $pembayaran;
        });
    }

    public function reversal(PembayaranAngsuran $pembayaran, User $user, string $alasan): PembayaranAngsuran
    {
        abort_if($pembayaran->dibatalkan_at !== null, 409, 'Pembayaran sudah dibatalkan.');

        return DB::transaction(function () use ($pembayaran, $user, $alasan): PembayaranAngsuran {
            $reversal = PembayaranAngsuran::query()->create([
                'cabang_id' => $pembayaran->cabang_id,
                'kredit_id' => $pembayaran->kredit_id,
                'jadwal_angsuran_id' => $pembayaran->jadwal_angsuran_id,
                'periode' => $pembayaran->periode,
                'tanggal_bayar' => now()->toDateString(),
                'pokok' => $pembayaran->pokok,
                'bunga' => $pembayaran->bunga,
                'denda' => $pembayaran->denda,
                'total' => $pembayaran->total,
                'metode' => $pembayaran->metode,
                'keterangan' => $alasan,
                'sumber' => 'REVERSAL',
                'reversal_of' => $pembayaran->id,
                'dibatalkan_at' => now(),
            ]);

            $pembayaran->update(['dibatalkan_at' => now()]);

            if ($pembayaran->jadwal_angsuran_id !== null) {
                JadwalAngsuran::query()->whereKey($pembayaran->jadwal_angsuran_id)->update([
                    'status' => Konstanta::JADWAL_BELUM_BAYAR,
                    'tanggal_bayar' => null,
                    'nominal_dibayar' => 0,
                    'denda' => 0,
                ]);
            }

            $this->ledger->record(
                LedgerEvent::AngsuranDireversal,
                'kredit',
                (string) $pembayaran->kredit_id,
                ['pembayaran_id' => $pembayaran->id, 'alasan' => $alasan],
                $user,
            );

            return $reversal->fresh() ?? $reversal;
        });
    }
}
