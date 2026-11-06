<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomorSequence extends Model
{
    protected $table = 'nomor_sequence';

    protected $primaryKey = 'scope';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['scope', 'last_number', 'updated_at'];
}
