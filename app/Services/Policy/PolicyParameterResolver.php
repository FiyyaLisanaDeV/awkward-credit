<?php

declare(strict_types=1);

namespace App\Services\Policy;

use App\Domain\Policy\PolicyParameters;
use App\Enums\StatusVersi;
use App\Exceptions\MissingPolicyParameter;
use App\Models\PermohonanKredit;
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

    public function forPermohonan(PermohonanKredit $permohonan): PolicyParameters
    {
        $snapshot = $permohonan->parameter_snapshot;

        if (is_array($snapshot) && $snapshot !== []) {
            return PolicyParameters::fromArray($snapshot);
        }

        if ($permohonan->parameter_kebijakan_id !== null) {
            $model = RefParameterKebijakan::query()->find($permohonan->parameter_kebijakan_id);

            if ($model instanceof RefParameterKebijakan) {
                return PolicyParameters::fromModel($model);
            }
        }

        throw new MissingPolicyParameter('Permohonan '.$permohonan->id.' tidak memiliki snapshot parameter kebijakan.');
    }
}
