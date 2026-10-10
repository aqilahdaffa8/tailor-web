<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    /**
     * GET /api/dashboard/summary
     * Semua metric ringkasan.
     */
    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->ringkasan(),
        ]);
    }

    /**
     * GET /api/dashboard/pesanan-aktif?limit=10
     */
    public function pesananAktif(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->pesananAktif($request->integer('limit', 10)),
        ]);
    }

    /**
     * GET /api/dashboard/pesanan-mendekati-deadline?limit=10
     */
    public function pesananMendekatiDeadline(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->pesananMendekatiDeadline($request->integer('limit', 10)),
        ]);
    }

    /**
     * GET /api/dashboard/pesanan-terlambat?limit=10
     */
    public function pesananTerlambat(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->pesananTerlambat($request->integer('limit', 10)),
        ]);
    }

    /**
     * GET /api/dashboard/stok-menipis?limit=10
     */
    public function stokMenipis(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->stokMenipis($request->integer('limit', 10)),
        ]);
    }
}
