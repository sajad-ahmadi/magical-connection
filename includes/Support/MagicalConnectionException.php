<?php
declare(strict_types=1);

namespace MagicalConnection\Support;

use RuntimeException;
use Throwable;

/**
 * Represent an application-level exception in Magical Connection.
 *
 * Extends the standard runtime exception with an application-specific
 * error key that can be used to identify and handle errors consistently
 * across the plugin.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class MagicalConnectionException extends RuntimeException
{

    /**
     * Application-specific key identifying the error.
     *
     * @since 1.0.0
     */
    private string $errorKey;

    /**
     * Create a new application exception.
     *
     * Stores the human-readable error message together with an
     * application-specific error key and an optional previous exception.
     *
     * @since 1.0.0
     *
     * @param string $message           Human-readable error message.
     * @param string $errorKey          Application-specific error identifier.
     * @param Throwable|null $previous Previous exception that caused this error.
     */
    public function __construct(string $message, string $errorKey, ?Throwable $previous = null)
    {
        $this->errorKey = $errorKey;
        parent::__construct($message, 0, $previous);
    }

    /**
     * Get the application-specific error key.
     *
     * @since 1.0.0
     *
     * @return string Error identifier.
     */
    public function getErrorKey(): string
    {
        return $this->errorKey;
    }
}