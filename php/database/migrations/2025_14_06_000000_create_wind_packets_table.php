<?php


use WindBox\Infrastructure\Persistence\Migrations\Migration;


class CreateWindPacketsTable implements Migration
{
    public function up(PDO $pdo): void
    {

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                migrated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");


        $pdo->exec("
            CREATE TABLE IF NOT EXISTS wind_packets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                location VARCHAR(255) NOT NULL,
                wind_speed_kph DECIMAL(8, 2) NOT NULL,
                volume_m3 DECIMAL(10, 2) NOT NULL,
                quality_rating VARCHAR(255) NOT NULL,
                stored_at DATETIME NOT NULL,
                expires_at DATETIME NULL
            );
        ");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS wind_packets;");
        $pdo->exec("DROP TABLE IF EXISTS migrations;"); 
    }
}