<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\JenisDokumen;
use App\Enums\PihakSignature;
use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\PermohonanKredit;
use App\Services\Document\DocumentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class DokumenController extends Controller
{
    public function index(Request $request): View
    {
        $dokumen = Dokumen::query()
            ->with('permohonan.debitur')
            ->where('cabang_id', $request->user()?->cabang_id)
            ->latest('tanggal_dokumen')
            ->paginate((int) config('loan.pagination.per_page'))
            ->withQueryString();

        return view('dokumen.index', ['dokumen' => $dokumen]);
    }

    public function show(Request $request, Dokumen $dokumen): View
    {
        abort_unless($dokumen->cabang_id === $request->user()?->cabang_id, 403);

        return view('dokumen.show', [
            'dokumen' => $dokumen->load(['permohonan.debitur', 'template', 'signatures']),
        ]);
    }

    public function generate(Request $request, PermohonanKredit $permohonan, DocumentService $service): RedirectResponse
    {
        abort_unless($permohonan->cabang_id === $request->user()?->cabang_id, 403);

        $data = $request->validate([
            'jenis' => ['required', Rule::enum(JenisDokumen::class)],
        ]);

        $dokumen = $service->generate($permohonan, JenisDokumen::from((string) $data['jenis']), $request->user());

        return redirect()->route('dokumen.show', $dokumen)->with('status', __('documents.generate'));
    }

    public function sign(Request $request, Dokumen $dokumen, DocumentService $service): RedirectResponse
    {
        abort_unless($dokumen->cabang_id === $request->user()?->cabang_id, 403);

        $data = $request->validate([
            'pihak' => ['required', Rule::enum(PihakSignature::class)],
            'nama' => ['required', 'string', 'max:200'],
            'jabatan' => ['nullable', 'string', 'max:200'],
        ]);

        $service->sign(
            $dokumen,
            $request->user(),
            PihakSignature::from((string) $data['pihak']),
            (string) $data['nama'],
            $data['jabatan'] ?? null,
        );

        return back()->with('status', __('documents.sign'));
    }
}
