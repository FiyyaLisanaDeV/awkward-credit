<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KreditAgunan extends Model
{
    use HasUuids;

    protected $table = 'kredit_agunan';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'kredit_id',
        'debitur_aset_id',
        'nilai_jaminan',
        'pengikatan',
        'no_pengikatan',
        'tanggal_ikatan',
        'status',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'nilai_jaminan' => 'decimal:2',
            'tanggal_ikatan' => 'date',
        ];
    }

    /** @return BelongsTo<DebiturAset, $this> */
    public function debiturAset(): BelongsTo
    {
        return $this->belongsTo(DebiturAset::class, 'debitur_aset_id');
    }

    /** @return BelongsTo<Kredit, $this> */
    public function kredit(): BelongsTo
    {
        return $this->belongsTo(Kredit::class, 'kredit_id');
    }
}
