<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalAngsuran extends Model
{
    protected $table = 'jadwal_angsuran';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    /** @var list<string> */
    protected $fillable = [
        'kredit_id',
        'periode',
        'tanggal_jatuh_tempo',
        'saldo_awal',
        'angsuran_pokok',
        'bunga',
        'total_angsuran',
        'saldo_akhir',
        'status',
        'tanggal_bayar',
        'nominal_dibayar',
        'denda',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'periode' => 'integer',
            'tanggal_jatuh_tempo' => 'date',
            'saldo_awal' => 'decimal:2',
            'angsuran_pokok' => 'decimal:2',
            'bunga' => 'decimal:2',
            'total_angsuran' => 'decimal:2',
            'saldo_akhir' => 'decimal:2',
            'tanggal_bayar' => 'date',
            'nominal_dibayar' => 'decimal:2',
            'denda' => 'decimal:2',
        ];
    }

    public function kredit(): BelongsTo
    {
        return $this->belongsTo(Kredit::class, 'kredit_id');
    }
}
