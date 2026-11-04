<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => 'ok',
            'db' => $this->probe(fn (): bool => DB::connection()->getPdo() !== null),
            'cache' => $this->probe(function (): bool {
                Cache::put('health:probe', 'ok', now()->addMinute());
                $value = Cache::get('health:probe');
                Cache::forget('health:probe');

                return $value === 'ok';
            }),
            'storage' => $this->probe(function (): bool {
                $disk = Storage::disk((string) config('loan.dokumen.disk'));
                $path = 'health-probe.txt';
                $disk->put($path, 'ok');
                $ok = $disk->exists($path);
                $disk->delete($path);

                return $ok;
            }),
            'queue' => (string) config('queue.default'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'migrations' => $this->migrationCount(),
            'ledger_chain' => 'n/a',
            'timestamp' => now()->toIso8601String(),
        ];

        $failed = $checks['db'] !== 'ok' || $checks['cache'] !== 'ok' || $checks['storage'] !== 'ok';

        return response()->json($checks, $failed ? 503 : 200);
    }

    /**
     * @param  callable(): bool  $check
     */
    private function probe(callable $check): string
    {
        try {
            return $check() ? 'ok' : 'fail';
        } catch (Throwable) {
            return 'fail';
        }
    }

    private function migrationCount(): int
    {
        try {
            return DB::table('migrations')->count();
        } catch (Throwable) {
            return -1;
        }
    }
}
