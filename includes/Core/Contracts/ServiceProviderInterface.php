<?php
declare(strict_types=1);

namespace MagicalConnection\Core\Contracts;

/**
 * Define the contract for registering a service provider.
 *
 * Service providers use this contract to register their services,
 * bindings, hooks, and other application-level dependencies.
 *
 * @since   1.0.0
 * @package CodeArt
 */
interface ServiceProviderInterface
{

    /**
     * Register the services provided by the implementation.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register(): void;
}