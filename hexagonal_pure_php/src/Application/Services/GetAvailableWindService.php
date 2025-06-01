<?php

namespace WindBox\Application\Services;

use WindBox\Domain\Ports\WindStorageService;

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