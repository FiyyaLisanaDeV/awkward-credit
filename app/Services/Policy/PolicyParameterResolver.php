<?php

declare(strict_types=1);

namespace App\Services\Policy;

use App\Domain\Policy\PolicyParameters;
use App\Enums\StatusVersi;
use App\Exceptions\MissingPolicyParameter;
use App\Models\RefParameterKebijakan;

final class PolicyParameterResolver
{
    public function active(): PolicyParameters
    {
        $model = RefParameterKebijakan::query()
            ->where('status', StatusVersi::ACTIVE->value)
            ->first();

        if ($model === null) {
            throw new MissingPolicyParameter('Tidak ada parameter kebijakan berstatus ACTIVE.');
        }

        return PolicyParameters::fromModel($model);
    }
}
