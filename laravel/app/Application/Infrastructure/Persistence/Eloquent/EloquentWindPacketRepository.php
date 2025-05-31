<?php

namespace App\Application\Infrastructure\Persistence\Eloquent;

use App\Domain\Ports\WindPacketRepository;
use App\Domain\Entities\WindPacket;
use Illuminate\Support\Collection;

class EloquentWindPacketRepository implements WindPacketRepository
{
    public function save(WindPacket $packet): void
    {
        $packet->save();
    }

    public function findById(int $id): ?WindPacket
    {
        return WindPacket::find($id);
    }

    public function findAvailableByLocation(string $location, int $limit = 1): Collection
    {
        return WindPacket::where('location', $location)
                         ->where('volume_m3', '>', 0)
                         ->where(function ($query) {
                             $query->whereNull('expires_at')
                                   ->orWhere('expires_at', '>', now());
                         })
                         ->orderBy('stored_at', 'asc') // FIFO
                         ->limit($limit)
                         ->get();
    }

    public function remove(WindPacket $packet): void
    {
        $packet->delete();
    }

    public function getAvailableVolume(string $location): float
    {
        return WindPacket::where('location', $location)
                         ->where('volume_m3', '>', 0)
                         ->where(function ($query) {
                             $query->whereNull('expires_at')
                                   ->orWhere('expires_at', '>', now());
                         })
                         ->sum('volume_m3');
    }
}
