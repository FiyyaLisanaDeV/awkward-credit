<?php

declare(strict_types=1);

use App\Domain\Policy\PolicyParameters;
use App\Enums\PolicyParameterKey;
use App\Exceptions\MissingPolicyParameter;
use App\Models\RefParameterKebijakan;
use App\Services\Policy\PolicyParameterResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

it('membaca parameter kebijakan aktif', function () {
    $parameters = app(PolicyParameterResolver::class)->active();

    expect($parameters->version)->toBe('v1.0')
        ->and($parameters->float(PolicyParameterKey::MinimumDscr))->toBe(1.0)
        ->and($parameters->float(PolicyParameterKey::MaksimumLtv))->toBe(0.7)
        ->and($parameters->int(PolicyParameterKey::HariKerjaPerTahun))->toBe(360)
        ->and($parameters->bool(PolicyParameterKey::SkorBandDraft))->toBeTrue()
        ->and($parameters->array(PolicyParameterKey::SkorBandDscr))->toHaveCount(5);
});

it('melempar exception bila kunci parameter tidak ada', function () {
    $parameters = new PolicyParameters('uji', 'v0', []);

    expect(fn () => $parameters->float(PolicyParameterKey::MinimumDscr))
        ->toThrow(MissingPolicyParameter::class);
});

it('melempar exception bila parameter aktif tidak ada', function () {
    RefParameterKebijakan::query()->delete();

    expect(fn () => app(PolicyParameterResolver::class)->active())
        ->toThrow(MissingPolicyParameter::class);
});
