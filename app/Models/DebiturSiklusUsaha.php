<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebiturSiklusUsaha extends Model
{
    use HasUuids;

    protected $table = 'debitur_siklus_usaha';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'debitur_id',
        'frekuensi_belanja',
        'satuan_periode',
        'modal_kerja_per_siklus',
        'pendapatan_kotor_per_siklus',
        'biaya_operasional_per_siklus',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'frekuensi_belanja' => 'decimal:2',
            'modal_kerja_per_siklus' => 'decimal:2',
            'pendapatan_kotor_per_siklus' => 'decimal:2',
            'biaya_operasional_per_siklus' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Debitur, $this> */
    public function debitur(): BelongsTo
    {
        return $this->belongsTo(Debitur::class, 'debitur_id');
    }
}
