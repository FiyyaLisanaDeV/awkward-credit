<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemblokiranTabungan extends Model
{
    use HasUuids;

    protected $table = 'pemblokiran_tabungan';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'kredit_id',
        'no_rekening',
        'nilai_blokir',
        'jangka_waktu_bulan',
        'alasan',
        'tanggal_blokir',
        'tanggal_buka',
        'status',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'nilai_blokir' => 'decimal:2',
            'jangka_waktu_bulan' => 'integer',
            'tanggal_blokir' => 'date',
            'tanggal_buka' => 'date',
        ];
    }

    public function kredit(): BelongsTo
    {
        return $this->belongsTo(Kredit::class, 'kredit_id');
    }
}
