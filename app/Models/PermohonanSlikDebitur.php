<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermohonanSlikDebitur extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'permohonan_slik_debitur';

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
        'jenis_penggunaan',
        'no_fasilitas',
        'plafon',
        'baki_debet',
        'suku_bunga',
        'jangka_waktu_bulan',
        'angsuran_bulanan',
        'kolektibilitas_id',
        'kondisi',
        'tanggal_mulai',
        'tanggal_tunggakan',
        'hari_tunggakan',
        'frekuensi_tunggakan_12m',
        'tunggakan_pokok',
        'tunggakan_bunga',
        'jenis_jaminan',
        'nilai_jaminan',
        'penjamin',
        'tanggal_data',
        'is_angsuran_estimated',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'plafon' => 'decimal:2',
            'baki_debet' => 'decimal:2',
            'suku_bunga' => 'decimal:4',
            'jangka_waktu_bulan' => 'integer',
            'angsuran_bulanan' => 'decimal:2',
            'tanggal_mulai' => 'date',
            'tanggal_tunggakan' => 'date',
            'hari_tunggakan' => 'integer',
            'frekuensi_tunggakan_12m' => 'integer',
            'tunggakan_pokok' => 'decimal:2',
            'tunggakan_bunga' => 'decimal:2',
            'nilai_jaminan' => 'decimal:2',
            'tanggal_data' => 'date',
            'is_angsuran_estimated' => 'boolean',
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
