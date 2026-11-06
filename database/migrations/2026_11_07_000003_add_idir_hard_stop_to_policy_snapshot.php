<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('ref_parameter_kebijakan')->get() as $row) {
            $params = json_decode((string) $row->params, true);

            if (! is_array($params) || array_key_exists('idir_hard_stop_tpp', $params)) {
                continue;
            }

            $params['idir_hard_stop_tpp'] = 0.80;

            DB::table('ref_parameter_kebijakan')->where('id', $row->id)->update([
                'params' => json_encode($params, JSON_THROW_ON_ERROR),
            ]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('ref_parameter_kebijakan')->get() as $row) {
            $params = json_decode((string) $row->params, true);

            if (! is_array($params)) {
                continue;
            }

            unset($params['idir_hard_stop_tpp']);

            DB::table('ref_parameter_kebijakan')->where('id', $row->id)->update([
                'params' => json_encode($params, JSON_THROW_ON_ERROR),
            ]);
        }
    }
};
