<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignatureLog extends Model
{
    use HasUuids;

    protected $table = 'signature_log';

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'dokumen_id',
        'pihak',
        'nama',
        'jabatan',
        'ttd_hash',
        'ttd_file_path',
        'ip_address',
        'timestamp',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
        ];
    }

    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class, 'dokumen_id');
    }
}
