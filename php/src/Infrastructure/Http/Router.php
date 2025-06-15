<?php

namespace WindBox\Infrastructure\Http;

use WindBox\Infrastructure\DependenceInjection\Container;
use Exception;
use WindBox\Domain\Exceptions\InsufficientWindException;
use WindBox\Domain\Exceptions\WindPacketNotFoundException;

class Router
{
    private array $routes = [];
    private Container $container;
    private string $basePath;

    public function __construct(Container $container, string $basePath = '/')
    {
        $this->container = $container;
        $this->basePath = rtrim($basePath, '/') . '/'; 
    }

    public function addRoute(string $method, string $uriPattern, callable|array $action): void
    {
       
        $fullUriPattern = $this->basePath . ltrim($uriPattern, '/');
      
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $fullUriPattern);
        $this->routes[$method]['/^' . str_replace('/', '\/', $regex) . '$/'] = [
            'action' => $action,
            'original_uri' => $uriPattern 
        ];
    }

    public function dispatch(Request $request, Response $response): string
    {
        $method = $request->method;
        $uri = $request->uri;

        if (!isset($this->routes[$method])) {
            return $response->json(['error' => 'Method Not Allowed'], 405);
        }

        foreach ($this->routes[$method] as $routeRegex => $routeData) {
            if (preg_match($routeRegex, $uri, $matches)) {
                $action = $routeData['action'];
                $pathParams = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $pathParams[$key] = $value;
                    }
                }
                $request->setPathParams($pathParams);

                return $this->executeAction($action, $request, $response);
            }
        }

        return $response->json(['error' => 'Not Found'], 404);
    }

    private function executeAction(callable|array $action, Request $request, Response $response): string
    {
        try {
            if (is_callable($action)) {
                return call_user_func($action, $request, $response);
            } elseif (is_array($action) && count($action) === 2) {
                [$controllerClass, $methodName] = $action;

             
                $controller = $this->container->make($controllerClass);

               
                $reflectionMethod = new \ReflectionMethod($controller, $methodName);
                $args = [];
                foreach ($reflectionMethod->getParameters() as $param) {
                    $paramType = $param->getType();
                    if ($paramType && !$paramType->isBuiltin()) {
                        $paramClassName = $paramType->getName();
                        if ($paramClassName === Request::class) {
                            $args[] = $request;
                        } elseif ($paramClassName === Response::class) {
                            $args[] = $response;
                        } elseif ($this->container->has($paramClassName)) {
                            $args[] = $this->container->make($paramClassName);
                        } else {
                            throw new Exception("Cannot resolve argument {$param->getName()} of type {$paramClassName} for {$controllerClass}::{$methodName}");
                        }
                    } else {
                    
                      
                        $paramName = $param->getName();
                        if (isset($request->pathParams[$paramName])) {
                            $args[] = $request->pathParams[$paramName];
                        } elseif ($request->input($paramName) !== null) {
                            $args[] = $request->input($paramName);
                        } elseif ($param->isDefaultValueAvailable()) {
                            $args[] = $param->getDefaultValue();
                        } else {
                            throw new Exception("Missing required argument {$paramName} for {$controllerClass}::{$methodName}");
                        }
                    }
                }

                return call_user_func_array([$controller, $methodName], $args);

            }
        } catch (\InvalidArgumentException $e) {
            return $response->json(['error' => $e->getMessage()], 400);
        } catch (InsufficientWindException | WindPacketNotFoundException $e) { 
            return $response->json(['error' => $e->getMessage()], $e->getCode());
        } catch (Exception $e) {
            error_log($e->getMessage() . " on " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());

            if ($_ENV['APP_DEBUG'] === 'true') {
                return $response->json([
                    'error' => 'Internal Server Error',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString())
                ], 500);
            } else {

                return $response->json(['error' => 'Internal Server Error'], 500);
            }
        }

        return $response->json(['error' => 'Route action not supported.'], 500);
    }
}