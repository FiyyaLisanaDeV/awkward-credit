<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        PasswordRule::defaults(function (): PasswordRule {
            $production = app()->isProduction();

            $rule = PasswordRule::min(
                (int) config($production ? 'loan.security.password_min_production' : 'loan.security.password_min')
            );

            return $production
                ? $rule->mixedCase()->numbers()->symbols()
                : $rule;
        });

        DB::listen(function (QueryExecuted $query): void {
            $threshold = (int) config('loan.security.slow_query_ms');

            if ($query->time > $threshold) {
                Log::channel('performance')->warning('slow query', [
                    'sql' => $query->sql,
                    'ms' => $query->time,
                ]);
            }
        });

        if (app()->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
