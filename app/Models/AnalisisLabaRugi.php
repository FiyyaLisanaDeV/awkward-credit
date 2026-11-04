<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisisLabaRugi extends Model
{
    use HasUuids;

    protected $table = 'analisis_laba_rugi';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'parameter_kebijakan_id',
        'jumlah_bulan_berjalan',
        'penghasilan_pemohon',
        'penghasilan_pasangan_gaji',
        'penghasilan_lain_pemohon',
        'penghasilan_pasangan_tpp',
        'hpp',
        'biaya_listrik_air',
        'biaya_transport',
        'biaya_sewa',
        'biaya_gaji',
        'biaya_pendidikan',
        'biaya_kesehatan',
        'biaya_konsumsi',
        'biaya_cicilan_lain',
        'biaya_telepon',
        'biaya_kebersihan',
        'biaya_lain',
        'total_pendapatan',
        'total_pengeluaran',
        'laba_kotor',
        'penyusutan',
        'pajak',
        'laba_bersih',
        'angsuran_kredit_berjalan',
        'surplus_per_bulan',
        'surplus_6_bulan',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'jumlah_bulan_berjalan' => 'integer',
            'penghasilan_pemohon' => 'decimal:2',
            'penghasilan_pasangan_gaji' => 'decimal:2',
            'penghasilan_lain_pemohon' => 'decimal:2',
            'penghasilan_pasangan_tpp' => 'decimal:2',
            'hpp' => 'decimal:2',
            'biaya_listrik_air' => 'decimal:2',
            'biaya_transport' => 'decimal:2',
            'biaya_sewa' => 'decimal:2',
            'biaya_gaji' => 'decimal:2',
            'biaya_pendidikan' => 'decimal:2',
            'biaya_kesehatan' => 'decimal:2',
            'biaya_konsumsi' => 'decimal:2',
            'biaya_cicilan_lain' => 'decimal:2',
            'biaya_telepon' => 'decimal:2',
            'biaya_kebersihan' => 'decimal:2',
            'biaya_lain' => 'decimal:2',
            'total_pendapatan' => 'decimal:2',
            'total_pengeluaran' => 'decimal:2',
            'laba_kotor' => 'decimal:2',
            'penyusutan' => 'decimal:2',
            'pajak' => 'decimal:2',
            'laba_bersih' => 'decimal:2',
            'angsuran_kredit_berjalan' => 'decimal:2',
            'surplus_per_bulan' => 'decimal:2',
            'surplus_6_bulan' => 'decimal:2',
        ];
    }

    public function parameterKebijakan(): BelongsTo
    {
        return $this->belongsTo(RefParameterKebijakan::class, 'parameter_kebijakan_id');
    }

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanKredit::class, 'permohonan_id');
    }
}
