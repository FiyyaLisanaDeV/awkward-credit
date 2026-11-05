<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AuditorUserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->where('username', 'auditor')->exists()) {
            return;
        }

        $cabangId = DB::table('cabang')->where('kode', 'KDI')->value('id');

        if ($cabangId === null) {
            $this->command?->warn('AuditorUserSeeder dilewati: cabang KDI belum ada.');

            return;
        }

        $password = (string) (config('loan.security.auditor_initial_password') ?: Str::password(16));

        $auditor = User::query()->create([
            'cabang_id' => $cabangId,
            'name' => 'Auditor Internal',
            'username' => 'auditor',
            'email' => 'auditor@bpr-bahteramas.local',
            'password' => $password,
            'jabatan' => 'Auditor Internal',
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $auditor->assignRole('AUDITOR');

        if (! app()->isProduction()) {
            $this->command?->warn('Akun auditor dibuat. Kata sandi awal: '.$password.' (wajib diganti saat login pertama).');
        }
    }
}
