<?php

declare(strict_types=1);

namespace App\Services\Rules;

use App\Models\PermohonanKredit;
use App\Services\Rules\Rules\BR01SlikBermasalah;
use App\Services\Rules\Rules\BR02DscrMinimum;
use App\Services\Rules\Rules\BR03LtvMaksimum;
use App\Services\Rules\Rules\BR04BalloonWajib;
use App\Services\Rules\Rules\BR05ChecklistWajib;
use App\Services\Rules\Rules\BR08ParameterTersimpan;
use App\Services\Rules\Rules\BR10KewajaranPlafon;
use App\Services\Rules\Rules\BR11IdirHardStop;
use App\Services\Rules\Rules\BR11IdirTpp;
use App\Services\Rules\Rules\BR12StressTest;
use App\Services\Rules\Rules\BR13BalloonKritis;
use App\Services\Rules\Rules\BR14TanggalLahir;
use App\Services\Rules\Rules\BR15NikConsent;
use InvalidArgumentException;

final class BusinessRuleEngine
{
    /** @var list<class-string<BusinessRule>> */
    public const STAGE_SUBMIT = [
        BR04BalloonWajib::class,
        BR08ParameterTersimpan::class,
        BR14TanggalLahir::class,
        BR15NikConsent::class,
    ];

    /** @var list<class-string<BusinessRule>> */
    public const STAGE_ANALISIS = [
        BR01SlikBermasalah::class,
        BR02DscrMinimum::class,
        BR03LtvMaksimum::class,
        BR10KewajaranPlafon::class,
        BR11IdirTpp::class,
        BR11IdirHardStop::class,
        BR12StressTest::class,
        BR13BalloonKritis::class,
    ];

    /** @var list<class-string<BusinessRule>> */
    public const STAGE_APPROVE = [
        BR01SlikBermasalah::class,
        BR02DscrMinimum::class,
        BR03LtvMaksimum::class,
        BR05ChecklistWajib::class,
        BR11IdirHardStop::class,
        BR13BalloonKritis::class,
    ];

    /**
     * @param  iterable<BusinessRule>  $rules
     */
    public function __construct(private readonly iterable $rules = []) {}

    /**
     * @return list<array{kode: string, level: string, result: RuleResult}>
     */
    public function validateAll(PermohonanKredit $permohonan): array
    {
        $results = [];

        foreach ($this->rules as $rule) {
            $results[] = [
                'kode' => $rule->kode(),
                'level' => $rule->level(),
                'result' => $rule->validate($permohonan),
            ];
        }

        return $results;
    }

    /**
     * @return list<array{kode: string, level: string, result: RuleResult}>
     */
    public function validateStage(string $stage, PermohonanKredit $permohonan): array
    {
        $classes = match ($stage) {
            'submit' => self::STAGE_SUBMIT,
            'analisis' => self::STAGE_ANALISIS,
            'approve' => self::STAGE_APPROVE,
            default => throw new InvalidArgumentException('Tahap aturan tidak dikenal: '.$stage),
        };

        $results = [];

        foreach ($classes as $class) {
            /** @var BusinessRule $rule */
            $rule = app($class);
            $results[] = [
                'kode' => $rule->kode(),
                'level' => $rule->level(),
                'result' => $rule->validate($permohonan),
            ];
        }

        return $results;
    }

    /**
     * @param  list<array{kode: string, level: string, result: RuleResult}>  $results
     * @return list<array{kode: string, level: string, result: RuleResult}>
     */
    public function hardViolations(array $results): array
    {
        return array_values(array_filter(
            $results,
            static fn (array $row): bool => $row['level'] === 'HARD' && ! $row['result']->passed
        ));
    }

    /**
     * @param  list<array{kode: string, level: string, result: RuleResult}>  $results
     */
    public function hasHardViolations(array $results): bool
    {
        return $this->hardViolations($results) !== [];
    }
}
