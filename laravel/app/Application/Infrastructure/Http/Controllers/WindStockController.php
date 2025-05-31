<?php

namespace App\Application\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Application\UseCases\Services\StoreWindService;
use App\Application\UseCases\Commands\StoreWindCommand;
use App\Application\UseCases\Services\AllocateWindService;
use App\Application\UseCases\Commands\AllocateWindCommand;
use App\Application\UseCases\Services\GetAvailableWindService;
use App\Domain\Exceptions\InsufficientWindException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class WindStockController extends Controller
{
    private StoreWindService $storeWindService;
    private AllocateWindService $allocateWindService;
    private GetAvailableWindService $getAvailableWindService;

    public function __construct(
        StoreWindService $storeWindService,
        AllocateWindService $allocateWindService,
        GetAvailableWindService $getAvailableWindService
    ) {
        $this->storeWindService = $storeWindService;
        $this->allocateWindService = $allocateWindService;
        $this->getAvailableWindService = $getAvailableWindService;
    }

    public function storeWind(Request $request): JsonResponse
    {
        $request->validate([
            'location' => 'required|string',
            'wind_speed_kph' => 'required|numeric|min:0',
            'volume_m3' => 'required|numeric|min:0',
            'quality_rating' => 'required|in:A,B,C',
            'expires_at' => 'nullable|date',
        ]);

        $command = new StoreWindCommand(
            $request->input('location'),
            $request->input('wind_speed_kph'),
            $request->input('volume_m3'),
            $request->input('quality_rating'),
            $request->has('expires_at') ? Carbon::parse($request->input('expires_at')) : null
        );

        $windPacket = $this->storeWindService->execute($command);

        return response()->json([
            'message' => 'Wind stored successfully!',
            'packet_id' => $windPacket->id,
            'current_volume' => $windPacket->volume_m3
        ], 201);
    }

    public function allocateWind(Request $request): JsonResponse
    {
        $request->validate([
            'location' => 'required|string',
            'volume_m3' => 'required|numeric|min:0',
        ]);

        $command = new AllocateWindCommand(
            $request->input('location'),
            $request->input('volume_m3')
        );

        try {
            $allocated = $this->allocateWindService->execute($command);
            if ($allocated) {
                return response()->json(['message' => 'Wind allocated successfully!'], 200);
            }
            return response()->json(['message' => 'Failed to allocate wind.'], 500); // Should be caught by exception
        } catch (InsufficientWindException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    public function getAvailableWind(string $location): JsonResponse
    {
        $volume = $this->getAvailableWindService->execute($location);
        return response()->json(['location' => $location, 'available_volume_m3' => $volume], 200);
    }
}
