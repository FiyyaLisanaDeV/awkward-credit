<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Policy\PolicyParameters;
use App\Models\RefParameterKebijakan;
use App\Models\SimulasiKredit;
use App\Services\Ledger\ChainHasher;
use App\Services\Simulation\SimulationEngine;
use Illuminate\Console\Command;

final class SimulationReplay extends Command
{
    protected $signature = 'simulasi:replay {id : ID simulasi}';

    protected $description = 'Jalankan ulang simulasi dan bandingkan output kanonik';

    public function handle(): int
    {
        $simulasi = SimulasiKredit::query()->find($this->argument('id'));

        if ($simulasi === null) {
            $this->error('Simulasi tidak ditemukan.');

            return self::FAILURE;
        }

        $parameter = RefParameterKebijakan::query()->find($simulasi->parameter_kebijakan_id);

        if ($parameter === null) {
            $this->error('Parameter kebijakan simulasi tidak ditemukan.');

            return self::FAILURE;
        }

        $output = app(SimulationEngine::class)->run(
            is_array($simulasi->input) ? $simulasi->input : [],
            PolicyParameters::fromModel($parameter),
        );

        $expected = ChainHasher::canonicalJson(is_array($simulasi->output) ? $simulasi->output : []);
        $actual = ChainHasher::canonicalJson($output);

        if ($expected === $actual) {
            $this->info('simulasi:replay OK — output identik');

            return self::SUCCESS;
        }

        $this->error('simulasi:replay GAGAL — output berbeda');
        $this->line('expected: '.$expected);
        $this->line('actual  : '.$actual);

        return self::FAILURE;
    }
}
