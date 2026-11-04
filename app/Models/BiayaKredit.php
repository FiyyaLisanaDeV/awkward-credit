<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiayaKredit extends Model
{
    use HasUuids;

    protected $table = 'biaya_kredit';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'kredit_id',
        'komponen',
        'nilai',
        'tipe',
        'rate',
        'keterangan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
            'rate' => 'decimal:4',
        ];
    }

    public function kredit(): BelongsTo
    {
        return $this->belongsTo(Kredit::class, 'kredit_id');
    }
}
