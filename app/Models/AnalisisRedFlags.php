<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisisRedFlags extends Model
{
    use HasUuids;

    protected $table = 'analisis_red_flags';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'parameter_kebijakan_id',
        'kode',
        'level',
        'pesan',
        'sumber',
        'teratasi',
        'mitigasi',
        'pemilik_mitigasi',
        'tenggat',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'teratasi' => 'boolean',
            'tenggat' => 'date',
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
