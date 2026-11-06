<?php

declare(strict_types=1);

namespace App\Services\Setting\Validators;

use App\Enums\SettingSection;
use App\Services\Setting\SettingSectionRegistry;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class ReferenceRowValidator
{
    public function __construct(private readonly SettingSectionRegistry $registry) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function validate(SettingSection $section, array $payload, ?string $targetId = null): void
    {
        $rules = $this->registry->rules($section);

        if ($section === SettingSection::ChecklistDokumen) {
            $rules['kode'][] = Rule::unique('ref_checklist_dokumen', 'kode')->ignore($targetId);
        }

        if ($section === SettingSection::TemplateDokumen) {
            $rules['kode'][] = Rule::unique('ref_template_dokumen', 'kode')
                ->where('version', $payload['version'] ?? '')
                ->ignore($targetId);
        }

        if ($section === SettingSection::Produk) {
            $rules['kode'][] = Rule::unique('produk', 'kode')
                ->where('cabang_id', $payload['cabang_id'] ?? '')
                ->ignore($targetId);
        }

        Validator::make($payload, $rules)->validate();

        if ($section === SettingSection::CollateralCategory && (float) $payload['persen_max'] > 1.0) {
            throw ValidationException::withMessages([
                'persen_max' => 'Persen maksimum agunan tidak boleh melebihi 1.0 (100%).',
            ]);
        }
    }
}
