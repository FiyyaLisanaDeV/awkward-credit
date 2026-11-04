<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RefRateAsuransiJiwa extends Model
{
    use HasUuids;

    protected $table = 'ref_rate_asuransi_jiwa';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'perusahaan',
        'usia_min',
        'usia_max',
        'tenor_min',
        'tenor_max',
        'rate_permil',
        'effective_date',
        'sumber',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'usia_min' => 'integer',
            'usia_max' => 'integer',
            'tenor_min' => 'integer',
            'tenor_max' => 'integer',
            'rate_permil' => 'decimal:4',
            'effective_date' => 'date',
        ];
    }
}
