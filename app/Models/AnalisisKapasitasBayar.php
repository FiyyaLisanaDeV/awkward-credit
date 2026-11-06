<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalisisKapasitasBayar extends Model
{
    use HasUuids;

    protected $table = 'analisis_kapasitas_bayar';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'permohonan_id',
        'parameter_kebijakan_id',
        'rpc_bulanan',
        'dscr_diajukan',
        'status_dscr_diajukan',
        'surplus_setelah_angsuran_diajukan',
        'dscr_rekomendasi',
        'status_dscr_rekomendasi',
        'surplus_setelah_angsuran_rekomendasi',
        'plafon_max_flat',
        'plafon_max_anuitas',
        'plafon_max_grace',
        'suku_bunga_efektif_diajukan',
        'suku_bunga_efektif_rekomendasi',
        'ltv',
        'status_kewajaran_plafon',
        'total_pembayaran_jatuh_tempo',
        'rasio_kecukupan_balloon',
        'status_kecukupan_balloon',
        'idir',
        'status_idir',
        'angsuran_slik_debitur',
        'angsuran_slik_pasangan',
        'total_angsuran_slik',
        'jumlah_fasilitas_slik_aktif',
        'biaya_lain_bulanan',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'rpc_bulanan' => 'decimal:2',
            'dscr_diajukan' => 'decimal:4',
            'surplus_setelah_angsuran_diajukan' => 'decimal:2',
            'dscr_rekomendasi' => 'decimal:4',
            'surplus_setelah_angsuran_rekomendasi' => 'decimal:2',
            'plafon_max_flat' => 'decimal:2',
            'plafon_max_anuitas' => 'decimal:2',
            'plafon_max_grace' => 'decimal:2',
            'suku_bunga_efektif_diajukan' => 'decimal:4',
            'suku_bunga_efektif_rekomendasi' => 'decimal:4',
            'ltv' => 'decimal:4',
            'total_pembayaran_jatuh_tempo' => 'decimal:2',
            'rasio_kecukupan_balloon' => 'decimal:4',
            'idir' => 'decimal:4',
            'angsuran_slik_debitur' => 'decimal:2',
            'angsuran_slik_pasangan' => 'decimal:2',
            'total_angsuran_slik' => 'decimal:2',
            'jumlah_fasilitas_slik_aktif' => 'integer',
            'biaya_lain_bulanan' => 'decimal:2',
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
