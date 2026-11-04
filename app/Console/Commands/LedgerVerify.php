<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Ledger;
use App\Services\Ledger\ChainHasher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

final class LedgerVerify extends Command
{
    protected $signature = 'ledger:verify {--from= : seq awal} {--to= : seq akhir}';

    protected $description = 'Verifikasi hash chain ledger';

    public function handle(): int
    {
        $query = Ledger::query()->orderBy('seq');

        if ($this->option('from') !== null) {
            $query->where('seq', '>=', (int) $this->option('from'));
        }

        if ($this->option('to') !== null) {
            $query->where('seq', '<=', (int) $this->option('to'));
        }

        $previous = ChainHasher::genesis();
        $checked = 0;
        $errors = [];

        foreach ($query->cursor() as $row) {
            $expected = ChainHasher::hash(
                $previous,
                is_array($row->payload) ? $row->payload : [],
                ChainHasher::time(Carbon::parse((string) $row->occurred_at)),
                (string) $row->event,
                (string) $row->entity_type,
                $row->entity_id,
            );

            if ($row->prev_hash !== $previous) {
                $errors[] = sprintf('seq %d: prev_hash tidak cocok dengan hash baris sebelumnya', $row->seq);
            }

            if ($row->hash !== $expected) {
                $errors[] = sprintf('seq %d: hash tidak cocok dengan isi baris', $row->seq);
            }

            $previous = (string) $row->hash;
            $checked++;
        }

        if ($errors !== []) {
            foreach (array_slice($errors, 0, 20) as $error) {
                $this->error($error);
            }

            $this->error(sprintf('ledger:verify gagal — %d baris diperiksa, %d masalah', $checked, count($errors)));

            return self::FAILURE;
        }

        $this->info(sprintf('ledger:verify OK — %d baris, rantai utuh', $checked));

        return self::SUCCESS;
    }
}
