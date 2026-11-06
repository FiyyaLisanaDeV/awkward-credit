<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BukuAgunan extends Model
{
    use HasUuids;

    protected $table = 'buku_agunan';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'agunan_key',
        'debitur_id',
        'debitur_aset_id',
        'permohonan_id',
        'kredit_id',
        'cabang_id',
        'jenis_aset',
        'no_dokumen',
        'nama_pemilik',
        'nilai_taksasi',
        'persen_diperhitungkan',
        'nilai_diperhitungkan',
        'peristiwa',
        'aktor_id',
        'aktor_nama',
        'occurred_at',
        'snapshot',
        'prev_hash',
        'hash',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'aktor_id' => 'integer',
            'occurred_at' => 'datetime',
            'nilai_taksasi' => 'decimal:2',
            'persen_diperhitungkan' => 'decimal:4',
            'nilai_diperhitungkan' => 'decimal:2',
            'snapshot' => 'array',
        ];
    }

    /** @return BelongsTo<Debitur, $this> */
    public function debitur(): BelongsTo
    {
        return $this->belongsTo(Debitur::class, 'debitur_id');
    }

    /** @return BelongsTo<DebiturAset, $this> */
    public function debiturAset(): BelongsTo
    {
        return $this->belongsTo(DebiturAset::class, 'debitur_aset_id');
    }

    /** @return BelongsTo<PermohonanKredit, $this> */
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanKredit::class, 'permohonan_id');
    }

    /** @return BelongsTo<Kredit, $this> */
    public function kredit(): BelongsTo
    {
        return $this->belongsTo(Kredit::class, 'kredit_id');
    }

    /** @return BelongsTo<Cabang, $this> */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    /** @return BelongsTo<User, $this> */
    public function aktor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aktor_id');
    }
}
