<?php

declare(strict_types=1);

use App\Enums\SettingSection;
use App\Models\RefChecklistDokumen;
use App\Models\RefParameterKebijakan;
use App\Services\Setting\Validators\ApprovalMatrixValidator;
use App\Services\Setting\Validators\PolicyParameterValidator;
use App\Services\Setting\Validators\ReferenceRowValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('menolak total bobot 5C yang tidak sama dengan 1', function () {
    $active = RefParameterKebijakan::query()->where('status', 'ACTIVE')->firstOrFail();
    $params = $active->params;
    $params['bobot_character'] = 0.50;

    expect(fn () => app(PolicyParameterValidator::class)->validate(['params' => $params, 'effective_date' => '2026-06-01']))
        ->toThrow(ValidationException::class);
});

it('menolak urutan rating yang tidak menurun', function () {
    $active = RefParameterKebijakan::query()->where('status', 'ACTIVE')->firstOrFail();
    $params = $active->params;
    $params['rating_layak_min'] = 4.5;

    expect(fn () => app(PolicyParameterValidator::class)->validate(['params' => $params, 'effective_date' => '2026-06-01']))
        ->toThrow(ValidationException::class);
});

it('menolak band matriks yang memiliki celah', function () {
    $matrix = [
        ['plafon_min' => 0, 'plafon_max' => 10000000, 'level' => 1, 'role' => 'AO'],
        ['plafon_min' => 20000000, 'plafon_max' => null, 'level' => 2, 'role' => 'KOMITE'],
    ];

    expect(fn () => app(ApprovalMatrixValidator::class)->validate(['matrix' => $matrix, 'effective_date' => '2026-06-01']))
        ->toThrow(ValidationException::class);
});

it('menolak level duplikat pada band yang sama', function () {
    $matrix = [
        ['plafon_min' => 0, 'plafon_max' => 10000000, 'level' => 1, 'role' => 'AO'],
        ['plafon_min' => 0, 'plafon_max' => 10000000, 'level' => 1, 'role' => 'KOMITE'],
        ['plafon_min' => 10000000, 'plafon_max' => null, 'level' => 2, 'role' => 'KOMITE'],
    ];

    expect(fn () => app(ApprovalMatrixValidator::class)->validate(['matrix' => $matrix, 'effective_date' => '2026-06-01']))
        ->toThrow(ValidationException::class);
});

it('menolak persen agunan di luar rentang 0-1', function () {
    $payload = [
        'nama' => 'Uji',
        'persen_max' => 1.2,
        'pasal_pojk' => 'Pasal 1',
        'sumber' => 'Uji',
        'effective_date' => '2026-06-01',
    ];

    expect(fn () => app(ReferenceRowValidator::class)->validate(SettingSection::CollateralCategory, $payload))
        ->toThrow(ValidationException::class);
});

it('menolak kode checklist duplikat', function () {
    RefChecklistDokumen::query()->create([
        'kode' => 'UJI-01',
        'nama' => 'Dokumen Uji',
        'kategori' => 'PEMOHON',
        'wajib' => true,
        'urutan' => 1,
        'is_active' => true,
        'sumber' => 'Uji',
    ]);

    $payload = [
        'kode' => 'UJI-01',
        'nama' => 'Dokumen Uji 2',
        'kategori' => 'PEMOHON',
        'wajib' => true,
        'urutan' => 2,
        'is_active' => true,
        'sumber' => 'Uji',
    ];

    expect(fn () => app(ReferenceRowValidator::class)->validate(SettingSection::ChecklistDokumen, $payload))
        ->toThrow(ValidationException::class);
});
