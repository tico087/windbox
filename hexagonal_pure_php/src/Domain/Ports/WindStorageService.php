<?php

namespace WindBox\Domain\Ports;

interface WindStorageService
{
    public function allocateWind(string $location, float $volumeM3): bool;
    public function deallocateWind(string $location, float $volumeM3): void;
    public function getAvailableVolume(string $location): float;
}