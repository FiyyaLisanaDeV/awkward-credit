<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsuransiKredit extends Model
{
    use HasUuids;

    protected $table = 'asuransi_kredit';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'kredit_id',
        'jenis',
        'perusahaan',
        'no_polis',
        'premi',
        'manfaat',
        'usia_peserta',
        'tanggal_mulai',
        'tanggal_berakhir',
        'status',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'premi' => 'decimal:2',
            'manfaat' => 'decimal:2',
            'usia_peserta' => 'integer',
            'tanggal_mulai' => 'date',
            'tanggal_berakhir' => 'date',
        ];
    }

    /** @return BelongsTo<Kredit, $this> */
    public function kredit(): BelongsTo
    {
        return $this->belongsTo(Kredit::class, 'kredit_id');
    }
}
