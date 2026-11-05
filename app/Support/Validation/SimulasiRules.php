<?php

declare(strict_types=1);

namespace App\Support\Validation;

use App\Enums\ProdukTipe;
use App\Enums\SistemSukuBunga;
use Illuminate\Validation\Rule;

final class SimulasiRules
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:100'],
            'produk_tipe' => ['required', Rule::enum(ProdukTipe::class)],
            'sistem_suku_bunga' => ['required', Rule::enum(SistemSukuBunga::class)],
            'plafon' => ['required', 'numeric', 'gt:0'],
            'rate' => ['required', 'numeric', 'gt:0', 'lte:0.5'],
            'tenor' => ['required', 'integer', 'between:1,360'],
            'surplus_bulanan' => ['required', 'numeric', 'min:0'],
            'penghasilan_bersih' => ['nullable', 'numeric', 'min:0'],
            'nilai_jaminan' => ['nullable', 'numeric', 'min:0'],
            'biaya_operasional_tahunan' => ['nullable', 'numeric', 'min:0'],
            'periode_perputaran_hari' => ['nullable', 'numeric', 'min:0'],
            'pembelian_bulanan' => ['nullable', 'numeric', 'min:0'],
            'hari_persediaan' => ['nullable', 'numeric', 'min:0'],
            'mk_tersedia' => ['nullable', 'numeric', 'min:0'],
            'permohonan_id' => ['nullable', 'uuid', 'exists:permohonan_kredit,id'],
            'debitur_id' => ['nullable', 'uuid', 'exists:debitur,id'],
        ];
    }
}
