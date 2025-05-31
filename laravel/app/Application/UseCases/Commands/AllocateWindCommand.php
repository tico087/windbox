<?php

namespace App\Application\UseCases\Commands;

class AllocateWindCommand
{
    public string $location;
    public float $volumeM3;

    public function __construct(string $location, float $volumeM3)
    {
        $this->location = $location;
        $this->volumeM3 = $volumeM3;
    }
}
