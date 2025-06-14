<?php

namespace WindBox\Infrastructure\Http\Controllers;

use WindBox\Application\Services\StoreWindService;
use WindBox\Application\UseCases\Commands\StoreWindCommand;
use WindBox\Application\Services\AllocateWindService;
use WindBox\Application\UseCases\Commands\AllocateWindCommand;
use WindBox\Application\Services\GetAvailableWindService;
use WindBox\Infrastructure\Http\Request;
use WindBox\Infrastructure\Http\Response;
use Carbon\Carbon;

class WindStockController
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

    public function storeWind(Request $request, Response $response): string
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
            (float) $request->input('wind_speed_kph'),
            (float) $request->input('volume_m3'),
            $request->input('quality_rating'),
            $request->input('expires_at') ? Carbon::parse($request->input('expires_at')) : null
        );

        $windPacket = $this->storeWindService->execute($command);

        return $response->json([
            'message' => 'Wind stored successfully!',
            'packet_id' => $windPacket->getId(),
            'current_volume' => $windPacket->getVolumeM3()
        ], 201);
    }

    public function allocateWind(Request $request, Response $response): string
    {
        $request->validate([
            'location' => 'required|string',
            'volume_m3' => 'required|numeric|min:0',
        ]);

        $command = new AllocateWindCommand(
            $request->input('location'),
            (float) $request->input('volume_m3')
        );

        $this->allocateWindService->execute($command);

        return $response->json(['message' => 'Wind allocated successfully!'], 200);
    }

    public function getAvailableWind(string $location, Request $request, Response $response): string
    {
        
        $location = $request->input('location') ?? $location;
        if (empty($location)) {
             throw new \InvalidArgumentException("Location parameter is required.");
        }
        $volume = $this->getAvailableWindService->execute($location);
        return $response->json(['location' => $location, 'available_volume_m3' => $volume], 200);
    }
}