<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemoPencairan extends Model
{
    use HasUuids;

    protected $table = 'memo_pencairan';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'kredit_id',
        'no_memo',
        'tanggal_memo',
        'dana_bersih',
        'terbilang',
        'rekening_tujuan',
        'atas_nama',
        'status',
        'approved_by',
        'approved_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tanggal_memo' => 'date',
            'dana_bersih' => 'decimal:2',
            'approved_by' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<Kredit, $this> */
    public function kredit(): BelongsTo
    {
        return $this->belongsTo(Kredit::class, 'kredit_id');
    }
}
