<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dokumen extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'dokumen';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'cabang_id',
        'permohonan_id',
        'template_id',
        'jenis_dokumen',
        'nomor_dokumen',
        'tanggal_dokumen',
        'file_path',
        'file_hash',
        'status_ttd',
        'tanggal_ttd',
        'ukuran_bytes',
        'sumber',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tanggal_dokumen' => 'date',
            'tanggal_ttd' => 'date',
            'ukuran_bytes' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Cabang, $this> */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    /** @return BelongsTo<PermohonanKredit, $this> */
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanKredit::class, 'permohonan_id');
    }

    /** @return BelongsTo<RefTemplateDokumen, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(RefTemplateDokumen::class, 'template_id');
    }

    /** @return HasMany<SignatureLog, $this> */
    public function signatures(): HasMany
    {
        return $this->hasMany(SignatureLog::class, 'dokumen_id');
    }
}
