<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefKategoriAgunanPojk extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'ref_kategori_agunan_pojk';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'nama',
        'persen_max',
        'pasal_pojk',
        'catatan',
        'sumber',
        'effective_date',
        'is_active',
        'approved_by',
        'approved_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'persen_max' => 'decimal:4',
            'effective_date' => 'date',
            'is_active' => 'boolean',
            'approved_by' => 'integer',
            'approved_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
