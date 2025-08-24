<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Exceptions;

use Exception;
use Throwable;

/**
 * Base exception for FLUX API errors
 *
 * @package Lanos\PHPBFL\Exceptions
 * @author Lanos <https://github.com/l4nos>
 */
class FluxApiException extends Exception
{
    /**
     * Create a new FluxApiException instance
     *
     * @param string $message The exception message
     * @param int $code The exception code (HTTP status code)
     * @param Throwable|null $previous Previous exception for chaining
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Check if the exception is due to a client error (4xx status codes)
     */
    public function isClientError(): bool
    {
        return $this->code >= 400 && $this->code < 500;
    }

    /**
     * Check if the exception is due to a server error (5xx status codes)
     */
    public function isServerError(): bool
    {
        return $this->code >= 500;
    }

    /**
     * Get a user-friendly error message based on the HTTP status code
     */
    public function getFriendlyMessage(): string
    {
        return match ($this->code) {
            400 => 'Bad request - please check your parameters',
            401 => 'Authentication failed - please check your API key',
            403 => 'Access forbidden - insufficient permissions',
            404 => 'Resource not found',
            429 => 'Rate limit exceeded - please try again later',
            500 => 'Internal server error - please try again later',
            502 => 'Bad gateway - service temporarily unavailable',
            503 => 'Service unavailable - please try again later',
            default => 'An unknown error occurred'
        };
    }
}