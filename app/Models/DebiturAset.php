<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DebiturAset extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'debitur_aset';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'debitur_id',
        'urutan',
        'jenis_aset',
        'kategori_pojk_id',
        'jenis_dokumen',
        'no_dokumen',
        'tanggal_dokumen',
        'berlaku_sampai',
        'luas_jumlah',
        'satuan',
        'harga_taksasi_satuan',
        'nilai_taksasi',
        'persen_diperhitungkan',
        'nilai_diperhitungkan',
        'status_kepemilikan',
        'nama_pemilik',
        'hubungan_pemohon',
        'lokasi',
        'koordinat',
        'njop',
        'penilai',
        'tanggal_penilaian',
        'foto_path',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'tanggal_dokumen' => 'date',
            'berlaku_sampai' => 'date',
            'luas_jumlah' => 'decimal:2',
            'harga_taksasi_satuan' => 'decimal:2',
            'nilai_taksasi' => 'decimal:2',
            'persen_diperhitungkan' => 'decimal:4',
            'nilai_diperhitungkan' => 'decimal:2',
            'njop' => 'decimal:2',
            'tanggal_penilaian' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public function debitur(): BelongsTo
    {
        return $this->belongsTo(Debitur::class, 'debitur_id');
    }

    public function kategoriPojk(): BelongsTo
    {
        return $this->belongsTo(RefKategoriAgunanPojk::class, 'kategori_pojk_id');
    }
}
