<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class PermohonanKredit extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'permohonan_kredit';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'cabang_id',
        'no_permohonan',
        'debitur_id',
        'produk_id',
        'sektor',
        'tujuan_penggunaan',
        'plafon_diajukan',
        'plafon_rekomendasi',
        'suku_bunga_tahunan',
        'tenor_diajukan',
        'tenor_rekomendasi',
        'sistem_suku_bunga',
        'kegunaan_pinjaman',
        'pinjaman_ke',
        'sumber_pelunasan_balloon',
        'perkiraan_penerimaan_balloon',
        'sumber_pembayaran_angsuran',
        'tanggal_target_realisasi',
        'prioritas',
        'status',
        'ao_id',
        'parameter_kebijakan_id',
        'parameter_snapshot',
        'tanggal_permohonan',
        'catatan_ao',
        'kelengkapan_persen',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'plafon_diajukan' => 'decimal:2',
            'plafon_rekomendasi' => 'decimal:2',
            'suku_bunga_tahunan' => 'decimal:4',
            'tenor_diajukan' => 'integer',
            'tenor_rekomendasi' => 'integer',
            'pinjaman_ke' => 'integer',
            'perkiraan_penerimaan_balloon' => 'decimal:2',
            'tanggal_target_realisasi' => 'date',
            'ao_id' => 'integer',
            'tanggal_permohonan' => 'date',
            'kelengkapan_persen' => 'decimal:2',
            'parameter_snapshot' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (self $model): void {
            if ($model->isDirty('parameter_snapshot')) {
                throw new RuntimeException('parameter_snapshot immutable');
            }
        });
    }

    /** @return BelongsTo<User, $this> */
    public function ao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ao_id');
    }

    /** @return BelongsTo<Cabang, $this> */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    /** @return BelongsTo<Debitur, $this> */
    public function debitur(): BelongsTo
    {
        return $this->belongsTo(Debitur::class, 'debitur_id');
    }

    /** @return BelongsTo<RefParameterKebijakan, $this> */
    public function parameterKebijakan(): BelongsTo
    {
        return $this->belongsTo(RefParameterKebijakan::class, 'parameter_kebijakan_id');
    }

    /** @return BelongsTo<Produk, $this> */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /** @return HasMany<PermohonanSlikDebitur, $this> */
    public function slikDebitur(): HasMany
    {
        return $this->hasMany(PermohonanSlikDebitur::class, 'permohonan_id');
    }

    /** @return HasMany<PermohonanSlikPasangan, $this> */
    public function slikPasangan(): HasMany
    {
        return $this->hasMany(PermohonanSlikPasangan::class, 'permohonan_id');
    }

    /** @return HasMany<SlikPermintaan, $this> */
    public function slikPermintaan(): HasMany
    {
        return $this->hasMany(SlikPermintaan::class, 'permohonan_id');
    }

    /** @return HasMany<PermohonanRiwayatInternal, $this> */
    public function riwayatInternal(): HasMany
    {
        return $this->hasMany(PermohonanRiwayatInternal::class, 'permohonan_id');
    }

    /** @return HasMany<Dokumen, $this> */
    public function dokumen(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'permohonan_id');
    }

    /** @return HasMany<ApprovalLog, $this> */
    public function approvalLog(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'permohonan_id');
    }

    /** @return HasMany<ChecklistStatus, $this> */
    public function checklistStatus(): HasMany
    {
        return $this->hasMany(ChecklistStatus::class, 'permohonan_id');
    }

    /** @return HasOne<AnalisisNeraca, $this> */
    public function neraca(): HasOne
    {
        return $this->hasOne(AnalisisNeraca::class, 'permohonan_id');
    }

    /** @return HasOne<AnalisisLabaRugi, $this> */
    public function labaRugi(): HasOne
    {
        return $this->hasOne(AnalisisLabaRugi::class, 'permohonan_id');
    }

    /** @return HasOne<AnalisisRasio, $this> */
    public function rasio(): HasOne
    {
        return $this->hasOne(AnalisisRasio::class, 'permohonan_id');
    }

    /** @return HasOne<AnalisisModalKerja, $this> */
    public function modalKerja(): HasOne
    {
        return $this->hasOne(AnalisisModalKerja::class, 'permohonan_id');
    }

    /** @return HasOne<AnalisisKapasitasBayar, $this> */
    public function kapasitasBayar(): HasOne
    {
        return $this->hasOne(AnalisisKapasitasBayar::class, 'permohonan_id');
    }

    /** @return HasMany<AnalisisSensitivitas, $this> */
    public function sensitivitas(): HasMany
    {
        return $this->hasMany(AnalisisSensitivitas::class, 'permohonan_id');
    }

    /** @return HasOne<AnalisisScoring5c, $this> */
    public function scoring5c(): HasOne
    {
        return $this->hasOne(AnalisisScoring5c::class, 'permohonan_id');
    }

    /** @return HasMany<AnalisisRedFlags, $this> */
    public function redFlags(): HasMany
    {
        return $this->hasMany(AnalisisRedFlags::class, 'permohonan_id');
    }

    /** @return HasOne<AnalisisRekomendasi, $this> */
    public function rekomendasi(): HasOne
    {
        return $this->hasOne(AnalisisRekomendasi::class, 'permohonan_id');
    }

    /** @return HasOne<Analisis7aspek, $this> */
    public function tujuhAspek(): HasOne
    {
        return $this->hasOne(Analisis7aspek::class, 'permohonan_id');
    }

    /** @return HasOne<Kredit, $this> */
    public function kredit(): HasOne
    {
        return $this->hasOne(Kredit::class, 'permohonan_id');
    }
}
