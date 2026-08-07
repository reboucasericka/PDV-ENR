<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settingService) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Settings list',
            'data' => $this->settingService->list(),
        ]);
    }

    public function current(): JsonResponse
    {
        return response()->json([
            'message' => 'Current settings',
            'data' => $this->settingService->current(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $setting = $this->settingService->find($id);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }

        return response()->json([
            'message' => 'Setting details',
            'data' => $setting,
        ]);
    }

    public function store(StoreSettingRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo');
        }

        $setting = $this->settingService->create($data);

        return response()->json([
            'message' => 'Setting created',
            'data' => $setting,
        ], 201);
    }

    public function update(UpdateSettingRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo');
        }

        try {
            $setting = $this->settingService->update($id, $data);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }

        return response()->json([
            'message' => 'Setting updated',
            'data' => $setting,
        ]);
    }

    public function updateCurrent(UpdateSettingRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo');
        }

        $setting = $this->settingService->updateCurrent($data);

        return response()->json([
            'message' => 'Settings saved',
            'data' => $setting,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->settingService->delete($id);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }

        return response()->json([
            'message' => 'Setting deleted',
        ]);
    }
}
