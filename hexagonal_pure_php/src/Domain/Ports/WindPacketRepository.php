<?php

namespace WindBox\Domain\Ports;

use WindBox\Domain\Entities\WindPacket;
use SplQueue; // simulate collections - Illuminate\Support\Collection

interface WindPacketRepository
{
    public function save(WindPacket $packet): void;
    public function findById(int $id): ?WindPacket;
    public function findAvailableByLocation(string $location, int $limit = 1): SplQueue;
    public function remove(WindPacket $packet): void;
    public function getAvailableVolume(string $location): float;
}