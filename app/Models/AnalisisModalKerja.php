<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisisModalKerja extends Model
{
    use HasUuids;

    protected $table = 'analisis_modal_kerja';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'parameter_kebijakan_id',
        'periode_perputaran_hari',
        'biaya_operasional_tahunan',
        'perputaran_kas_per_tahun',
        'kebutuhan_mk_cash_turnover',
        'pembelian_bulanan',
        'hari_persediaan',
        'kebutuhan_mk_wcd',
        'mk_tersedia',
        'rata_rata_kebutuhan_mk',
        'gap_mk',
        'omzet_bulanan',
        'kebutuhan_mk_persen_omzet',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'periode_perputaran_hari' => 'decimal:2',
            'biaya_operasional_tahunan' => 'decimal:2',
            'perputaran_kas_per_tahun' => 'decimal:4',
            'kebutuhan_mk_cash_turnover' => 'decimal:2',
            'pembelian_bulanan' => 'decimal:2',
            'hari_persediaan' => 'decimal:2',
            'kebutuhan_mk_wcd' => 'decimal:2',
            'mk_tersedia' => 'decimal:2',
            'rata_rata_kebutuhan_mk' => 'decimal:2',
            'gap_mk' => 'decimal:2',
            'omzet_bulanan' => 'decimal:2',
            'kebutuhan_mk_persen_omzet' => 'decimal:4',
        ];
    }

    /** @return BelongsTo<RefParameterKebijakan, $this> */
    public function parameterKebijakan(): BelongsTo
    {
        return $this->belongsTo(RefParameterKebijakan::class, 'parameter_kebijakan_id');
    }

    /** @return BelongsTo<PermohonanKredit, $this> */
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanKredit::class, 'permohonan_id');
    }
}
