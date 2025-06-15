<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


if ($_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}


use WindBox\Infrastructure\DependenceInjection\Container;
use WindBox\Infrastructure\Http\Request;
use WindBox\Infrastructure\Http\Response;
use WindBox\Infrastructure\Http\Router;
use WindBox\Infrastructure\Http\ApiRouter;


$container = new Container();

$request = new Request();
$response = new Response();


$basePath = '/php/';
$router = new Router($container, $basePath);

$apiRouter = new ApiRouter($router, $container);
$apiRouter->registerRoutes();

// Despacha a requisição
echo $apiRouter->dispatch($request, $response);