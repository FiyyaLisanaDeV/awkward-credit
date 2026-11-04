<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ledger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

final class LedgerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Ledger::query();

        if (! $request->user()->hasRole('ADMIN')) {
            $query->where('cabang_id', $request->user()->cabang_id);
        }

        if ($event = $request->string('event')->toString()) {
            $query->where('event', $event);
        }

        if ($entityType = $request->string('entity_type')->toString()) {
            $query->where('entity_type', $entityType);
        }

        return response()->json($query->orderByDesc('seq')->paginate((int) config('loan.pagination.ledger_per_page')));
    }

    public function verify(): JsonResponse
    {
        $exit = Artisan::call('ledger:verify');
        $output = Artisan::output();

        return response()->json([
            'exit' => $exit,
            'output' => trim($output),
        ], $exit === 0 ? 200 : 500);
    }
}
