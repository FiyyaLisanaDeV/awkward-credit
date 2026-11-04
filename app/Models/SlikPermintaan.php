<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlikPermintaan extends Model
{
    use HasUuids;

    protected $table = 'slik_permintaan';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'debitur_id',
        'tanggal_permintaan',
        'jenis',
        'tujuan',
        'no_referensi',
        'operator_id',
        'file_path',
        'file_hash',
        'sumber',
        'jumlah_fasilitas',
        'total_plafon',
        'total_baki',
        'kualitas_terburuk',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tanggal_permintaan' => 'date',
            'operator_id' => 'integer',
            'jumlah_fasilitas' => 'integer',
            'total_plafon' => 'decimal:2',
            'total_baki' => 'decimal:2',
            'kualitas_terburuk' => 'integer',
        ];
    }

    /** @return BelongsTo<Debitur, $this> */
    public function debitur(): BelongsTo
    {
        return $this->belongsTo(Debitur::class, 'debitur_id');
    }

    /** @return BelongsTo<User, $this> */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /** @return BelongsTo<PermohonanKredit, $this> */
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanKredit::class, 'permohonan_id');
    }
}
