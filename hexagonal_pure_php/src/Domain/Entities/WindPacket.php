<?php

namespace WindBox\Domain\Entities;

use Carbon\Carbon;

class WindPacket
{
    private ?int $id;
    private string $location;
    private float $windSpeedKph;
    private float $volumeM3;
    private string $qualityRating;
    private Carbon $storedAt;
    private ?Carbon $expiresAt;

    public function __construct(
        ?int $id,
        string $location,
        float $windSpeedKph,
        float $volumeM3,
        string $qualityRating,
        Carbon $storedAt,
        ?Carbon $expiresAt = null
    ) {
        $this->id = $id;
        $this->location = $location;
        $this->windSpeedKph = $windSpeedKph;
        $this->volumeM3 = $volumeM3;
        $this->qualityRating = $qualityRating;
        $this->storedAt = $storedAt;
        $this->expiresAt = $expiresAt;
    }

    // Getters para acessar as propriedades
    public function getId(): ?int { return $this->id; }
    public function getLocation(): string { return $this->location; }
    public function getWindSpeedKph(): float { return $this->windSpeedKph; }
    public function getVolumeM3(): float { return $this->volumeM3; }
    public function getQualityRating(): string { return $this->qualityRating; }
    public function getStoredAt(): Carbon { return $this->storedAt; }
    public function getExpiresAt(): ?Carbon { return $this->expiresAt; }

    // Setters (se necessário para a lógica de domínio, ou métodos de atualização)
    public function setVolumeM3(float $volumeM3): void { $this->volumeM3 = $volumeM3; }
    public function setId(int $id): void { $this->id = $id; } // Usado pela camada de persistência

    public function isExpired(): bool
    {
        return $this->expiresAt && $this->expiresAt->isPast();
    }
}