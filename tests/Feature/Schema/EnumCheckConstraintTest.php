<?php

declare(strict_types=1);

use App\Enums\JenisDokumen;
use App\Enums\JenisPertanggungan;
use App\Enums\KategoriChecklist;
use App\Enums\Kolektibilitas;
use App\Enums\ProdukTipe;
use App\Enums\StatusPelunasanInternal;
use App\Enums\StatusTemplate;
use App\Enums\StatusVersi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function constraintDefinition(string $table, string $constraint): ?string
{
    $value = DB::table('pg_constraint')
        ->join('pg_class', 'pg_class.oid', '=', 'pg_constraint.conrelid')
        ->where('pg_class.relname', $table)
        ->where('pg_constraint.conname', $constraint)
        ->selectRaw('pg_get_constraintdef(pg_constraint.oid) as definition')
        ->value('definition');

    return is_string($value) ? $value : null;
}

it('membangun CHECK dari nilai enum', function () {
    $pairs = [
        ['produk', 'tipe', ProdukTipe::cases()],
        ['ref_kolektibilitas', 'kode', Kolektibilitas::cases()],
        ['ref_status_pelunasan_internal', 'kode', StatusPelunasanInternal::cases()],
        ['ref_rate_kendaraan', 'jenis_pertanggungan', JenisPertanggungan::cases()],
        ['ref_checklist_dokumen', 'kategori', KategoriChecklist::cases()],
        ['ref_parameter_kebijakan', 'status', StatusVersi::cases()],
        ['ref_approval_matrix', 'status', StatusVersi::cases()],
        ['ref_template_dokumen', 'kode', JenisDokumen::templateCases()],
        ['ref_template_dokumen', 'status', StatusTemplate::cases()],
    ];

    foreach ($pairs as [$table, $column, $cases]) {
        $definition = constraintDefinition($table, $table.'_'.$column.'_check');

        expect($definition)->not->toBeNull("CHECK {$table}.{$column} tidak ada");

        foreach ($cases as $case) {
            expect($definition)->toContain((string) $case->value);
        }
    }
});

it('tidak mengizinkan LAINNYA pada template dokumen', function () {
    $definition = constraintDefinition('ref_template_dokumen', 'ref_template_dokumen_kode_check');

    expect($definition)->not->toBeNull()
        ->and($definition)->not->toContain(JenisDokumen::Lainnya->value);
});
