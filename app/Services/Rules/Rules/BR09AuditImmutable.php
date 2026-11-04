<?php

declare(strict_types=1);

namespace App\Services\Rules\Rules;

use App\Models\PermohonanKredit;
use App\Services\Rules\AbstractRule;
use App\Services\Rules\RuleResult;
use Illuminate\Support\Facades\DB;

final class BR09AuditImmutable extends AbstractRule
{
    public function kode(): string
    {
        return 'BR-09';
    }

    public function level(): string
    {
        return 'HARD';
    }

    public function validate(PermohonanKredit $permohonan): RuleResult
    {
        $function = DB::selectOne("select 1 as ada from pg_proc where proname = 'audit_log_immutable' limit 1");
        $trigger = DB::selectOne("select 1 as ada from pg_trigger where tgname = 'audit_log_immutable_trigger' limit 1");

        return $function !== null && $trigger !== null
            ? $this->pass('Audit log dan ledger dilindungi trigger immutable.')
            : $this->fail('Trigger immutable audit log tidak ditemukan.');
    }
}
