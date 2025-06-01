<?php

namespace WindBox\Infrastructure\DependenceInjection;

use Psr\Container\ContainerInterface; // Interface PSR-11
use ReflectionClass;
use Exception;

class Container implements ContainerInterface
{
    protected array $bindings = [];
    protected array $instances = [];

    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        if (is_null($concrete)) {
            $concrete = $abstract; // Assume concrete class name is same as abstract
        }
        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function make(string $abstract, array $parameters = []): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract]['concrete'] ?? $abstract;

        if ($concrete instanceof \Closure) {
            $object = $concrete($this, $parameters);
        } elseif (is_string($concrete)) {
            $reflector = new ReflectionClass($concrete);
            if (!$reflector->isInstantiable()) {
                throw new Exception("Class {$concrete} is not instantiable.");
            }

            $constructor = $reflector->getConstructor();
            if (is_null($constructor)) {
                $object = new $concrete();
            } else {
                $dependencies = $this->getDependencies($constructor->getParameters(), $parameters);
                $object = $reflector->newInstanceArgs($dependencies);
            }
        } else {
            throw new Exception("Invalid concrete type for binding {$abstract}.");
        }

        if (isset($this->bindings[$abstract]['shared']) && $this->bindings[$abstract]['shared']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    protected function getDependencies(array $parameters, array $primitives = []): array
    {
        $dependencies = [];
        foreach ($parameters as $parameter) {
            $dependency = $parameter->getType() && !$parameter->getType()->isBuiltin()
                          ? $parameter->getType()->getName()
                          : null;

            if (array_key_exists($parameter->getName(), $primitives)) {
                $dependencies[] = $primitives[$parameter->getName()];
            } elseif (is_string($dependency) && $this->has($dependency)) {
                $dependencies[] = $this->make($dependency);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new Exception("Cannot resolve dependency {$parameter->getName()} for class {$dependency}");
            }
        }
        return $dependencies;
    }

    public function get(string $id)
    {
        return $this->make($id);
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || class_exists($id) || interface_exists($id);
    }
}