<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ApprovalMatrixSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ref_approval_matrix')->where('version', 'v1.0')->exists()) {
            return;
        }

        $adminId = DB::table('users')->orderBy('id')->value('id');

        if ($adminId === null) {
            $this->command?->warn('ApprovalMatrixSeeder dilewati: belum ada user.');

            return;
        }

        $matrix = [
            ['plafon_min' => 0, 'plafon_max' => 10000000, 'level' => 1, 'role' => 'AO'],
            ['plafon_min' => 0, 'plafon_max' => 10000000, 'level' => 2, 'role' => 'MGR_PEMASARAN'],
            ['plafon_min' => 10000000, 'plafon_max' => 50000000, 'level' => 3, 'role' => 'KOORDINATOR'],
            ['plafon_min' => 50000000, 'plafon_max' => 100000000, 'level' => 4, 'role' => 'DIRUT'],
            ['plafon_min' => 100000000, 'plafon_max' => null, 'level' => 5, 'role' => 'KOMITE'],
        ];

        DB::table('ref_approval_matrix')->insert([
            'id' => (string) Str::uuid(),
            'version' => 'v1.0',
            'effective_date' => '2026-01-01',
            'matrix' => json_encode($matrix),
            'status' => 'ACTIVE',
            'created_by' => $adminId,
            'approved_by' => $adminId,
            'approved_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
