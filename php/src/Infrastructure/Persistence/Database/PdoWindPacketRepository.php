<?php

namespace WindBox\Infrastructure\Persistence\Database;

use WindBox\Domain\Ports\WindPacketRepository;
use WindBox\Domain\Entities\WindPacket;
use WindBox\Domain\Exceptions\WindPacketNotFoundException;
use PDO;
use Carbon\Carbon;
use SplQueue;

class PdoWindPacketRepository implements WindPacketRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(WindPacket $packet): void
    {
        if ($packet->getId() === null) {
          
            $stmt = $this->pdo->prepare("
                INSERT INTO wind_packets (location, wind_speed_kph, volume_m3, quality_rating, stored_at, expires_at)
                VALUES (:location, :wind_speed_kph, :volume_m3, :quality_rating, :stored_at, :expires_at)
            ");

            $stmt->execute([
                ':location' => $packet->getLocation(),
                ':wind_speed_kph' => $packet->getWindSpeedKph(),
                ':volume_m3' => $packet->getVolumeM3(),
                ':quality_rating' => $packet->getQualityRating(),
                ':stored_at' => $packet->getStoredAt()->format('Y-m-d H:i:s'),
                ':expires_at' => $packet->getExpiresAt() ? $packet->getExpiresAt()->format('Y-m-d H:i:s') : null,
            ]);

            
            $packet->setId((int) $this->pdo->lastInsertId());

        } else {
           
            $stmt = $this->pdo->prepare("
                UPDATE wind_packets
                SET location = :location,
                    wind_speed_kph = :wind_speed_kph,
                    volume_m3 = :volume_m3,
                    quality_rating = :quality_rating,
                    stored_at = :stored_at,
                    expires_at = :expires_at
                WHERE id = :id
            ");

            $stmt->execute([
                ':id' => $packet->getId(),
                ':location' => $packet->getLocation(),
                ':wind_speed_kph' => $packet->getWindSpeedKph(),
                ':volume_m3' => $packet->getVolumeM3(),
                ':quality_rating' => $packet->getQualityRating(),
                ':stored_at' => $packet->getStoredAt()->format('Y-m-d H:i:s'),
                ':expires_at' => $packet->getExpiresAt() ? $packet->getExpiresAt()->format('Y-m-d H:i:s') : null,
            ]);
        }
    }

    public function findById(int $id): ?WindPacket
    {
        $stmt = $this->pdo->prepare("SELECT * FROM wind_packets WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null; 
        }

        return $this->arrayToPacket($data);
    }

    public function findAvailableByLocation(string $location, int $limit = 1): SplQueue
    {
        $queue = new SplQueue();
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM wind_packets
            WHERE location = :location
              AND volume_m3 > 0
              AND (expires_at IS NULL OR expires_at > NOW())
            ORDER BY stored_at ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':location', $location);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT); 
        $stmt->execute();
        
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $queue->enqueue($this->arrayToPacket($data));
        }

        return $queue;
    }

    public function remove(WindPacket $packet): void
    {
        if ($packet->getId() === null) {
            throw new WindPacketNotFoundException("WindPacket cannot be removed without an ID.");
        }

        $stmt = $this->pdo->prepare("DELETE FROM wind_packets WHERE id = :id");
        $stmt->execute([':id' => $packet->getId()]);

        if ($stmt->rowCount() === 0) {
            throw new WindPacketNotFoundException("WindPacket with ID {$packet->getId()} not found for removal.");
        }
    }

    public function getAvailableVolume(string $location): float
    {
        $stmt = $this->pdo->prepare("
            SELECT SUM(volume_m3) AS total_volume
            FROM wind_packets
            WHERE location = :location
              AND volume_m3 > 0
              AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([':location' => $location]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (float) ($result['total_volume'] ?? 0.0);
    }


    private function arrayToPacket(array $data): WindPacket
    {
        return new WindPacket(
            $data['id'],
            $data['location'],
            (float) $data['wind_speed_kph'],
            (float) $data['volume_m3'],
            (int) $data['quality_rating'],
            Carbon::parse($data['stored_at']),
            isset($data['expires_at']) && $data['expires_at'] ? Carbon::parse($data['expires_at']) : null
        );
    }
}