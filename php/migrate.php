<?php



require_once __DIR__ . '/vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "Dotenv loaded successfully.\n";
} catch (\Dotenv\Exception\InvalidPathException $e) {
    die("Dotenv Error: Invalid Path - " . $e->getMessage() . "\n");
} catch (\Exception $e) {
    die("Dotenv Error: " . $e->getMessage() . "\n");
}


// dd(
//     'DB_DRIVER from getenv(): ' . (getenv('DB_DRIVER') ?: 'NOT SET'),
//     'DB_HOST from getenv(): ' . (getenv('DB_HOST') ?: 'NOT SET'),
//     'DB_DATABASE from getenv(): ' . (getenv('DB_DATABASE') ?: 'NOT SET'),
//     '$_ENV content: ', $_ENV // Verifica o array superglobal $_ENV
// );

use WindBox\Infrastructure\DependenceInjection\Container;
use WindBox\Infrastructure\Persistence\Migrations\MigrationRunner;



$container = new Container();


$pdo = $container->get(PDO::class);


$migrationsPath = __DIR__ . '/database/migrations';

$runner = new MigrationRunner($pdo, $migrationsPath);


$command = $argv[1] ?? 'migrate'; 
$steps = (int) ($argv[2] ?? 1); 

try {
    if ($command === 'migrate') {
        $runner->migrate();
    } elseif ($command === 'rollback') {
        $runner->rollback($steps);
    } else {
        echo "Usage: php migrate.php [migrate|rollback] [steps]\n";
        echo "Example: php migrate.php migrate\n";
        echo "Example: php migrate.php rollback\n";
        echo "Example: php migrate.php rollback 2\n";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);