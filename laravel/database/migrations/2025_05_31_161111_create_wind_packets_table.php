<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wind_packets', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->float('wind_speed_kph');
            $table->float('volume_m3');
            $table->string('quality_rating');
            $table->timestamp('stored_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wind_packets');
    }
};
