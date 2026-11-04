<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

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
            'deleted_at' => 'datetime',
        ];
    }

    public function ao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ao_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function debitur(): BelongsTo
    {
        return $this->belongsTo(Debitur::class, 'debitur_id');
    }

    public function parameterKebijakan(): BelongsTo
    {
        return $this->belongsTo(RefParameterKebijakan::class, 'parameter_kebijakan_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function slikDebitur(): HasMany
    {
        return $this->hasMany(PermohonanSlikDebitur::class, 'permohonan_id');
    }

    public function slikPasangan(): HasMany
    {
        return $this->hasMany(PermohonanSlikPasangan::class, 'permohonan_id');
    }

    public function slikPermintaan(): HasMany
    {
        return $this->hasMany(SlikPermintaan::class, 'permohonan_id');
    }

    public function riwayatInternal(): HasMany
    {
        return $this->hasMany(PermohonanRiwayatInternal::class, 'permohonan_id');
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'permohonan_id');
    }

    public function approvalLog(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'permohonan_id');
    }

    public function checklistStatus(): HasMany
    {
        return $this->hasMany(ChecklistStatus::class, 'permohonan_id');
    }

    public function neraca(): HasOne
    {
        return $this->hasOne(AnalisisNeraca::class, 'permohonan_id');
    }

    public function labaRugi(): HasOne
    {
        return $this->hasOne(AnalisisLabaRugi::class, 'permohonan_id');
    }

    public function rasio(): HasOne
    {
        return $this->hasOne(AnalisisRasio::class, 'permohonan_id');
    }

    public function modalKerja(): HasOne
    {
        return $this->hasOne(AnalisisModalKerja::class, 'permohonan_id');
    }

    public function kapasitasBayar(): HasOne
    {
        return $this->hasOne(AnalisisKapasitasBayar::class, 'permohonan_id');
    }

    public function sensitivitas(): HasMany
    {
        return $this->hasMany(AnalisisSensitivitas::class, 'permohonan_id');
    }

    public function scoring5c(): HasOne
    {
        return $this->hasOne(AnalisisScoring5c::class, 'permohonan_id');
    }

    public function redFlags(): HasMany
    {
        return $this->hasMany(AnalisisRedFlags::class, 'permohonan_id');
    }

    public function rekomendasi(): HasOne
    {
        return $this->hasOne(AnalisisRekomendasi::class, 'permohonan_id');
    }

    public function tujuhAspek(): HasOne
    {
        return $this->hasOne(Analisis7aspek::class, 'permohonan_id');
    }

    public function kredit(): HasOne
    {
        return $this->hasOne(Kredit::class, 'permohonan_id');
    }
}
