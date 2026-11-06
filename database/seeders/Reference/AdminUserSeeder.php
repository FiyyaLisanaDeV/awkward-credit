<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $existing = User::query()
            ->where('email', 'admin@bpr-bahteramas.local')
            ->orWhere('username', 'admin')
            ->first();

        if ($existing instanceof User) {
            $existing->assignRole('ADMIN');

            return;
        }

        $cabangId = DB::table('cabang')->where('kode', 'KDI')->value('id');
        $password = (string) (config('loan.security.admin_initial_password') ?: Str::password(16));

        $admin = User::query()->create([
            'cabang_id' => $cabangId,
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@bpr-bahteramas.local',
            'password' => $password,
            'jabatan' => 'System Administrator',
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $admin->assignRole('ADMIN');

        if (! app()->isProduction()) {
            $this->command?->warn('Akun admin dibuat. Kata sandi awal: '.$password.' (wajib diganti saat login pertama).');
        }
    }
}
