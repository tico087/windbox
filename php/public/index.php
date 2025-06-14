<?php

require_once __DIR__ . '/../vendor/autoload.php';

use WindBox\Infrastructure\DependenceInjection\Container;
use WindBox\Infrastructure\Http\Request;
use WindBox\Infrastructure\Http\Response;
use WindBox\Infrastructure\Http\Router;
use WindBox\Infrastructure\Http\Controllers\WindStockController;
use WindBox\Infrastructure\Persistence\JsonFile\JsonWindPacketRepository;
use WindBox\Domain\Ports\WindPacketRepository;
use WindBox\Domain\Ports\WindStorageService;
use WindBox\Domain\Services\WindStorageManager;
use WindBox\Application\Services\StoreWindService;
use WindBox\Application\Services\AllocateWindService;
use WindBox\Application\Services\GetAvailableWindService;


$container = new Container();


$container->singleton(WindPacketRepository::class, JsonWindPacketRepository::class);
$container->bind(WindStorageService::class, WindStorageManager::class);


$container->bind(StoreWindService::class);
$container->bind(AllocateWindService::class);
$container->bind(GetAvailableWindService::class);


$request = new Request();
$response = new Response();

// 3. Setup Router
// O basePath deve corresponder ao que você configurou no Nginx para este projeto
// Por exemplo, se no Nginx você usa `location /windbox-php/`, o basePath deve ser '/windbox-php/'
$router = new Router($container, '/php/');

// 4. Definir Rotas
$router->addRoute('POST', 'api/windbox/store', [WindStockController::class, 'storeWind']);
$router->addRoute('POST', 'api/windbox/allocate', [WindStockController::class, 'allocateWind']);
$router->addRoute('GET', 'api/windbox/available/{location}', [WindStockController::class, 'getAvailableWind']); // Com parâmetro de rota

// 5. Despachar a requisição
echo $router->dispatch($request, $response);