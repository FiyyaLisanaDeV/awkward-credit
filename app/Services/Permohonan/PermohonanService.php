<?php

declare(strict_types=1);

namespace App\Services\Permohonan;

use App\Enums\LedgerEvent;
use App\Enums\StatusPermohonan;
use App\Exceptions\BusinessRuleViolation;
use App\Models\Cabang;
use App\Models\Debitur;
use App\Models\PermohonanKredit;
use App\Models\User;
use App\Services\Ledger\LedgerService;
use App\Services\Policy\PolicyParameterResolver;
use App\Services\Rules\Rules\BR04BalloonWajib;
use App\Services\Rules\Rules\BR08ParameterTersimpan;
use App\Services\Workflow\NomorService;
use Illuminate\Support\Facades\DB;

final class PermohonanService
{
    public function __construct(
        private readonly NomorService $nomor,
        private readonly PolicyParameterResolver $parameters,
        private readonly LedgerService $ledger,
        private readonly BR04BalloonWajib $br04,
        private readonly BR08ParameterTersimpan $br08,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $user): PermohonanKredit
    {
        $debitur = Debitur::query()->findOrFail($data['debitur_id']);
        $cabang = Cabang::query()->findOrFail($debitur->cabang_id);
        $parameter = $this->parameters->active();

        return DB::transaction(function () use ($data, $cabang, $parameter, $user): PermohonanKredit {
            $permohonan = PermohonanKredit::query()->create(array_merge($data, [
                'cabang_id' => $cabang->id,
                'no_permohonan' => $this->nomor->permohonan($cabang),
                'parameter_kebijakan_id' => $parameter->id,
                'ao_id' => $user->id,
                'status' => StatusPermohonan::DRAFT->value,
                'prioritas' => $data['prioritas'] ?? 'NORMAL',
                'pinjaman_ke' => $data['pinjaman_ke'] ?? 1,
            ]));

            $this->ledger->record(
                LedgerEvent::PermohonanDibuat,
                'permohonan_kredit',
                (string) $permohonan->id,
                [
                    'no_permohonan' => $permohonan->no_permohonan,
                    'debitur_id' => $permohonan->debitur_id,
                    'produk_id' => $permohonan->produk_id,
                    'plafon_diajukan' => (float) $permohonan->plafon_diajukan,
                ],
                $user,
            );

            return $permohonan;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PermohonanKredit $permohonan, array $data): PermohonanKredit
    {
        abort_unless($permohonan->status === StatusPermohonan::DRAFT->value, 409, __('permohonan.only_draft_editable'));

        $permohonan->update($data);

        return $permohonan->fresh() ?? $permohonan;
    }

    public function submit(PermohonanKredit $permohonan, User $user): PermohonanKredit
    {
        abort_unless($permohonan->status === StatusPermohonan::DRAFT->value, 409, __('permohonan.not_draft'));

        $balloon = $this->br04->validate($permohonan);

        if (! $balloon->passed) {
            throw new BusinessRuleViolation('BR-04', $balloon->message);
        }

        $parameter = $this->br08->validate($permohonan);

        if (! $parameter->passed) {
            throw new BusinessRuleViolation('BR-08', $parameter->message);
        }

        $permohonan->update(['status' => StatusPermohonan::SUBMITTED->value]);

        $this->ledger->record(
            LedgerEvent::PermohonanDiajukan,
            'permohonan_kredit',
            (string) $permohonan->id,
            ['status' => $permohonan->status],
            $user,
        );

        return $permohonan->fresh() ?? $permohonan;
    }
}
