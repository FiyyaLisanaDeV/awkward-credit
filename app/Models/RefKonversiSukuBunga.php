<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RefKonversiSukuBunga extends Model
{
    use HasUuids;

    protected $table = 'ref_konversi_suku_bunga';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'tenor_bulan',
        'kategori',
        'flat',
        'efektif',
        'anuitas',
        'effective_date',
        'sumber',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tenor_bulan' => 'integer',
            'flat' => 'decimal:6',
            'efektif' => 'decimal:6',
            'anuitas' => 'decimal:6',
            'effective_date' => 'date',
        ];
    }
}
