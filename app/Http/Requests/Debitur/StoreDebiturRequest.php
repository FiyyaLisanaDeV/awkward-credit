<?php

declare(strict_types=1);

namespace App\Http\Requests\Debitur;

use App\Support\Validation\DebiturRules;
use Illuminate\Foundation\Http\FormRequest;

final class StoreDebiturRequest extends FormRequest
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
        return DebiturRules::rules();
    }
}
