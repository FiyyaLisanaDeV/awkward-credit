<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RefRateKendaraan extends Model
{
    use HasUuids;

    protected $table = 'ref_rate_kendaraan';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'perusahaan',
        'jenis_pertanggungan',
        'harga_min',
        'harga_max',
        'rate',
        'effective_date',
        'sumber',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'harga_min' => 'decimal:2',
            'harga_max' => 'decimal:2',
            'rate' => 'decimal:6',
            'effective_date' => 'date',
        ];
    }
}
