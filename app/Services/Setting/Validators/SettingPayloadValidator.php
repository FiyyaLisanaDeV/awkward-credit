<?php

declare(strict_types=1);

namespace App\Services\Setting\Validators;

use App\Enums\SettingSection;

final class SettingPayloadValidator
{
    public function __construct(
        private readonly PolicyParameterValidator $policy,
        private readonly ApprovalMatrixValidator $matrix,
        private readonly ReferenceRowValidator $reference,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function validate(SettingSection $section, array $payload, ?string $targetId = null): void
    {
        match ($section) {
            SettingSection::PolicyParameter => $this->policy->validate($payload),
            SettingSection::ApprovalMatrix => $this->matrix->validate($payload),
            default => $this->reference->validate($section, $payload, $targetId),
        };
    }
}
