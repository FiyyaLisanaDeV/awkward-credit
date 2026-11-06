<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Services\Import\PembayaranCsvImporter;
use App\Support\Konstanta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ImportController extends Controller
{
    public function index(Request $request): View
    {
        $batch = ImportBatch::query()
            ->with('user')
            ->where('cabang_id', $request->user()?->cabang_id)
            ->latest()
            ->paginate((int) config('loan.pagination.per_page'))
            ->withQueryString();

        return view('import.index', ['batch' => $batch]);
    }

    public function create(): View
    {
        return view('import.create');
    }

    public function store(Request $request, PembayaranCsvImporter $importer): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:'.((int) config('import.max_file_mb') * Konstanta::KILOBYTE)],
        ]);

        $batch = $importer->preview($data['file'], $request->user());

        return redirect()->route('import.show', $batch)->with('status', __('import.preview'));
    }

    public function show(Request $request, ImportBatch $batch): View
    {
        abort_unless($batch->cabang_id === $request->user()?->cabang_id, 403);

        return view('import.show', [
            'batch' => $batch->load('baris'),
        ]);
    }
}
