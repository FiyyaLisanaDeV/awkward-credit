<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisisRekomendasi extends Model
{
    use HasUuids;

    protected $table = 'analisis_rekomendasi';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'parameter_kebijakan_id',
        'rekomendasi_sistem',
        'narasi',
        'plafon_final',
        'tenor_final',
        'rate_final',
        'angsuran_final',
        'syarat',
        'covenant',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'plafon_final' => 'decimal:2',
            'tenor_final' => 'integer',
            'rate_final' => 'decimal:4',
            'angsuran_final' => 'decimal:2',
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
