<?php

declare(strict_types=1);

namespace App\Core;

use Exception;
use ReflectionClass;
use ReflectionNamedType;

class Container
{
    /** @var array<string, object> */
    private array $instances = [];

    /** @var array<string, callable|string> */
    private array $bindings = [];

    public function bind(string $key, callable|string $resolver): void
    {
        $this->bindings[$key] = $resolver;
    }

    public function singleton(string $key, object $instance): void
    {
        $this->instances[$key] = $instance;
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            $resolver = $this->bindings[$id];
            if (is_callable($resolver)) {
                $instance = $resolver($this);
                $this->instances[$id] = $instance;

                return $instance;
            }

            return $this->resolve($resolver);
        }

        return $this->resolve($id);
    }

    private function resolve(string $className): mixed
    {
        if (!class_exists($className)) {
            throw new Exception("Class {$className} does not exist.");
        }

        $reflector = new ReflectionClass($className);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$className} cannot be instantiated.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $className();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new Exception(
                    "Cannot resolve parameter {$parameter->getName()} in constructor of {$className}."
                );
            }

            $dependencies[] = $this->get($type->getName());
        }

        $instance = $reflector->newInstanceArgs($dependencies);
        $this->instances[$className] = $instance;

        return $instance;
    }
}
