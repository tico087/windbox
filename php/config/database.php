<?php

// return [
//     'driver'    => getenv('DB_DRIVER') ?: 'mysql', 
//     'host'      => getenv('DB_HOST') ?: 'localhost',
//     'port'      => getenv('DB_PORT') ?: '3306',
//     'database'  => getenv('DB_DATABASE') ?: 'windbox_db',
//     'username'  => getenv('DB_USERNAME') ?: 'root',
//     'password'  => getenv('DB_PASSWORD') ?: '', 
//     'charset'   => getenv('DB_CHARSET') ?: 'utf8mb4',
//     'collation' => getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci',
//     'prefix'    => getenv('DB_PREFIX') ?: '',
//     'options' => [
//         \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
//         \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
//         \PDO::ATTR_EMULATE_PREPARES   => false,
//     ],
// ];


return [
    'driver'    => $_ENV['DB_DRIVER'] ?? 'mysql', 
    'host'      => $_ENV['DB_HOST'] ?? 'localhost', 
    'port'      => $_ENV['DB_PORT'] ?? '3306', 
    'database'  => $_ENV['DB_DATABASE'] ?? 'windbox_db', 
    'username'  => $_ENV['DB_USERNAME'] ?? 'root', 
    'password'  => $_ENV['DB_PASSWORD'] ?? '', 
    'charset'   => $_ENV['DB_CHARSET'] ?? 'utf8mb4', 
    'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci', 
    'prefix'    => $_ENV['DB_PREFIX'] ?? '', 
    'options' => [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];