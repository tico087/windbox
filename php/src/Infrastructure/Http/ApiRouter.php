<?php

namespace WindBox\Infrastructure\Http;

use WindBox\Infrastructure\DependenceInjection\Container;
use WindBox\Infrastructure\Http\Controllers\WindStockController;

class ApiRouter
{
    private Router $router;
    private Container $container;

    public function __construct(Router $router, Container $container)
    {
        $this->router = $router;
        $this->container = $container;
    }

    public function registerRoutes(): void
    {
       
        $this->router->addRoute('POST', 'api/windbox/store', [WindStockController::class, 'storeWind']);
        $this->router->addRoute('POST', 'api/windbox/allocate', [WindStockController::class, 'allocateWind']);
        $this->router->addRoute('GET', 'api/windbox/available/{location}', [WindStockController::class, 'getAvailableWind']);
    }

    public function dispatch(Request $request, Response $response): string
    {
        return $this->router->dispatch($request, $response);
    }
}