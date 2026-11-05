<?php

declare(strict_types=1);

namespace App\Http\Requests\Permohonan;

use App\Support\Validation\PermohonanRules;
use Illuminate\Foundation\Http\FormRequest;

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
        return PermohonanRules::rules();
    }
}
