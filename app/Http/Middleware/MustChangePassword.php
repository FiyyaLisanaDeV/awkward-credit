<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class MustChangePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->must_change_password && ! $request->routeIs('password.*', 'logout')) {
            return redirect()->route('password.change')
                ->with('status', __('auth.must_change_password'));
        }

        return $next($request);
    }
}
