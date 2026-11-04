<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ImportProfil extends Model
{
    use HasUuids;

    protected $table = 'import_profil';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'kode',
        'nama',
        'jenis',
        'versi',
        'mapping',
        'delimiter',
        'header_row',
        'sheet_name',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'versi' => 'integer',
            'mapping' => 'array',
            'header_row' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
