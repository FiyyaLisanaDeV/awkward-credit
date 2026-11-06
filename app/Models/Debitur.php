<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debitur extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'debitur';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::updating(function (Debitur $debitur): void {
            if ($debitur->isDirty('cif')) {
                throw new \RuntimeException('CIF immutable');
            }
        });
    }

    /** @var list<string> */
    protected $fillable = [
        'cabang_id',
        'cif',
        'nama_ktp',
        'nama_ibu_kandung',
        'no_ktp',
        'no_kk',
        'npwp',
        'jenis_kelamin',
        'status_perkawinan',
        'agama',
        'pendidikan',
        'pekerjaan_utama',
        'jabatan',
        'nama_perusahaan',
        'lama_bekerja_tahun',
        'penghasilan_bulanan',
        'email',
        'no_hp',
        'no_wa',
        'alamat_jalan',
        'alamat_rt',
        'alamat_rw',
        'kelurahan',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'kode_pos',
        'alamat_tinggal',
        'status_tempat_tinggal',
        'lama_tinggal_tahun',
        'koordinat_rumah',
        'jumlah_keluarga',
        'jumlah_tanggungan',
        'tempat_lahir',
        'tanggal_lahir',
        'sumber_data',
        'verified_by',
        'verified_at',
        'foto_ktp_path',
        'foto_debitur_path',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'lama_bekerja_tahun' => 'integer',
            'penghasilan_bulanan' => 'decimal:2',
            'lama_tinggal_tahun' => 'integer',
            'jumlah_keluarga' => 'integer',
            'jumlah_tanggungan' => 'integer',
            'tanggal_lahir' => 'date',
            'verified_by' => 'integer',
            'verified_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Cabang, $this> */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    /** @return BelongsTo<User, $this> */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** @return HasOne<DebiturPasangan, $this> */
    public function pasangan(): HasOne
    {
        return $this->hasOne(DebiturPasangan::class, 'debitur_id');
    }

    /** @return HasOne<DebiturUsaha, $this> */
    public function usaha(): HasOne
    {
        return $this->hasOne(DebiturUsaha::class, 'debitur_id');
    }

    /** @return HasOne<DebiturSiklusUsaha, $this> */
    public function siklusUsaha(): HasOne
    {
        return $this->hasOne(DebiturSiklusUsaha::class, 'debitur_id');
    }

    /** @return HasMany<DebiturAset, $this> */
    public function aset(): HasMany
    {
        return $this->hasMany(DebiturAset::class, 'debitur_id');
    }

    /** @return HasMany<DebiturPengkinian, $this> */
    public function pengkinian(): HasMany
    {
        return $this->hasMany(DebiturPengkinian::class, 'debitur_id');
    }

    /** @return HasMany<PermohonanKredit, $this> */
    public function permohonan(): HasMany
    {
        return $this->hasMany(PermohonanKredit::class, 'debitur_id');
    }
}
