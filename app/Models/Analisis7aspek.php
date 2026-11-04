<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analisis7aspek extends Model
{
    use HasUuids;

    protected $table = 'analisis_7aspek';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'parameter_kebijakan_id',
        'aspek_manfaat',
        'aspek_teknis',
        'aspek_manajemen',
        'aspek_pemasaran',
        'aspek_keuangan',
        'aspek_jaminan',
        'kesimpulan',
        'usul',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
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
