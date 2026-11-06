<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class BackfillNomorSequenceSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement(<<<'SQL'
            INSERT INTO nomor_sequence (scope, last_number, updated_at)
            SELECT
                LOWER('permohonan:' || SPLIT_PART(no_permohonan, '/', 1) || ':' || SPLIT_PART(no_permohonan, '/', 2)) AS scope,
                MAX(CAST(SUBSTRING(no_permohonan FROM '([0-9]+)$') AS BIGINT)) AS last_number,
                CURRENT_TIMESTAMP
            FROM permohonan_kredit
            WHERE no_permohonan ~ '^[A-Z0-9]+/[0-9]{4}/[0-9]+$'
            GROUP BY 1
            ON CONFLICT (scope) DO UPDATE
            SET last_number = GREATEST(nomor_sequence.last_number, EXCLUDED.last_number),
                updated_at = CURRENT_TIMESTAMP
        SQL);

        DB::statement(<<<'SQL'
            INSERT INTO nomor_sequence (scope, last_number, updated_at)
            SELECT
                LOWER('kredit:' || SPLIT_PART(no_rekening_kredit, '/', 1) || ':' || SPLIT_PART(no_rekening_kredit, '/', 2)) AS scope,
                MAX(CAST(SUBSTRING(no_rekening_kredit FROM '([0-9]+)$') AS BIGINT)) AS last_number,
                CURRENT_TIMESTAMP
            FROM kredit
            WHERE no_rekening_kredit ~ '^[A-Z0-9]+/[0-9]{4}/K/[0-9]+$'
            GROUP BY 1
            ON CONFLICT (scope) DO UPDATE
            SET last_number = GREATEST(nomor_sequence.last_number, EXCLUDED.last_number),
                updated_at = CURRENT_TIMESTAMP
        SQL);

        DB::statement(<<<'SQL'
            INSERT INTO nomor_sequence (scope, last_number, updated_at)
            SELECT
                LOWER('dokumen:' || SPLIT_PART(nomor_dokumen, '/', 1) || ':' || SPLIT_PART(nomor_dokumen, '/', 2) || ':' || SPLIT_PART(nomor_dokumen, '/', 3)) AS scope,
                MAX(CAST(SUBSTRING(nomor_dokumen FROM '([0-9]+)$') AS BIGINT)) AS last_number,
                CURRENT_TIMESTAMP
            FROM dokumen
            WHERE nomor_dokumen ~ '^[A-Z0-9]+/[A-Z0-9]+/[0-9]{4}/[0-9]+$'
            GROUP BY 1
            ON CONFLICT (scope) DO UPDATE
            SET last_number = GREATEST(nomor_sequence.last_number, EXCLUDED.last_number),
                updated_at = CURRENT_TIMESTAMP
        SQL);
    }
}
