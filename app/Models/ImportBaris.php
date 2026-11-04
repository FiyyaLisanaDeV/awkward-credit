<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBaris extends Model
{
    use HasUuids;

    protected $table = 'import_baris';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'batch_id',
        'nomor_baris',
        'payload',
        'normalized',
        'errors',
        'status',
        'target_type',
        'target_id',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'nomor_baris' => 'integer',
            'payload' => 'array',
            'normalized' => 'array',
            'errors' => 'array',
        ];
    }

    /** @return BelongsTo<ImportBatch, $this> */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'batch_id');
    }
}
