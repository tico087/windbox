<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use WindBox\Infrastructure\DependenceInjection\Container;
use WindBox\Infrastructure\Http\Request;
use WindBox\Infrastructure\Http\Response;
use WindBox\Infrastructure\Http\Router;
use WindBox\Infrastructure\Http\Controllers\WindStockController;

$container = new Container();


$request = new Request();
$response = new Response();

$router = new Router($container, '/php/');

$router->addRoute('POST', 'api/windbox/store', [WindStockController::class, 'storeWind']);
$router->addRoute('POST', 'api/windbox/allocate', [WindStockController::class, 'allocateWind']);
$router->addRoute('GET', 'api/windbox/available/{location}', [WindStockController::class, 'getAvailableWind']);

echo $router->dispatch($request, $response);