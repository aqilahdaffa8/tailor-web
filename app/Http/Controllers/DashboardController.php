<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    /**
     * Halaman dashboard. Permintaan AJAX (auto-refresh) hanya menerima potongan widget.
     */
    public function index(Request $request): View
    {
        return view(
            $request->ajax() ? 'dashboard._widget' : 'dashboard',
            $this->dashboardService->dataDashboard()
        );
    }
}
