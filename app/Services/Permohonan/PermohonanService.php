<?php

declare(strict_types=1);

namespace App\Services\Permohonan;

use App\Enums\LedgerEvent;
use App\Enums\StatusPermohonan;
use App\Exceptions\BusinessRuleViolation;
use App\Models\Cabang;
use App\Models\Debitur;
use App\Models\DebiturAset;
use App\Models\PermohonanKredit;
use App\Models\User;
use App\Services\Ledger\LedgerService;
use App\Services\Policy\PolicyParameterResolver;
use App\Services\Register\BukuAgunanService;
use App\Services\Register\BukuBesarService;
use App\Services\Rules\BusinessRuleEngine;
use App\Services\Workflow\NomorService;
use Illuminate\Support\Facades\DB;

final class PermohonanService
{
    public function __construct(
        private readonly NomorService $nomor,
        private readonly PolicyParameterResolver $parameters,
        private readonly LedgerService $ledger,
        private readonly BusinessRuleEngine $rules,
        private readonly BukuBesarService $bukuBesar,
        private readonly BukuAgunanService $bukuAgunan,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $user): PermohonanKredit
    {
        $debitur = Debitur::query()->findOrFail((string) $data['debitur_id']);
        /** @var Debitur $debitur */
        $cabang = Cabang::query()->findOrFail($debitur->cabang_id);
        $parameter = $this->parameters->active();

        return DB::transaction(function () use ($data, $cabang, $parameter, $user, $debitur): PermohonanKredit {
            $permohonan = PermohonanKredit::query()->create(array_merge($data, [
                'cabang_id' => $cabang->id,
                'no_permohonan' => $this->nomor->permohonan($cabang),
                'parameter_kebijakan_id' => $parameter->id,
                'parameter_snapshot' => $parameter->toArray(),
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

            $this->bukuBesar->catat('INISIASI', $permohonan, $user, 'Permohonan dibuat', [
                'debitur' => $this->ringkasDebitur($debitur),
                'permohonan' => $this->ringkasPermohonan($permohonan),
            ]);

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

        $results = $this->rules->validateStage('submit', $permohonan);
        $hard = $this->rules->hardViolations($results);

        if ($hard !== []) {
            $first = $hard[0];

            throw new BusinessRuleViolation($first['kode'], $first['result']->message);
        }

        $permohonan->update(['status' => StatusPermohonan::SUBMITTED->value]);

        $this->ledger->record(
            LedgerEvent::PermohonanDiajukan,
            'permohonan_kredit',
            (string) $permohonan->id,
            ['status' => $permohonan->status],
            $user,
        );

        $debitur = $permohonan->debitur;

        $this->bukuBesar->catat('PENGAJUAN', $permohonan, $user, 'Permohonan diajukan', [
            'debitur' => $debitur === null ? null : $this->ringkasDebitur($debitur),
            'permohonan' => $this->ringkasPermohonan($permohonan),
        ]);

        DebiturAset::query()
            ->where('debitur_id', $permohonan->debitur_id)
            ->get()
            ->each(fn (DebiturAset $aset) => $this->bukuAgunan->catatPenggunaan($aset, $permohonan, $user));

        return $permohonan->fresh() ?? $permohonan;
    }

    /**
     * @return array<string, mixed>
     */
    private function ringkasDebitur(Debitur $debitur): array
    {
        return $debitur->only([
            'id', 'cif', 'nama_ktp', 'no_ktp', 'no_hp', 'alamat_jalan',
            'kelurahan', 'kecamatan', 'kabupaten', 'provinsi',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function ringkasPermohonan(PermohonanKredit $permohonan): array
    {
        return $permohonan->only([
            'id', 'no_permohonan', 'plafon_diajukan', 'tenor_diajukan',
            'suku_bunga_tahunan', 'sistem_suku_bunga', 'tujuan_penggunaan',
            'sektor', 'status', 'tanggal_permohonan',
        ]);
    }
}
