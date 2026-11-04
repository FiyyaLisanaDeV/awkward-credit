<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranAngsuran extends Model
{
    use HasUuids;

    protected $table = 'pembayaran_angsuran';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'cabang_id',
        'kredit_id',
        'jadwal_angsuran_id',
        'periode',
        'tanggal_bayar',
        'pokok',
        'bunga',
        'denda',
        'total',
        'metode',
        'no_bukti',
        'keterangan',
        'sumber',
        'dibatalkan_at',
        'reversal_of',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'jadwal_angsuran_id' => 'integer',
            'periode' => 'integer',
            'tanggal_bayar' => 'date',
            'pokok' => 'decimal:2',
            'bunga' => 'decimal:2',
            'denda' => 'decimal:2',
            'total' => 'decimal:2',
            'dibatalkan_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Cabang, $this> */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    /** @return BelongsTo<JadwalAngsuran, $this> */
    public function jadwalAngsuran(): BelongsTo
    {
        return $this->belongsTo(JadwalAngsuran::class, 'jadwal_angsuran_id');
    }

    /** @return BelongsTo<Kredit, $this> */
    public function kredit(): BelongsTo
    {
        return $this->belongsTo(Kredit::class, 'kredit_id');
    }

    /** @return BelongsTo<PembayaranAngsuran, $this> */
    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(PembayaranAngsuran::class, 'reversal_of');
    }
}
