<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatch extends Model
{
    use HasUuids;

    protected $table = 'import_batch';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'cabang_id',
        'user_id',
        'jenis',
        'profil_id',
        'file_path',
        'file_hash',
        'status',
        'total_baris',
        'sukses',
        'gagal',
        'committed_at',
        'ledger_id',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'total_baris' => 'integer',
            'sukses' => 'integer',
            'gagal' => 'integer',
            'committed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Cabang, $this> */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    /** @return BelongsTo<Ledger, $this> */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }

    /** @return BelongsTo<ImportProfil, $this> */
    public function profil(): BelongsTo
    {
        return $this->belongsTo(ImportProfil::class, 'profil_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
