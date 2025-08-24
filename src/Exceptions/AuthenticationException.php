<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Exceptions;

/**
 * Exception thrown when authentication fails
 *
 * @package Lanos\PHPBFL\Exceptions
 * @author Lanos <https://github.com/l4nos>
 */
class AuthenticationException extends FluxApiException
{
    /**
     * Create a new AuthenticationException instance
     *
     * @param string $message The exception message
     * @param int $code The exception code (HTTP status code, default 401)
     * @param \Throwable|null $previous Previous exception for chaining
     */
    public function __construct(string $message = 'Authentication failed', int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}