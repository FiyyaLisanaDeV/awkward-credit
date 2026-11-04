<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebiturPasangan extends Model
{
    use HasUuids;

    protected $table = 'debitur_pasangan';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'debitur_id',
        'nama_ktp',
        'jenis_kelamin',
        'tanggal_lahir',
        'pekerjaan',
        'jenis_usaha',
        'gaji_pokok',
        'tpp',
        'penghasilan_lain',
        'no_ktp',
        'no_hp',
        'is_ahli_waris',
        'consent_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'gaji_pokok' => 'decimal:2',
            'tpp' => 'decimal:2',
            'penghasilan_lain' => 'decimal:2',
            'is_ahli_waris' => 'boolean',
            'consent_at' => 'datetime',
        ];
    }

    public function debitur(): BelongsTo
    {
        return $this->belongsTo(Debitur::class, 'debitur_id');
    }
}
