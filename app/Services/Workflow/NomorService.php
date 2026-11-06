<?php

declare(strict_types=1);

namespace App\Services\Workflow;

use App\Models\Cabang;
use Illuminate\Support\Facades\DB;

final class NomorService
{
    public function permohonan(Cabang $cabang): string
    {
        return $this->generate('permohonan', (string) $cabang->kode, (int) now()->format('Y'));
    }

    public function kredit(Cabang $cabang): string
    {
        return $this->generate('kredit', (string) $cabang->kode, (int) now()->format('Y'));
    }

    public function dokumen(Cabang $cabang, string $kode): string
    {
        return $this->generate('dokumen', (string) $cabang->kode, (int) now()->format('Y'), $kode);
    }

    public function generate(string $jenis, string $cabangKode, int $tahun, ?string $kodeDokumen = null): string
    {
        $cabang = strtoupper($cabangKode);
        $scope = $kodeDokumen !== null
            ? strtolower("{$jenis}:{$kodeDokumen}:{$cabang}:{$tahun}")
            : strtolower("{$jenis}:{$cabang}:{$tahun}");

        $seq = $this->next($scope);

        return match ($jenis) {
            'permohonan' => sprintf('%s/%d/%04d', $cabang, $tahun, $seq),
            'kredit' => sprintf('%s/%d/K/%04d', $cabang, $tahun, $seq),
            'dokumen' => sprintf('%s/%s/%d/%04d', strtoupper((string) $kodeDokumen), $cabang, $tahun, $seq),
            default => throw new \InvalidArgumentException("Jenis penomoran [{$jenis}] tidak dikenali."),
        };
    }

    public function cif(Cabang $cabang): string
    {
        $scope = 'CIF-'.strtoupper((string) $cabang->kode);
        $padding = (int) config('loan.nomor.cif_padding');
        $nomor = $this->next($scope);

        return 'CIF-'.strtoupper((string) $cabang->kode).'-'.str_pad((string) $nomor, $padding, '0', STR_PAD_LEFT);
    }

    private function next(string $scope): int
    {
        $row = DB::selectOne(
            'INSERT INTO nomor_sequence (scope, last_number, updated_at)
             VALUES (?, 1, CURRENT_TIMESTAMP)
             ON CONFLICT (scope) DO UPDATE
             SET last_number = nomor_sequence.last_number + 1, updated_at = CURRENT_TIMESTAMP
             RETURNING last_number',
            [$scope],
        );

        return (int) ($row->last_number ?? 1);
    }
}
