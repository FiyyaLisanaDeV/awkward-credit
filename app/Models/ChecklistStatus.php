<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistStatus extends Model
{
    use HasUuids;

    protected $table = 'checklist_status';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'checklist_id',
        'status',
        'nomor_dokumen',
        'tanggal_dokumen',
        'catatan',
        'verified_by',
        'verified_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tanggal_dokumen' => 'date',
            'verified_by' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<RefChecklistDokumen, $this> */
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(RefChecklistDokumen::class, 'checklist_id');
    }

    /** @return BelongsTo<PermohonanKredit, $this> */
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanKredit::class, 'permohonan_id');
    }

    /** @return BelongsTo<User, $this> */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
