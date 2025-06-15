<?php

namespace WindBox\Infrastructure\DependenceInjection;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use Exception;

// Importações de serviços e entidades do seu domínio/aplicação
use WindBox\Application\Services\AllocateWindService;
use WindBox\Application\Services\GetAvailableWindService;
use WindBox\Application\Services\StoreWindService;
use WindBox\Domain\Services\WindStorageManager;

// Importações de portas (interfaces)
use WindBox\Domain\Ports\WindPacketRepository;
// IMPORTAÇÃO CORRIGIDA/ADICIONADA: A interface WindStorageService precisa ser importada para o binding
use WindBox\Domain\Ports\WindStorageService;

// Importações de adaptadores de infraestrutura
use WindBox\Infrastructure\Persistence\Database\PdoWindPacketRepository;
use WindBox\Infrastructure\Persistence\DatabaseConnection;

// IMPORTAÇÃO CORRIGIDA: Ajuste o namespace do seu controlador conforme a estrutura do seu projeto
// Pelo seu tree, o namespace correto para WindStockController deve ser WindBox\Infrastructure\Http\Controllers
use WindBox\Infrastructure\Http\Controllers\WindStockController;

use PDO; // Importação da classe PDO

class Container implements ContainerInterface
{
    protected array $bindings = [];
    protected array $instances = [];

    public function __construct()
    {
        $this->registerServiceDefinitions();
    }

    protected function registerServiceDefinitions(): void
    {

        $databaseConfig = require __DIR__ . '/../../../config/database.php';


        $this->singleton(DatabaseConnection::class, function () use ($databaseConfig) {
            return new DatabaseConnection($databaseConfig);
        });


        $this->singleton(PDO::class, function (ContainerInterface $c) {
            return $c->get(DatabaseConnection::class)->connect();
        });

        $this->singleton(WindPacketRepository::class, PdoWindPacketRepository::class);


        $this->singleton(PdoWindPacketRepository::class, function (ContainerInterface $c) {
            return new PdoWindPacketRepository($c->get(PDO::class));
        });


        $this->singleton(WindStorageService::class, WindStorageManager::class);


        $this->singleton(WindStorageManager::class, function (ContainerInterface $c) {
            return new WindStorageManager(

                $c->get(WindPacketRepository::class)
            );
        });


        $this->singleton(AllocateWindService::class);
        $this->singleton(StoreWindService::class);
        $this->singleton(GetAvailableWindService::class);


        $this->singleton(WindStockController::class);
    }

    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
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
            try {
                $reflector = new ReflectionClass($concrete);
            } catch (ReflectionException $e) {
                throw new Exception("Class {$concrete} does not exist: " . $e->getMessage(), 0, $e);
            }

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
            $type = $parameter->getType();

            if (array_key_exists($parameter->getName(), $primitives)) {
                $dependencies[] = $primitives[$parameter->getName()];
            } elseif ($type instanceof \ReflectionNamedType && !$type->isBuiltin() && $this->has($type->getName())) {
                $dependencies[] = $this->make($type->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } elseif ($type instanceof \ReflectionNamedType && $type->allowsNull() && !$parameter->isDefaultValueAvailable()) {
                $dependencies[] = null;
            } else {
                $declaringClass = $parameter->getDeclaringClass() ? $parameter->getDeclaringClass()->getName() : 'unknown class';
                $dependencyName = $type instanceof \ReflectionNamedType ? $type->getName() : $parameter->getName();
                throw new Exception("Cannot resolve dependency [{$parameter->getName()}] (Type: {$dependencyName}) for class [{$declaringClass}].");
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