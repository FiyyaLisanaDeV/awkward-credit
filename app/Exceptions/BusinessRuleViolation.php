<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class BusinessRuleViolation extends DomainException
{
    public function __construct(
        public readonly string $kode,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
                'kode' => $this->kode,
            ], 422);
        }

        return back()->withErrors(['permohonan' => $this->getMessage()]);
    }
}
