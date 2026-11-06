<?php

declare(strict_types=1);

namespace App\Services\Workflow;

use App\Models\Cabang;
use Illuminate\Support\Facades\DB;

final class NomorService
{
    public function permohonan(Cabang $cabang): string
    {
        return $this->berikutnya('permohonan_kredit', 'no_permohonan', $cabang->kode.'/'.now()->format('Y').'/');
    }

    public function kredit(Cabang $cabang): string
    {
        return $this->berikutnya('kredit', 'no_rekening_kredit', $cabang->kode.'/'.now()->format('Y').'/K/');
    }

    public function dokumen(Cabang $cabang, string $kode): string
    {
        return $this->berikutnya('dokumen', 'nomor_dokumen', $kode.'/'.$cabang->kode.'/'.now()->format('Y').'/');
    }

    public function cif(Cabang $cabang): string
    {
        $scope = 'CIF-'.$cabang->kode;

        $row = DB::selectOne(
            'INSERT INTO nomor_sequence (scope, last_number, updated_at)
             VALUES (?, 1, now())
             ON CONFLICT (scope) DO UPDATE
             SET last_number = nomor_sequence.last_number + 1, updated_at = now()
             RETURNING last_number',
            [$scope],
        );

        $padding = (int) config('loan.nomor.cif_padding');
        $nomor = (int) ($row->last_number ?? 1);

        return 'CIF-'.$cabang->kode.'-'.str_pad((string) $nomor, $padding, '0', STR_PAD_LEFT);
    }

    private function berikutnya(string $table, string $column, string $prefix): string
    {
        /** @phpstan-ignore larastan.noUnnecessaryCollectionCall (PostgreSQL: FOR UPDATE not allowed with aggregate count; rows locked then counted) */
        $terakhir = DB::table($table)
            ->where($column, 'like', $prefix.'%')
            ->lockForUpdate()
            ->pluck('id')
            ->count();

        $padding = (int) config('loan.nomor.urut_padding');

        return $prefix.str_pad((string) ($terakhir + 1), $padding, '0', STR_PAD_LEFT);
    }
}
