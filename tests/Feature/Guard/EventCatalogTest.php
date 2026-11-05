<?php

declare(strict_types=1);

use App\Enums\LedgerEvent;

it('semua event ledger tercatat di katalog INVENTARIS', function () {
    $katalog = (string) file_get_contents(base_path('docs/INVENTARIS_DATA.md'));
    $hilang = [];

    foreach (LedgerEvent::cases() as $case) {
        if (! str_contains($katalog, $case->value)) {
            $hilang[] = $case->value;
        }
    }

    expect($hilang)->toBe([]);
});

it('nilai event ledger unik dan uppercase', function () {
    $values = array_map(static fn (LedgerEvent $case): string => $case->value, LedgerEvent::cases());

    expect(count($values))->toBe(count(array_unique($values)));

    foreach ($values as $value) {
        expect($value)->toMatch('/^[A-Z][A-Z_]*$/');
    }
});
