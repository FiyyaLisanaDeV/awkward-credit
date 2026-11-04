<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebiturPengkinian extends Model
{
    use HasUuids;

    protected $table = 'debitur_pengkinian';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'debitur_id',
        'cif',
        'tujuan_penggunaan',
        'sumber_penghasilan',
        'pemasukan_bulanan',
        'frekuensi_setoran',
        'pengeluaran_bulanan',
        'frekuensi_tarikan',
        'tanggal_pengkinian',
        'processed_by',
        'catatan',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'pemasukan_bulanan' => 'decimal:2',
            'frekuensi_setoran' => 'integer',
            'pengeluaran_bulanan' => 'decimal:2',
            'frekuensi_tarikan' => 'integer',
            'tanggal_pengkinian' => 'date',
            'processed_by' => 'integer',
        ];
    }

    /** @return BelongsTo<Debitur, $this> */
    public function debitur(): BelongsTo
    {
        return $this->belongsTo(Debitur::class, 'debitur_id');
    }

    /** @return BelongsTo<User, $this> */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
