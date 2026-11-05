<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboard): View
    {
        return view('dashboard.index', $dashboard->ringkasan($request->user()));
    }
}
