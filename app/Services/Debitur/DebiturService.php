<?php

declare(strict_types=1);

namespace App\Services\Debitur;

use App\Enums\SumberData;
use App\Models\Debitur;
use App\Models\User;

final class DebiturService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $user): Debitur
    {
        return Debitur::query()->create(array_merge($data, [
            'cabang_id' => $user->cabang_id,
            'sumber_data' => SumberData::Manual->value,
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Debitur $debitur, array $data): Debitur
    {
        $debitur->update($data);

        return $debitur->fresh() ?? $debitur;
    }
}
