<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermohonanRiwayatInternal extends Model
{
    use HasUuids;

    protected $table = 'permohonan_riwayat_internal';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'no_rekening_pinjaman',
        'plafon',
        'tanggal_cair',
        'tanggal_lunas_jatuh_tempo',
        'kolektibilitas_tertinggi_id',
        'status_pelunasan_id',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'plafon' => 'decimal:2',
            'tanggal_cair' => 'date',
            'tanggal_lunas_jatuh_tempo' => 'date',
        ];
    }

    /** @return BelongsTo<RefKolektibilitas, $this> */
    public function kolektibilitasTertinggi(): BelongsTo
    {
        return $this->belongsTo(RefKolektibilitas::class, 'kolektibilitas_tertinggi_id');
    }

    /** @return BelongsTo<PermohonanKredit, $this> */
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanKredit::class, 'permohonan_id');
    }

    /** @return BelongsTo<RefStatusPelunasanInternal, $this> */
    public function statusPelunasan(): BelongsTo
    {
        return $this->belongsTo(RefStatusPelunasanInternal::class, 'status_pelunasan_id');
    }
}
