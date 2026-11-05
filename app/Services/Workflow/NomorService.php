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
