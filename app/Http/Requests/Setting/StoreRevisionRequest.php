<?php

declare(strict_types=1);

namespace App\Http\Requests\Setting;

use App\Enums\SettingAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('setting.edit');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::enum(SettingAction::class)],
            'target_id' => ['nullable', 'uuid'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payload' => ['nullable', 'array'],
        ];
    }
}
