<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebiturUsaha extends Model
{
    use HasUuids;

    protected $table = 'debitur_usaha';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'debitur_id',
        'nama_usaha',
        'bentuk_badan',
        'nib',
        'npwp_usaha',
        'sektor',
        'alamat_lokasi',
        'koordinat_lokasi',
        'lama_usaha_tahun',
        'jumlah_karyawan',
        'jam_operasional',
        'hari_kerja_per_bulan',
        'media_penjualan',
        'kompetitor',
        'omzet_harian',
        'omzet_bulanan',
        'hpp_persen',
        'latar_belakang',
        'siklus_usaha_desc',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'lama_usaha_tahun' => 'integer',
            'jumlah_karyawan' => 'integer',
            'hari_kerja_per_bulan' => 'integer',
            'omzet_harian' => 'decimal:2',
            'omzet_bulanan' => 'decimal:2',
            'hpp_persen' => 'decimal:4',
        ];
    }

    /** @return BelongsTo<Debitur, $this> */
    public function debitur(): BelongsTo
    {
        return $this->belongsTo(Debitur::class, 'debitur_id');
    }
}
