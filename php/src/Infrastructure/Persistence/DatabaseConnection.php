<?php

namespace WindBox\Infrastructure\Persistence;

use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?PDO $instance = null;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function connect(): PDO
    {
        if (self::$instance === null) {
            $dsn = $this->getDsn();
            try {
                self::$instance = new PDO(
                    $dsn,
                    $this->config['username'],
                    $this->config['password'],
                    $this->config['options'] ?? []
                );
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    private function getDsn(): string
    {
        $driver = $this->config['driver'];
        $host = $this->config['host'];
        $port = $this->config['port'];
        $database = $this->config['database'];
        $charset = $this->config['charset'];

        return "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";
    }

    
    public function disconnect(): void
    {
        self::$instance = null;
    }
}