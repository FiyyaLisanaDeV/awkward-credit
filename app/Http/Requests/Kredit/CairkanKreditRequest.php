<?php

declare(strict_types=1);

namespace App\Http\Requests\Kredit;

use Illuminate\Foundation\Http\FormRequest;

final class CairkanKreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('kredit.cairkan');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tanggal_cair' => ['required', 'date'],
            'rekening_pencairan' => ['required', 'string', 'max:50'],
            'no_akad' => ['nullable', 'string', 'max:50'],
        ];
    }
}
