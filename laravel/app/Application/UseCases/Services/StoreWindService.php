<?php

namespace App\Application\UseCases\Services;

use App\Application\UseCases\Commands\StoreWindCommand;
use App\Domain\Ports\WindPacketRepository;
use App\Domain\Entities\WindPacket;

class StoreWindService
{
    private WindPacketRepository $windPacketRepository;

    public function __construct(WindPacketRepository $windPacketRepository)
    {
        $this->windPacketRepository = $windPacketRepository;
    }

    public function execute(StoreWindCommand $command): WindPacket
    {
        $windPacket = new WindPacket([
            'location' => $command->location,
            'wind_speed_kph' => $command->windSpeedKph,
            'volume_m3' => $command->volumeM3,
            'quality_rating' => $command->qualityRating,
            'stored_at' => now(),
            'expires_at' => $command->expiresAt,
        ]);

        $this->windPacketRepository->save($windPacket);

        return $windPacket;
    }
}
