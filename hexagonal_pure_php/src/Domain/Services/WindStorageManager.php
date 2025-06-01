<?php

namespace WindBox\Domain\Services;

use WindBox\Domain\Ports\WindPacketRepository;
use WindBox\Domain\Ports\WindStorageService;
use WindBox\Domain\Exceptions\InsufficientWindException;

class WindStorageManager implements WindStorageService
{
    private WindPacketRepository $windPacketRepository;

    public function __construct(WindPacketRepository $windPacketRepository)
    {
        $this->windPacketRepository = $windPacketRepository;
    }

    public function allocateWind(string $location, float $volumeM3): bool
    {
        if ($this->windPacketRepository->getAvailableVolume($location) < $volumeM3) {
            throw new InsufficientWindException("Not enough wind available at location: {$location}");
        }

        $availablePackets = $this->windPacketRepository->findAvailableByLocation($location, 100);
        $allocatedVolume = 0.0;

      
        while (!$availablePackets->isEmpty()) {
            $packet = $availablePackets->dequeue(); 

            if ($allocatedVolume >= $volumeM3) {
                
                $availablePackets->enqueue($packet);
                break;
            }

            $currentPacketVolume = $packet->getVolumeM3();
            $volumeToTake = min($volumeM3 - $allocatedVolume, $currentPacketVolume);

            if ($volumeToTake == $currentPacketVolume) {
              
                $this->windPacketRepository->remove($packet);
            } else {
              
                $packet->setVolumeM3($currentPacketVolume - $volumeToTake);
                $this->windPacketRepository->save($packet);
            }
            $allocatedVolume += $volumeToTake;
        }

        return $allocatedVolume >= $volumeM3;
    }

    public function deallocateWind(string $location, float $volumeM3): void
    {
        //@todo logic 
    }

    public function getAvailableVolume(string $location): float
    {
        return $this->windPacketRepository->getAvailableVolume($location);
    }
}