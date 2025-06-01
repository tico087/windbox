<?php

namespace WindBox\Application\UseCases\Commands;

use Carbon\Carbon;

class StoreWindCommand
{
    public string $location;
    public float $windSpeedKph;
    public float $volumeM3;
    public string $qualityRating;
    public ?Carbon $expiresAt;

    public function __construct(
        string $location,
        float $windSpeedKph,
        float $volumeM3,
        string $qualityRating,
        ?Carbon $expiresAt = null
    ) {
        $this->location = $location;
        $this->windSpeedKph = $windSpeedKph;
        $this->volumeM3 = $volumeM3;
        $this->qualityRating = $qualityRating;
        $this->expiresAt = $expiresAt;
    }
}