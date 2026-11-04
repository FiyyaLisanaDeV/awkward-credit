<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RefStatusPelunasanInternal extends Model
{
    use HasUuids;

    protected $table = 'ref_status_pelunasan_internal';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'kode',
        'nama',
        'flag_bermasalah',
        'sumber',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'flag_bermasalah' => 'boolean',
        ];
    }
}
