<?php

namespace WindBox\Infrastructure\Persistence\JsonFile;

use WindBox\Domain\Ports\WindPacketRepository;
use WindBox\Domain\Entities\WindPacket;
use WindBox\Domain\Exceptions\WindPacketNotFoundException;
use Carbon\Carbon;
use SplQueue;

class JsonWindPacketRepository implements WindPacketRepository
{
    private string $filePath;
    private int $nextId = 1;

    public function __construct(string $filePath = __DIR__ . '/../../../storage/wind_packets.json')
    { 
        $this->filePath = $filePath;
        $this->ensureFileExists();
        $this->loadNextId();
    }

    private function ensureFileExists(): void
    {  
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([]));
        }
    }

    private function readAll(): array
    {
        $content = file_get_contents($this->filePath);
        return json_decode($content, true) ?? [];
    }

    private function writeAll(array $data): void
    {
        file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function loadNextId(): void
    {
        $data = $this->readAll();
        if (empty($data)) {
            $this->nextId = 1;
        } else {
            $maxId = 0;
            foreach ($data as $item) {
                if (isset($item['id']) && $item['id'] > $maxId) {
                    $maxId = $item['id'];
                }
            }
            $this->nextId = $maxId + 1;
        }
    }

    public function save(WindPacket $packet): void
    {
        $data = $this->readAll();
        $isUpdate = false;

        if ($packet->getId() !== null) {
            foreach ($data as $key => $item) {
                if ($item['id'] === $packet->getId()) {
                    $data[$key] = $this->packetToArray($packet);
                    $isUpdate = true;
                    break;
                }
            }
        }

        if (!$isUpdate) {
            $packet->setId($this->nextId++); // Set ID before saving new packet
            $data[] = $this->packetToArray($packet);
        }

        $this->writeAll($data);
    }

    public function findById(int $id): ?WindPacket
    {
        $data = $this->readAll();
        foreach ($data as $item) {
            if ($item['id'] === $id) {
                return $this->arrayToPacket($item);
            }
        }
        return null;
    }

    public function findAvailableByLocation(string $location, int $limit = 1): SplQueue
    {
        $queue = new SplQueue();
        $data = $this->readAll();

        foreach ($data as $item) {
            $packet = $this->arrayToPacket($item);
            // Verifica localização, volume positivo e se não expirou
            if ($packet->getLocation() === $location && $packet->getVolumeM3() > 0 && !$packet->isExpired()) {
                $queue->enqueue($packet);
            }
        }

        // Ordenar a fila por stored_at (FIFO)
        $sortedArray = iterator_to_array($queue);
        usort($sortedArray, function($a, $b) {
            return $a->getStoredAt()->getTimestamp() <=> $b->getStoredAt()->getTimestamp();
        });

        // Limitar o número de resultados e recriar a fila
        $limitedQueue = new SplQueue();
        $count = 0;
        foreach ($sortedArray as $packet) {
            if ($count >= $limit) {
                break;
            }
            $limitedQueue->enqueue($packet);
            $count++;
        }

        return $limitedQueue;
    }


    public function remove(WindPacket $packet): void
    {
        $data = $this->readAll();
        $newData = [];
        $found = false;
        foreach ($data as $item) {
            if ($item['id'] === $packet->getId()) {
                $found = true;
                continue;
            }
            $newData[] = $item;
        }
        if (!$found) {
            throw new WindPacketNotFoundException("WindPacket with ID {$packet->getId()} not found for removal.");
        }
        $this->writeAll($newData);
    }

    public function getAvailableVolume(string $location): float
    {
        $data = $this->readAll();
        $totalVolume = 0.0;
        foreach ($data as $item) {
            $packet = $this->arrayToPacket($item);
            if ($packet->getLocation() === $location && $packet->getVolumeM3() > 0 && !$packet->isExpired()) {
                $totalVolume += $packet->getVolumeM3();
            }
        }
        return $totalVolume;
    }

    private function packetToArray(WindPacket $packet): array
    {
        return [
            'id' => $packet->getId(),
            'location' => $packet->getLocation(),
            'wind_speed_kph' => $packet->getWindSpeedKph(),
            'volume_m3' => $packet->getVolumeM3(),
            'quality_rating' => $packet->getQualityRating(),
            'stored_at' => $packet->getStoredAt()->toIso8601String(),
            'expires_at' => $packet->getExpiresAt() ? $packet->getExpiresAt()->toIso8601String() : null,
        ];
    }

    private function arrayToPacket(array $data): WindPacket
    {
        return new WindPacket(
            $data['id'],
            $data['location'],
            $data['wind_speed_kph'],
            $data['volume_m3'],
            $data['quality_rating'],
            Carbon::parse($data['stored_at']),
            isset($data['expires_at']) && $data['expires_at'] ? Carbon::parse($data['expires_at']) : null
        );
    }
}