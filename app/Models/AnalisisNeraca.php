<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisisNeraca extends Model
{
    use HasUuids;

    protected $table = 'analisis_neraca';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'parameter_kebijakan_id',
        'kas_inventaris',
        'bank_simpanan',
        'piutang',
        'persediaan',
        'sewa_dibayar_dimuka',
        'biaya_dibayar_dimuka',
        'pajak_dibayar_dimuka',
        'investasi_jangka_panjang',
        'tanah_bangunan',
        'kebun',
        'kendaraan',
        'aset_lain',
        'jumlah_aktiva_lancar',
        'jumlah_aktiva_tetap',
        'total_aktiva',
        'hutang_pendek',
        'hutang_pajak',
        'hutang_gaji',
        'hutang_panjang',
        'pinjaman_bank',
        'pinjaman_lain',
        'prive',
        'jumlah_hutang',
        'rugi_laba_ditahan',
        'ekuitas_modal_disetor',
        'jumlah_modal',
        'total_pasiva',
        'selisih_cek',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'kas_inventaris' => 'decimal:2',
            'bank_simpanan' => 'decimal:2',
            'piutang' => 'decimal:2',
            'persediaan' => 'decimal:2',
            'sewa_dibayar_dimuka' => 'decimal:2',
            'biaya_dibayar_dimuka' => 'decimal:2',
            'pajak_dibayar_dimuka' => 'decimal:2',
            'investasi_jangka_panjang' => 'decimal:2',
            'tanah_bangunan' => 'decimal:2',
            'kebun' => 'decimal:2',
            'kendaraan' => 'decimal:2',
            'aset_lain' => 'decimal:2',
            'jumlah_aktiva_lancar' => 'decimal:2',
            'jumlah_aktiva_tetap' => 'decimal:2',
            'total_aktiva' => 'decimal:2',
            'hutang_pendek' => 'decimal:2',
            'hutang_pajak' => 'decimal:2',
            'hutang_gaji' => 'decimal:2',
            'hutang_panjang' => 'decimal:2',
            'pinjaman_bank' => 'decimal:2',
            'pinjaman_lain' => 'decimal:2',
            'prive' => 'decimal:2',
            'jumlah_hutang' => 'decimal:2',
            'rugi_laba_ditahan' => 'decimal:2',
            'ekuitas_modal_disetor' => 'decimal:2',
            'jumlah_modal' => 'decimal:2',
            'total_pasiva' => 'decimal:2',
            'selisih_cek' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<RefParameterKebijakan, $this> */
    public function parameterKebijakan(): BelongsTo
    {
        return $this->belongsTo(RefParameterKebijakan::class, 'parameter_kebijakan_id');
    }

    /** @return BelongsTo<PermohonanKredit, $this> */
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PermohonanKredit::class, 'permohonan_id');
    }
}
