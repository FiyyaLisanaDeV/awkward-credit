<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('memiliki 55 model dan setiap model menunjuk tabel yang ada', function () {
    $files = glob(app_path('Models/*.php')) ?: [];

    expect(count($files))->toBe(55);

    foreach ($files as $file) {
        $class = 'App\\Models\\'.basename($file, '.php');

        expect(class_exists($class))->toBeTrue("kelas {$class} tidak dapat dimuat");

        /** @var Model $model */
        $model = new $class;

        expect(Schema::hasTable($model->getTable()))->toBeTrue("tabel {$model->getTable()} tidak ada");
    }
});

it('memastikan semua kolom fillable ada di database', function () {
    foreach (glob(app_path('Models/*.php')) ?: [] as $file) {
        $class = 'App\\Models\\'.basename($file, '.php');

        /** @var Model $model */
        $model = new $class;
        $table = $model->getTable();

        foreach ($model->getFillable() as $column) {
            expect(Schema::hasColumn($table, $column))->toBeTrue("kolom {$table}.{$column} tidak ada");
        }
    }
});
