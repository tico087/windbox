<?php

namespace WindBox\Infrastructure\Persistence\Migrations;

use PDO;
use DirectoryIterator;
use Exception;

class MigrationRunner
{
    private PDO $pdo;
    private string $migrationsPath;

    public function __construct(PDO $pdo, string $migrationsPath)
    {
        $this->pdo = $pdo;
        $this->migrationsPath = $migrationsPath;
    }

    public function migrate(): void
    {
        echo "Running migrations...\n";


        $this->ensureMigrationsTableExists();

        $executedMigrations = $this->getExecutedMigrations();
        $migrationFiles = $this->getMigrationFiles();

    
        $currentBatch = $this->getCurrentBatch() + 1;

        foreach ($migrationFiles as $fileName => $dummyClassName) {
          
            if (!in_array($fileName, $executedMigrations)) {

                $filePath = $this->migrationsPath . '/' . $fileName;

               
                try {
                    $className = $this->getClassNameFromFile($filePath);
                } catch (Exception $e) {
                    echo "Skipping migration {$fileName}: " . $e->getMessage() . "\n";
                    continue; // Pula esta migração se a classe não for encontrada
                }


                echo "Migrating: {$fileName}\n";
             
                require_once $this->migrationsPath . '/' . $fileName;
                $migration = new $className();
                $migration->up($this->pdo);
                $this->recordMigration($fileName, $currentBatch);
            }
        }

        echo "Migrations finished.\n";
    }

    public function rollback(int $steps = 1): void
    {
        echo "Rolling back migrations...\n";

        $currentBatch = $this->getCurrentBatch();
        if ($currentBatch === 0) {
            echo "No migrations to rollback.\n";
            return;
        }

        for ($i = 0; $i < $steps; $i++) {
            $migrationsToRollback = $this->getMigrationsByBatch($currentBatch - $i);

            if (empty($migrationsToRollback)) {
                echo "No migrations found for batch " . ($currentBatch - $i) . ".\n";
                continue;
            }

            foreach (array_reverse($migrationsToRollback) as $migrationName) {
                echo "Rolling back: {$migrationName}\n";
                require_once $this->migrationsPath . '/' . $migrationName;
                $className = pathinfo($migrationName, PATHINFO_FILENAME); 
                $migration = new $className();
                $migration->down($this->pdo);
                $this->deleteMigrationRecord($migrationName);
            }
        }

        echo "Rollback finished.\n";
    }

    private function ensureMigrationsTableExists(): void
    {
        try {
            $this->pdo->query("SELECT 1 FROM migrations LIMIT 1");
        } catch (\PDOException $e) {
            
            $this->pdo->exec("
                CREATE TABLE migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL,
                    batch INT NOT NULL,
                    migrated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
            ");
        }
    }

    private function getExecutedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getMigrationFiles(): array
    {
        $files = [];
        $iterator = new DirectoryIterator($this->migrationsPath);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile() && $fileinfo->getExtension() === 'php') {
                $fileName = $fileinfo->getFilename();
               
                $className = pathinfo($fileName, PATHINFO_FILENAME);
                $files[$fileName] = $className;
            }
        }
        ksort($files);
        return $files;
    }

    private function recordMigration(string $migration, int $batch): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)");
        $stmt->execute([':migration' => $migration, ':batch' => $batch]);
    }

    private function deleteMigrationRecord(string $migration): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = :migration");
        $stmt->execute([':migration' => $migration]);
    }

    private function getCurrentBatch(): int
    {
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM migrations");
        return (int) ($stmt->fetchColumn() ?? 0);
    }

    private function getMigrationsByBatch(int $batch): array
    {
        $stmt = $this->pdo->prepare("SELECT migration FROM migrations WHERE batch = :batch ORDER BY id DESC");
        $stmt->execute([':batch' => $batch]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }


    private function getClassNameFromFile(string $filePath): string
    {
        $content = file_get_contents($filePath);

        // Regex para encontrar "class NomeDaClasse"
        if (preg_match('/^\s*class\s+([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)/mi', $content, $matches)) {
            return $matches[1];
        }

        throw new Exception("No class found in file: {$filePath}");
    }
}