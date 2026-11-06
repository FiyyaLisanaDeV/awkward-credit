<?php

declare(strict_types=1);

namespace App\Services\Register;

use App\Models\BukuAgunan;
use App\Models\DebiturAset;
use App\Models\Kredit;
use App\Models\PermohonanKredit;
use App\Models\User;
use App\Services\Ledger\ChainHasher;
use App\Support\Konstanta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class BukuAgunanService
{
    public function catatPenggunaan(
        DebiturAset $aset,
        PermohonanKredit $permohonan,
        User $aktor,
        ?Kredit $kredit = null,
        string $peristiwa = 'DIPAKAI',
    ): BukuAgunan {
        return DB::transaction(function () use ($aset, $permohonan, $aktor, $kredit, $peristiwa): BukuAgunan {
            DB::statement('SELECT pg_advisory_xact_lock(?)', [Konstanta::REGISTER_LOCK_BUKU_AGUNAN]);

            $key = $this->agunanKey($aset);
            $occurred = now();
            $prev = (string) (BukuAgunan::query()->orderByDesc('seq')->value('hash') ?? ChainHasher::genesis());

            $snapshot = [
                'aset' => $aset->only([
                    'id', 'jenis_aset', 'jenis_dokumen', 'no_dokumen', 'nama_pemilik',
                    'hubungan_pemohon', 'lokasi', 'luas_jumlah', 'satuan',
                    'nilai_taksasi', 'persen_diperhitungkan', 'nilai_diperhitungkan',
                ]),
                'permohonan' => $permohonan->only(['id', 'no_permohonan', 'plafon_diajukan', 'tujuan_penggunaan']),
                'kredit' => $kredit?->only(['id', 'no_rekening_kredit', 'plafon', 'tanggal_cair']),
            ];

            $payload = ['peristiwa' => $peristiwa, 'agunan_key' => $key, 'snapshot' => $snapshot];

            $hash = ChainHasher::hash(
                $prev,
                $payload,
                ChainHasher::time($occurred),
                $peristiwa,
                'buku_agunan',
                (string) $permohonan->id,
            );

            return BukuAgunan::query()->create([
                'agunan_key' => $key,
                'debitur_id' => $aset->debitur_id,
                'debitur_aset_id' => $aset->id,
                'permohonan_id' => $permohonan->id,
                'kredit_id' => $kredit?->id,
                'cabang_id' => $permohonan->cabang_id,
                'jenis_aset' => (string) $aset->jenis_aset,
                'no_dokumen' => $aset->no_dokumen,
                'nama_pemilik' => $aset->nama_pemilik,
                'nilai_taksasi' => $aset->nilai_taksasi,
                'persen_diperhitungkan' => $aset->persen_diperhitungkan,
                'nilai_diperhitungkan' => $aset->nilai_diperhitungkan,
                'peristiwa' => $peristiwa,
                'aktor_id' => $aktor->id,
                'aktor_nama' => $aktor->name,
                'occurred_at' => $occurred,
                'snapshot' => $snapshot,
                'prev_hash' => $prev,
                'hash' => $hash,
            ]);
        });
    }

    public function agunanKey(DebiturAset $aset): string
    {
        $noDokumen = strtoupper(trim((string) ($aset->no_dokumen ?? '')));

        $identity = $noDokumen !== ''
            ? 'DOK|'.$noDokumen
            : 'ASET|'.strtoupper(trim((string) $aset->jenis_aset))
                .'|'.strtoupper(trim((string) ($aset->nama_pemilik ?? '')))
                .'|'.trim((string) ($aset->lokasi ?? ''))
                .'|'.(string) $aset->luas_jumlah;

        return hash('sha256', $identity);
    }

    public function terpakaiLintasCabang(string $key): bool
    {
        return BukuAgunan::query()
            ->where('agunan_key', $key)
            ->where('peristiwa', 'DIPAKAI')
            ->distinct()
            ->count('cabang_id') > 1;
    }

    /** @return Collection<int, BukuAgunan> */
    public function riwayat(string $key): Collection
    {
        return BukuAgunan::query()
            ->with(['cabang', 'permohonan.kredit', 'kredit', 'aktor'])
            ->where('agunan_key', $key)
            ->orderBy('seq')
            ->get();
    }
}
