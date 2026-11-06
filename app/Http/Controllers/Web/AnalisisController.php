<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\ScopesByCabang;
use App\Http\Controllers\Controller;
use App\Models\PermohonanKredit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class AnalisisController extends Controller
{
    use ScopesByCabang;

    public function show(Request $request, PermohonanKredit $permohonan): View
    {
        $this->ensureSameCabang($permohonan, $request->user());

        return view('analisis.workbench', ['permohonan' => $permohonan]);
    }
}
