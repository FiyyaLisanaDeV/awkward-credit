<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BukuBesarKredit extends Model
{
    use HasUuids;

    protected $table = 'buku_besar_kredit';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'kredit_id',
        'cabang_id',
        'tahap',
        'aktor_id',
        'aktor_nama',
        'aktor_role',
        'occurred_at',
        'ringkasan',
        'snapshot',
        'prev_hash',
        'hash',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'aktor_id' => 'integer',
            'occurred_at' => 'datetime',
            'snapshot' => 'array',
        ];
    }

    /** @return BelongsTo<PermohonanKredit, $this> */
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanKredit::class, 'permohonan_id');
    }

    /** @return BelongsTo<Kredit, $this> */
    public function kredit(): BelongsTo
    {
        return $this->belongsTo(Kredit::class, 'kredit_id');
    }

    /** @return BelongsTo<User, $this> */
    public function aktor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aktor_id');
    }

    /** @return BelongsTo<Cabang, $this> */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }
}
