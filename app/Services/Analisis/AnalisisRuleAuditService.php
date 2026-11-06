<?php

declare(strict_types=1);

namespace App\Services\Analisis;

use App\Models\AuditLog;
use App\Models\PermohonanKredit;
use App\Models\User;
use App\Services\Rules\BusinessRuleEngine;
use Illuminate\Support\Carbon;

final class AnalisisRuleAuditService
{
    public function __construct(private readonly BusinessRuleEngine $rules) {}

    /**
     * @return list<array{kode: string, level: string, passed: bool, message: string}>
     */
    public function evaluate(PermohonanKredit $permohonan, ?User $actor = null): array
    {
        $results = $this->rules->validateStage('analisis', $permohonan);

        $ringkas = array_map(static fn (array $row): array => [
            'kode' => $row['kode'],
            'level' => $row['level'],
            'passed' => $row['result']->passed,
            'message' => $row['result']->message,
        ], $results);

        AuditLog::query()->create([
            'cabang_id' => $permohonan->cabang_id,
            'user_id' => $actor?->id,
            'entity_type' => 'permohonan_kredit',
            'entity_id' => $permohonan->id,
            'aksi' => 'ANALISIS_RULES',
            'old_value' => null,
            'new_value' => ['rules' => $ringkas],
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : request()->userAgent(),
            'timestamp' => Carbon::now(),
        ]);

        return $ringkas;
    }
}
