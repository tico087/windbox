<?php

namespace WindBox\Application\Services;

use WindBox\Application\UseCases\Commands\StoreWindCommand;
use WindBox\Domain\Ports\WindPacketRepository;
use WindBox\Domain\Entities\WindPacket;
use Carbon\Carbon;

class StoreWindService
{
    private WindPacketRepository $windPacketRepository;

    public function __construct(WindPacketRepository $windPacketRepository)
    {
        $this->windPacketRepository = $windPacketRepository;
    }

    public function execute(StoreWindCommand $command): WindPacket
    {
        $windPacket = new WindPacket(
            null, // ID é nulo na criação; será definido pela persistência
            $command->location,
            $command->windSpeedKph,
            $command->volumeM3,
            $command->qualityRating,
            Carbon::now(),
            $command->expiresAt
        );

        $this->windPacketRepository->save($windPacket);

        return $windPacket;
    }
}