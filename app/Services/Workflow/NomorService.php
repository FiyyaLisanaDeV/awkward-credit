<?php

declare(strict_types=1);

namespace App\Services\Workflow;

use App\Models\Cabang;
use Illuminate\Support\Facades\DB;

final class NomorService
{
    public function permohonan(Cabang $cabang): string
    {
        $tahun = now()->format('Y');
        $prefix = $cabang->kode.'/'.$tahun.'/';

        $terakhir = DB::table('permohonan_kredit')
            ->where('no_permohonan', 'like', $prefix.'%')
            ->lockForUpdate()
            ->count();

        $padding = (int) config('loan.nomor.urut_padding');

        return $prefix.str_pad((string) ($terakhir + 1), $padding, '0', STR_PAD_LEFT);
    }
}
