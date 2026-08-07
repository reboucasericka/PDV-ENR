<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->dashboardService->getDashboard(
            $request->user()?->id ? (int) $request->user()->id : null
        );

        return response()->json([
            'message' => 'Dashboard administrativo.',
            'data' => $data,
        ]);
    }
}
