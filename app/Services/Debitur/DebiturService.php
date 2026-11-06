<?php

declare(strict_types=1);

namespace App\Services\Debitur;

use App\Enums\SumberData;
use App\Models\Cabang;
use App\Models\Debitur;
use App\Models\User;
use App\Services\Workflow\NomorService;
use Illuminate\Support\Facades\DB;

final class DebiturService
{
    public function __construct(private readonly NomorService $nomor) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $user): Debitur
    {
        return DB::transaction(function () use ($data, $user): Debitur {
            $cabang = Cabang::query()->findOrFail($user->cabang_id);

            $debitur = Debitur::query()->create(array_merge($data, [
                'cabang_id' => $user->cabang_id,
                'cif' => $this->nomor->cif($cabang),
                'sumber_data' => SumberData::Manual->value,
            ]));

            return $debitur->fresh() ?? $debitur;
        });
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
