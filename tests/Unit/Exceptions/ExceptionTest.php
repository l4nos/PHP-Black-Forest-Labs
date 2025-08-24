<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Unit\Exceptions;

use Lanos\PHPBFL\Exceptions\AuthenticationException;
use Lanos\PHPBFL\Exceptions\FluxApiException;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * @covers \Lanos\PHPBFL\Exceptions\FluxApiException
 * @covers \Lanos\PHPBFL\Exceptions\AuthenticationException
 */
class ExceptionTest extends TestCase
{
    public function test_flux_api_exception_can_be_created(): void
    {
        $exception = new FluxApiException('Test message', 400);

        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
    }

    public function test_authentication_exception_extends_flux_api_exception(): void
    {
        $exception = new AuthenticationException('Auth failed');

        $this->assertInstanceOf(FluxApiException::class, $exception);
        $this->assertSame('Auth failed', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
    }

    public function test_is_client_error_returns_true_for_4xx(): void
    {
        $exception = new FluxApiException('Client error', 400);

        $this->assertTrue($exception->isClientError());
        $this->assertFalse($exception->isServerError());
    }

    public function test_is_server_error_returns_true_for_5xx(): void
    {
        $exception = new FluxApiException('Server error', 500);

        $this->assertTrue($exception->isServerError());
        $this->assertFalse($exception->isClientError());
    }

    /**
     * @dataProvider friendlyMessageProvider
     */
    public function test_get_friendly_message_returns_appropriate_message(int $code, string $expected): void
    {
        $exception = new FluxApiException('Original message', $code);

        $this->assertSame($expected, $exception->getFriendlyMessage());
    }

    public static function friendlyMessageProvider(): array
    {
        return [
            [400, 'Bad request - please check your parameters'],
            [401, 'Authentication failed - please check your API key'],
            [403, 'Access forbidden - insufficient permissions'],
            [404, 'Resource not found'],
            [429, 'Rate limit exceeded - please try again later'],
            [500, 'Internal server error - please try again later'],
            [502, 'Bad gateway - service temporarily unavailable'],
            [503, 'Service unavailable - please try again later'],
            [999, 'An unknown error occurred'], // fallback for unknown codes
        ];
    }

    public function test_authentication_exception_uses_default_values(): void
    {
        $exception = new AuthenticationException();

        $this->assertSame('Authentication failed', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
    }
}
