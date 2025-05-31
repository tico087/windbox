<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WindPacket extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'wind_speed_kph',
        'volume_m3',
        'quality_rating',
        'stored_at',
        'expires_at',
    ];

    protected $casts = [
        'wind_speed_kph' => 'float',
        'volume_m3' => 'float',
        'stored_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
