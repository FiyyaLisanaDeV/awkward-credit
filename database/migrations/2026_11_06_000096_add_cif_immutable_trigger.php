<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION debitur_cif_immutable() RETURNS trigger AS $$
            BEGIN
                IF OLD.cif IS NOT NULL AND NEW.cif IS DISTINCT FROM OLD.cif THEN
                    RAISE EXCEPTION 'CIF immutable';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        DB::statement('CREATE TRIGGER debitur_cif_immutable_trigger BEFORE UPDATE OF cif ON debitur FOR EACH ROW EXECUTE FUNCTION debitur_cif_immutable()');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS debitur_cif_immutable_trigger ON debitur');
        DB::statement('DROP FUNCTION IF EXISTS debitur_cif_immutable()');
    }
};
