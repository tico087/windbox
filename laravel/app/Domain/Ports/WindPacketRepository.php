<?php

namespace App\Domain\Ports;

use App\Domain\Entities\WindPacket;
use Illuminate\Support\Collection;

interface WindPacketRepository
{
    public function save(WindPacket $packet): void;
    public function findById(int $id): ?WindPacket;
    public function findAvailableByLocation(string $location, int $limit = 1): Collection;
    public function remove(WindPacket $packet): void;
    public function getAvailableVolume(string $location): float;
}
