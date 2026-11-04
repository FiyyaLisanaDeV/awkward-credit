<?php

declare(strict_types=1);

use App\Enums\AksiApproval;
use App\Enums\SistemSukuBunga;
use App\Enums\StatusChecklist;
use App\Models\AnalisisModalKerja;
use App\Models\ApprovalLog;
use App\Models\ChecklistStatus;
use App\Models\PermohonanKredit;
use App\Models\PermohonanSlikDebitur;
use App\Models\RefChecklistDokumen;
use App\Models\RefKolektibilitas;
use App\Models\User;
use App\Services\Rules\BusinessRuleEngine;
use App\Services\Rules\Rules\BR01SlikBermasalah;
use App\Services\Rules\Rules\BR02DscrMinimum;
use App\Services\Rules\Rules\BR03LtvMaksimum;
use App\Services\Rules\Rules\BR04BalloonWajib;
use App\Services\Rules\Rules\BR05ChecklistWajib;
use App\Services\Rules\Rules\BR06UrutanApproval;
use App\Services\Rules\Rules\BR07TemplateApproved;
use App\Services\Rules\Rules\BR08ParameterTersimpan;
use App\Services\Rules\Rules\BR09AuditImmutable;
use App\Services\Rules\Rules\BR10KewajaranPlafon;
use App\Services\Rules\Rules\BR11IdirTpp;
use App\Services\Rules\Rules\BR12StressTest;
use App\Services\Rules\Rules\BR13BalloonKritis;
use App\Services\Rules\Rules\BR14TanggalLahir;
use App\Services\Rules\Rules\BR15NikConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

it('memiliki 15 rule dengan kode unik', function () {
    $classes = [
        BR01SlikBermasalah::class, BR02DscrMinimum::class, BR03LtvMaksimum::class,
        BR04BalloonWajib::class, BR05ChecklistWajib::class, BR06UrutanApproval::class,
        BR07TemplateApproved::class, BR08ParameterTersimpan::class, BR09AuditImmutable::class,
        BR10KewajaranPlafon::class, BR11IdirTpp::class, BR12StressTest::class,
        BR13BalloonKritis::class, BR14TanggalLahir::class, BR15NikConsent::class,
    ];

    $rules = array_map(static fn (string $class) => app($class), $classes);

    expect($rules)->toHaveCount(15);

    $kode = array_map(static fn ($rule) => $rule->kode(), $rules);
    expect(array_unique($kode))->toHaveCount(15);
});

it('BR-01 menolak SLIK dengan kolektibilitas 3', function () {
    $permohonan = fixturePermohonan();
    $kol3 = RefKolektibilitas::query()->where('kode', 3)->firstOrFail();

    PermohonanSlikDebitur::query()->create([
        'permohonan_id' => $permohonan->id,
        'urutan' => 1,
        'nama_lembaga' => 'Bank Uji',
        'jenis_fasilitas' => 'Kredit',
        'plafon' => 10000000,
        'baki_debet' => 5000000,
        'kolektibilitas_id' => $kol3->id,
    ]);

    $result = app(BR01SlikBermasalah::class)->validate($permohonan);

    expect($result->passed)->toBeFalse();
});

it('BR-02 dan BR-03 memeriksa DSCR serta LTV', function () {
    $permohonan = fixturePermohonan();
    fixtureKapasitas($permohonan, ['dscr_rekomendasi' => 0.8, 'ltv' => 0.9]);

    $dscr = app(BR02DscrMinimum::class)->validate($permohonan);
    $ltv = app(BR03LtvMaksimum::class)->validate($permohonan);

    expect($dscr->passed)->toBeFalse()
        ->and($ltv->passed)->toBeFalse();
});

it('BR-04 mewajibkan sumber balloon pada grace period', function () {
    $permohonan = new PermohonanKredit([
        'sistem_suku_bunga' => SistemSukuBunga::GracePeriod->value,
        'sumber_pelunasan_balloon' => null,
        'perkiraan_penerimaan_balloon' => null,
    ]);

    expect(app(BR04BalloonWajib::class)->validate($permohonan)->passed)->toBeFalse();
    expect((string) $permohonan->sistem_suku_bunga)->toBe(SistemSukuBunga::GracePeriod->value);
});

it('BR-05 memeriksa checklist wajib', function () {
    $permohonan = fixturePermohonan();

    expect(app(BR05ChecklistWajib::class)->validate($permohonan)->passed)->toBeFalse();

    $checklist = RefChecklistDokumen::query()->where('wajib', true)->where('is_active', true)->get();

    foreach ($checklist as $item) {
        ChecklistStatus::query()->create([
            'permohonan_id' => $permohonan->id,
            'checklist_id' => $item->id,
            'status' => StatusChecklist::Ada->value,
        ]);
    }

    expect(app(BR05ChecklistWajib::class)->validate($permohonan)->passed)->toBeTrue();
});

it('BR-06 menolak persetujuan yang melompati level', function () {
    $permohonan = fixturePermohonan();
    $user = User::query()->where('username', 'admin')->firstOrFail();
    $roleId = DB::table('roles')->value('id');

    ApprovalLog::query()->create([
        'cabang_id' => $permohonan->cabang_id,
        'permohonan_id' => $permohonan->id,
        'level' => 2,
        'role_id' => $roleId,
        'user_id' => $user->id,
        'aksi' => AksiApproval::Approve->value,
    ]);

    expect(app(BR06UrutanApproval::class)->validate($permohonan)->passed)->toBeFalse();
});

it('BR-08, BR-09, BR-10, BR-11, BR-13, BR-14, BR-15 berjalan', function () {
    $permohonan = fixturePermohonan();
    fixtureKapasitas($permohonan, ['idir' => 0.9, 'rasio_kecukupan_balloon' => 0.9]);

    AnalisisModalKerja::query()->create([
        'permohonan_id' => $permohonan->id,
        'parameter_kebijakan_id' => $permohonan->parameter_kebijakan_id,
        'rata_rata_kebutuhan_mk' => 10000000,
    ]);

    expect(app(BR08ParameterTersimpan::class)->validate($permohonan)->passed)->toBeTrue()
        ->and(app(BR09AuditImmutable::class)->validate($permohonan)->passed)->toBeTrue()
        ->and(app(BR10KewajaranPlafon::class)->validate($permohonan)->passed)->toBeFalse()
        ->and(app(BR11IdirTpp::class)->validate($permohonan)->passed)->toBeFalse()
        ->and(app(BR13BalloonKritis::class)->validate($permohonan)->passed)->toBeFalse()
        ->and(app(BR14TanggalLahir::class)->validate($permohonan)->passed)->toBeTrue()
        ->and(app(BR15NikConsent::class)->validate($permohonan)->passed)->toBeTrue();
});

it('engine mengumpulkan hasil dan mendeteksi pelanggaran keras', function () {
    $permohonan = fixturePermohonan();
    fixtureKapasitas($permohonan, ['dscr_rekomendasi' => 0.7]);

    $rules = [
        app(BR01SlikBermasalah::class), app(BR02DscrMinimum::class), app(BR03LtvMaksimum::class),
        app(BR04BalloonWajib::class), app(BR05ChecklistWajib::class), app(BR06UrutanApproval::class),
        app(BR07TemplateApproved::class), app(BR08ParameterTersimpan::class), app(BR09AuditImmutable::class),
        app(BR10KewajaranPlafon::class), app(BR11IdirTpp::class), app(BR12StressTest::class),
        app(BR13BalloonKritis::class), app(BR14TanggalLahir::class), app(BR15NikConsent::class),
    ];

    $engine = new BusinessRuleEngine($rules);
    $results = $engine->validateAll($permohonan);

    expect($results)->toHaveCount(15)
        ->and($engine->hasHardViolations($results))->toBeTrue();
});
