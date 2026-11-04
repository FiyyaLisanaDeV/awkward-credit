<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulasiKredit extends Model
{
    use HasUuids;

    protected $table = 'simulasi_kredit';

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'cabang_id',
        'user_id',
        'permohonan_id',
        'debitur_id',
        'label',
        'produk_tipe',
        'sistem_suku_bunga',
        'parameter_kebijakan_id',
        'parameter_version',
        'engine_version',
        'input',
        'input_hash',
        'output',
        'plafon',
        'rate',
        'tenor',
        'angsuran',
        'dscr',
        'plafon_max',
        'ltv',
        'rasio_balloon',
        'idir',
        'skor_5c',
        'rating',
        'status_kelayakan',
        'jumlah_red_flag',
        'durasi_ms',
        'ip_address',
        'user_agent',
        'ledger_id',
        'dibuat_pada',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'input' => 'array',
            'output' => 'array',
            'plafon' => 'decimal:2',
            'rate' => 'decimal:4',
            'tenor' => 'integer',
            'angsuran' => 'decimal:2',
            'dscr' => 'decimal:4',
            'plafon_max' => 'decimal:2',
            'ltv' => 'decimal:4',
            'rasio_balloon' => 'decimal:4',
            'idir' => 'decimal:4',
            'skor_5c' => 'decimal:4',
            'jumlah_red_flag' => 'integer',
            'durasi_ms' => 'integer',
            'dibuat_pada' => 'datetime',
        ];
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

    /** @return BelongsTo<Ledger, $this> */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
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

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
