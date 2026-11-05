<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Reference\AdminUserSeeder;
use Database\Seeders\Reference\ApprovalMatrixSeeder;
use Database\Seeders\Reference\AuditorUserSeeder;
use Database\Seeders\Reference\CabangSeeder;
use Database\Seeders\Reference\ChecklistDokumenSeeder;
use Database\Seeders\Reference\ImportProfilSeeder;
use Database\Seeders\Reference\KategoriAgunanPojkSeeder;
use Database\Seeders\Reference\KolektibilitasSeeder;
use Database\Seeders\Reference\KonversiSukuBungaSeeder;
use Database\Seeders\Reference\ParameterKebijakanSeeder;
use Database\Seeders\Reference\ProdukSeeder;
use Database\Seeders\Reference\RateAsuransiSeeder;
use Database\Seeders\Reference\RolePermissionSeeder;
use Database\Seeders\Reference\StatusPelunasanSeeder;
use Database\Seeders\Reference\TemplateDokumenSeeder;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CabangSeeder::class,
            ProdukSeeder::class,
            KategoriAgunanPojkSeeder::class,
            KolektibilitasSeeder::class,
            StatusPelunasanSeeder::class,
            ChecklistDokumenSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            AuditorUserSeeder::class,
            ParameterKebijakanSeeder::class,
            ApprovalMatrixSeeder::class,
            TemplateDokumenSeeder::class,
            ImportProfilSeeder::class,
            RateAsuransiSeeder::class,
            KonversiSukuBungaSeeder::class,
        ]);
    }
}
