<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ScopesByCabang
{
    protected function ensureSameCabang(Model $model, User $user): void
    {
        if ($user->hasRole('ADMIN')) {
            return;
        }

        abort_unless(
            (string) $model->getAttribute('cabang_id') === (string) $user->cabang_id,
            403,
            'Akses lintas cabang ditolak.'
        );
    }
}
