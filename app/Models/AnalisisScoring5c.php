<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisisScoring5c extends Model
{
    use HasUuids;

    protected $table = 'analisis_scoring_5c';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'parameter_kebijakan_id',
        'skor_character',
        'skor_capacity',
        'skor_capital',
        'skor_collateral',
        'skor_condition',
        'bobot_character',
        'bobot_capacity',
        'bobot_capital',
        'bobot_collateral',
        'bobot_condition',
        'skor_tertimbang',
        'skor_total',
        'rating',
        'overridden',
        'override_reason',
        'justifikasi_character',
        'justifikasi_capacity',
        'justifikasi_capital',
        'justifikasi_collateral',
        'justifikasi_condition',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'skor_character' => 'decimal:2',
            'skor_capacity' => 'decimal:2',
            'skor_capital' => 'decimal:2',
            'skor_collateral' => 'decimal:2',
            'skor_condition' => 'decimal:2',
            'bobot_character' => 'decimal:4',
            'bobot_capacity' => 'decimal:4',
            'bobot_capital' => 'decimal:4',
            'bobot_collateral' => 'decimal:4',
            'bobot_condition' => 'decimal:4',
            'skor_tertimbang' => 'decimal:4',
            'skor_total' => 'decimal:4',
            'overridden' => 'boolean',
        ];
    }

    public function parameterKebijakan(): BelongsTo
    {
        return $this->belongsTo(RefParameterKebijakan::class, 'parameter_kebijakan_id');
    }

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanKredit::class, 'permohonan_id');
    }
}
