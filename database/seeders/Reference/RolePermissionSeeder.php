<?php

declare(strict_types=1);

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'permohonan.create', 'permohonan.edit_own', 'permohonan.view', 'permohonan.submit',
            'permohonan.approve_level_1', 'permohonan.approve_level_2', 'permohonan.approve_level_3',
            'permohonan.approve_level_4', 'permohonan.approve_level_5', 'permohonan.return', 'permohonan.reject',
            'dokumen.view', 'dokumen.generate_draft', 'dokumen.upload', 'dokumen.sign',
            'template.review', 'template.approve',
            'audit.view', 'ledger.view', 'report.view',
            'parameter.activate', 'user.manage',
            'setting.view', 'setting.edit', 'setting.approve', 'setting.apply',
            'simulasi.create', 'import.slik', 'import.debitur', 'import.dokumen', 'import.pembayaran',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $map = [
            'AO' => [
                'permohonan.create', 'permohonan.edit_own', 'permohonan.view', 'permohonan.submit',
                'permohonan.approve_level_1',
                'dokumen.view', 'dokumen.generate_draft', 'dokumen.upload',
                'simulasi.create', 'import.slik', 'import.debitur', 'import.dokumen',
            ],
            'MGR_PEMASARAN' => [
                'permohonan.view', 'permohonan.approve_level_2', 'permohonan.return',
                'dokumen.view', 'report.view',
            ],
            'KOORDINATOR' => [
                'permohonan.view', 'permohonan.approve_level_3', 'permohonan.return', 'report.view',
            ],
            'DIRUT' => [
                'permohonan.view', 'permohonan.approve_level_4', 'permohonan.reject', 'report.view',
                'setting.view', 'setting.approve',
            ],
            'KOMITE' => [
                'permohonan.view', 'permohonan.approve_level_5', 'report.view',
                'setting.view', 'setting.approve',
            ],
            'ADMIN' => $permissions,
            'LEGAL' => [
                'dokumen.view', 'template.review', 'template.approve', 'setting.view',
            ],
            'AUDITOR' => [
                'audit.view', 'ledger.view', 'report.view', 'permohonan.view', 'setting.view',
            ],
        ];

        foreach ($map as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
