<?php

declare(strict_types=1);

namespace App\Http\Requests\Permohonan;

use App\Enums\SistemSukuBunga;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePermohonanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $grace = SistemSukuBunga::GracePeriod->value;

        return [
            'debitur_id' => ['required', 'uuid', 'exists:debitur,id'],
            'produk_id' => ['required', 'uuid', 'exists:produk,id'],
            'sektor' => ['nullable', 'string', 'max:100'],
            'tujuan_penggunaan' => ['nullable', 'string', 'max:200'],
            'plafon_diajukan' => ['required', 'numeric', 'gt:0'],
            'plafon_rekomendasi' => ['nullable', 'numeric', 'gt:0', 'lte:plafon_diajukan'],
            'suku_bunga_tahunan' => ['required', 'numeric', 'gt:0', 'lte:0.5'],
            'tenor_diajukan' => ['required', 'integer', 'between:1,360'],
            'tenor_rekomendasi' => ['nullable', 'integer', 'between:1,360'],
            'sistem_suku_bunga' => ['required', Rule::enum(SistemSukuBunga::class)],
            'kegunaan_pinjaman' => ['required', 'string'],
            'pinjaman_ke' => ['nullable', 'integer', 'min:1'],
            'sumber_pelunasan_balloon' => ['required_if:sistem_suku_bunga,'.$grace, 'nullable', 'string'],
            'perkiraan_penerimaan_balloon' => ['required_if:sistem_suku_bunga,'.$grace, 'nullable', 'numeric', 'min:0'],
            'sumber_pembayaran_angsuran' => ['nullable', 'string', 'max:100'],
            'tanggal_permohonan' => ['required', 'date'],
            'tanggal_target_realisasi' => ['nullable', 'date'],
            'prioritas' => ['nullable', 'string', 'max:10'],
            'catatan_ao' => ['nullable', 'string'],
        ];
    }
}
