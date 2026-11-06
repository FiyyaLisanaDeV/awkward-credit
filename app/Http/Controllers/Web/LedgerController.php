<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ledger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

final class LedgerController extends Controller
{
    public function index(Request $request): View
    {
        $ledger = Ledger::query()
            ->with('actor')
            ->where('cabang_id', $request->user()?->cabang_id)
            ->orderByDesc('seq')
            ->paginate((int) config('loan.pagination.per_page'))
            ->withQueryString();

        return view('ledger.index', ['ledger' => $ledger]);
    }

    public function show(Request $request, Ledger $ledger): View
    {
        abort_unless($ledger->cabang_id === $request->user()?->cabang_id, 403);

        return view('ledger.show', ['row' => $ledger->load('actor')]);
    }

    public function verify(): View
    {
        $buffer = new BufferedOutput;
        $exit = Artisan::call('ledger:verify', [], $buffer);

        return view('ledger.verify', [
            'ok' => $exit === 0,
            'output' => trim((string) $buffer->fetch()),
        ]);
    }
}
