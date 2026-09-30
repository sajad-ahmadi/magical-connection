<?php

declare(strict_types=1);

namespace MagicalConnection\Core;

use MagicalConnection\Support\MagicalConnectionException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Resolve and manage application service dependencies.
 *
 * Provides service bindings, singleton services, and automatic
 * constructor dependency resolution for Magical Connection.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class Container
{
    /**
     * Registered service bindings.
     *
     * @since 1.0.0
     *
     * @var array
     */
    protected array $bindings = [];

    /**
     * Resolved singleton service instances.
     *
     * @since 1.0.0
     *
     * @var array
     */
    protected array $instances = [];

    /**
     * Register a transient service binding.
     *
     * A new instance is resolved each time the service is requested.
     *
     * @since 1.0.0
     *
     * @param string $abstract   Service identifier.
     * @param callable $concrete Factory used to resolve the service.
     *
     * @return void
     */
    public function bind(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = [
            'concrete'  => $concrete,
            'singleton' => false,
        ];
    }

    /**
     * Register a singleton service binding.
     *
     * The resolved instance is stored and returned for subsequent requests.
     *
     * @since 1.0.0
     *
     * @param string $abstract   Service identifier.
     * @param callable $concrete Factory used to resolve the service.
     *
     * @return void
     */
    public function singleton(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = [
            'concrete'  => $concrete,
            'singleton' => true,
        ];
    }

    /**
     * Resolve a service from the container.
     *
     * Registered bindings take precedence over automatic constructor
     * dependency resolution.
     *
     * @since 1.0.0
     *
     * @param string $abstract Service identifier.
     *
     * @return object Resolved service instance.
     */
    public function make(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $binding = $this->bindings[$abstract];

            $object = ($binding['concrete'])($this);
        } else {
            $object = $this->build($abstract);
        }

        if (isset($this->bindings[$abstract]) && $this->bindings[$abstract]['singleton']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Build a service using its constructor dependencies.
     *
     * @since 1.0.0
     *
     * @param string $abstract Service class name.
     *
     * @return object Resolved service instance.
     */
    protected function build(string $abstract): object
    {
        if (!class_exists($abstract)) {
            throw new MagicalConnectionException(
                "Service [{$abstract}] cannot be resolved.",
                'container.service_not_found'
            );
        }

        $reflection = new ReflectionClass($abstract);

        if (!$reflection->isInstantiable()) {
            throw new MagicalConnectionException(
                "Service [{$abstract}] cannot be instantiated.",
                'container.service_not_instantiable'
            );
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $dependencies[] = $this->resolve($parameter);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Resolve a constructor dependency.
     *
     * Only class and interface dependencies are resolved automatically.
     * Built-in types must be provided through an explicit binding or
     * constructor default value.
     *
     * @since 1.0.0
     *
     * @param ReflectionParameter $parameter Constructor parameter.
     *
     * @return object Resolved dependency instance.
     */
    protected function resolve(ReflectionParameter $parameter): object
    {
        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            if ($parameter->isDefaultValueAvailable()) {
                $value = $parameter->getDefaultValue();

                if (is_object($value)) {
                    return $value;
                }
            }

            throw new MagicalConnectionException(
                "Unable to resolve dependency [{$parameter->getName()}].",
                'container.dependency_unresolvable'
            );
        }

        return $this->make($type->getName());
    }
}