<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermohonanSlikPasangan extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'permohonan_slik_pasangan';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'slik_permintaan_id',
        'urutan',
        'nama_lembaga',
        'jenis_fasilitas',
        'no_fasilitas',
        'plafon',
        'baki_debet',
        'kolektibilitas_id',
        'kondisi',
        'tanggal_data',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'plafon' => 'decimal:2',
            'baki_debet' => 'decimal:2',
            'tanggal_data' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<RefKolektibilitas, $this> */
    public function kolektibilitas(): BelongsTo
    {
        return $this->belongsTo(RefKolektibilitas::class, 'kolektibilitas_id');
    }

    /** @return BelongsTo<PermohonanKredit, $this> */
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanKredit::class, 'permohonan_id');
    }

    /** @return BelongsTo<SlikPermintaan, $this> */
    public function slikPermintaan(): BelongsTo
    {
        return $this->belongsTo(SlikPermintaan::class, 'slik_permintaan_id');
    }
}
