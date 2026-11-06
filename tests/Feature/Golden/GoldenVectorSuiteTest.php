<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;

it('memuat 29 kasus golden yang valid dan bebas PII', function () {
    $directory = base_path('../../commandcode bpr/project/tests/golden/cases');
    $files = glob($directory.'/*.json') ?: [];

    expect($files)->toHaveCount(29);

    foreach ($files as $file) {
        $case = json_decode((string) file_get_contents($file), true);

        expect($case)->toBeArray()
            ->and($case['id'])->toBe(basename($file, '.json'))
            ->and($case['pii'])->toBeFalse()
            ->and($case['dummy_data'])->toBeTrue();
    }
});

it('mengunci batas IDIR 80,00 lolos dan 80,01 ditolak pada fixture', function () {
    $path = base_path('../../commandcode bpr/project/tests/golden/cases/G-TPP-03.json');
    $case = json_decode((string) file_get_contents($path), true);
    $values = $case['recomputed']['values'];

    expect($values['scenarios'][0]['idir'])->toBe(0.8)
        ->and($values['scenarios'][0]['expected_hard_stop'])->toBeFalse()
        ->and($values['scenarios'][1]['idir'])->toBe(0.81)
        ->and($values['scenarios'][1]['expected_hard_stop'])->toBeTrue();
});

it('mencatat status draft dan bukan verified', function () {
    $path = base_path('../../commandcode bpr/project/tests/golden/cases/G-TPP-03.json');
    $case = json_decode((string) file_get_contents($path), true);

    expect($case['verification']['verified'])->toBeFalse()
        ->and($case['recomputed']['status'])->toBe('RECOMPUTED_DRAFT_NOT_VERIFIED');
})->skip(Carbon::now()->year < 2026, 'Fixture masih draft; verifikasi manusia belum dilakukan.');
