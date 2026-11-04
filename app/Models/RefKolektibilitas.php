<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RefKolektibilitas extends Model
{
    use HasUuids;

    protected $table = 'ref_kolektibilitas';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'min_hari_tunggakan',
        'max_hari_tunggakan',
        'sumber',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'kode' => 'integer',
            'min_hari_tunggakan' => 'integer',
            'max_hari_tunggakan' => 'integer',
        ];
    }
}
