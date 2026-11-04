<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisisSensitivitas extends Model
{
    use HasUuids;

    protected $table = 'analisis_sensitivitas';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'parameter_kebijakan_id',
        'urutan',
        'nama_skenario',
        'delta_omzet',
        'delta_biaya',
        'total_pendapatan',
        'total_pengeluaran_non_angsuran',
        'surplus_baru',
        'rpc_baru',
        'dscr',
        'status_layak',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'delta_omzet' => 'decimal:4',
            'delta_biaya' => 'decimal:4',
            'total_pendapatan' => 'decimal:2',
            'total_pengeluaran_non_angsuran' => 'decimal:2',
            'surplus_baru' => 'decimal:2',
            'rpc_baru' => 'decimal:2',
            'dscr' => 'decimal:4',
            'status_layak' => 'boolean',
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
