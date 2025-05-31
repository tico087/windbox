<?php

namespace App\Application\UseCases\Services;

use App\Domain\Ports\WindStorageService;

class GetAvailableWindService
{
    private WindStorageService $windStorageService;

    public function __construct(WindStorageService $windStorageService)
    {
        $this->windStorageService = $windStorageService;
    }

    public function execute(string $location): float
    {
        return $this->windStorageService->getAvailableVolume($location);
    }
}
