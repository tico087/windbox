<?php

namespace App\Application\UseCases\Services;

use App\Application\UseCases\Commands\AllocateWindCommand;
use App\Domain\Ports\WindStorageService;

class AllocateWindService
{
    private WindStorageService $windStorageService;

    public function __construct(WindStorageService $windStorageService)
    {
        $this->windStorageService = $windStorageService;
    }

    public function execute(AllocateWindCommand $command): bool
    {
        return $this->windStorageService->allocateWind($command->location, $command->volumeM3);
    }
}
