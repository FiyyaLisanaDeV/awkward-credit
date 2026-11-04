<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisisRasio extends Model
{
    use HasUuids;

    protected $table = 'analisis_rasio';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'parameter_kebijakan_id',
        'current_ratio',
        'quick_ratio',
        'cash_ratio',
        'debt_to_asset',
        'debt_to_equity',
        'equity_to_asset',
        'net_profit_margin',
        'roa_annualized',
        'roe_annualized',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'current_ratio' => 'decimal:4',
            'quick_ratio' => 'decimal:4',
            'cash_ratio' => 'decimal:4',
            'debt_to_asset' => 'decimal:4',
            'debt_to_equity' => 'decimal:4',
            'equity_to_asset' => 'decimal:4',
            'net_profit_margin' => 'decimal:4',
            'roa_annualized' => 'decimal:4',
            'roe_annualized' => 'decimal:4',
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
