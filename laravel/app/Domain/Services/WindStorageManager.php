<?php

namespace App\Domain\Services;

use App\Domain\Ports\WindPacketRepository;
use App\Domain\Ports\WindStorageService;
use App\Domain\Exceptions\InsufficientWindException; // Vamos criar essa exceção

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

        // Complex logic to select and "allocate" (remove) specific wind packets
        // For simplicity, we'll just decrement the total volume conceptually here.
        // In a real WMS, you'd iterate through packets, mark them as allocated/shipped, etc.
        $availablePackets = $this->windPacketRepository->findAvailableByLocation($location, 100); // Fetch many
        $allocatedVolume = 0.0;

        foreach ($availablePackets as $packet) {
            if ($allocatedVolume >= $volumeM3) {
                break;
            }
            $currentPacketVolume = $packet->volume_m3;
            $volumeToTake = min($volumeM3 - $allocatedVolume, $currentPacketVolume);

            // This is simplified: in reality, you might split packets or update their volume
            if ($volumeToTake == $currentPacketVolume) {
                $this->windPacketRepository->remove($packet);
            } else {
                // Update packet's remaining volume and save
                $packet->volume_m3 -= $volumeToTake;
                $this->windPacketRepository->save($packet);
            }
            $allocatedVolume += $volumeToTake;
        }

        return $allocatedVolume >= $volumeM3;
    }

    public function deallocateWind(string $location, float $volumeM3): void
    {
        // This would involve finding an existing allocated "order" and marking its wind as available again
        // For simplicity, this example doesn't fully implement deallocation.
        // It would likely involve creating new WindPacket records or updating existing ones.
        // Example: creating a "return" packet
        // $returnedPacket = new WindPacket([
        //     'location' => $location,
        //     'wind_speed_kph' => 0, // Or average from the original allocation
        //     'volume_m3' => $volumeM3,
        //     'quality_rating' => 'A', // Or original quality
        //     'stored_at' => now(),
        //     'expires_at' => now()->addYear(),
        // ]);
        // $this->windPacketRepository->save($returnedPacket);
    }

    public function getAvailableVolume(string $location): float
    {
        return $this->windPacketRepository->getAvailableVolume($location);
    }
}
