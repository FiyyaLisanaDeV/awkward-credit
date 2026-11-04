<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kredit extends Model
{
    use HasUuids;

    protected $table = 'kredit';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'cabang_id',
        'permohonan_id',
        'no_rekening_kredit',
        'no_akad',
        'tanggal_akad',
        'tanggal_cair',
        'plafon',
        'rate',
        'tenor',
        'sistem_suku_bunga',
        'angsuran_bulanan',
        'total_bunga',
        'total_pembayaran',
        'sumber_pelunasan_balloon',
        'rekening_pencairan',
        'rekening_potong_tpp',
        'tujuan_penggunaan',
        'sektor',
        'status',
        'closed_at',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tanggal_akad' => 'date',
            'tanggal_cair' => 'date',
            'plafon' => 'decimal:2',
            'rate' => 'decimal:4',
            'tenor' => 'integer',
            'angsuran_bulanan' => 'decimal:2',
            'total_bunga' => 'decimal:2',
            'total_pembayaran' => 'decimal:2',
            'closed_at' => 'datetime',
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

    /** @return HasMany<JadwalAngsuran, $this> */
    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalAngsuran::class, 'kredit_id');
    }

    /** @return HasMany<BiayaKredit, $this> */
    public function biaya(): HasMany
    {
        return $this->hasMany(BiayaKredit::class, 'kredit_id');
    }

    /** @return HasMany<AsuransiKredit, $this> */
    public function asuransi(): HasMany
    {
        return $this->hasMany(AsuransiKredit::class, 'kredit_id');
    }

    /** @return HasMany<KreditAgunan, $this> */
    public function agunan(): HasMany
    {
        return $this->hasMany(KreditAgunan::class, 'kredit_id');
    }

    /** @return HasOne<MemoPencairan, $this> */
    public function memo(): HasOne
    {
        return $this->hasOne(MemoPencairan::class, 'kredit_id');
    }

    /** @return HasMany<PembayaranAngsuran, $this> */
    public function pembayaran(): HasMany
    {
        return $this->hasMany(PembayaranAngsuran::class, 'kredit_id');
    }
}
