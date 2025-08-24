<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Unit;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Lanos\PHPBFL\Exceptions\AuthenticationException;
use Lanos\PHPBFL\Exceptions\FluxApiException;
use Lanos\PHPBFL\FluxClient;
use Lanos\PHPBFL\Services\FinetuneService;
use Lanos\PHPBFL\Services\ImageGenerationService;
use Lanos\PHPBFL\Services\UtilityService;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * @covers \Lanos\PHPBFL\FluxClient
 */
class FluxClientTest extends TestCase
{
    public function test_can_be_instantiated_with_api_key(): void
    {
        $client = new FluxClient('test-api-key');

        $this->assertInstanceOf(FluxClient::class, $client);
        $this->assertSame('test-api-key', $client->getApiKey());
    }

    public function test_throws_exception_for_empty_api_key(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('API key cannot be empty');

        new FluxClient('');
    }

    public function test_returns_image_generation_service(): void
    {
        $client = new FluxClient('test-api-key');

        $service = $client->imageGeneration();

        $this->assertInstanceOf(ImageGenerationService::class, $service);

        // Test that it returns the same instance (singleton pattern)
        $this->assertSame($service, $client->imageGeneration());
    }

    public function test_returns_finetune_service(): void
    {
        $client = new FluxClient('test-api-key');

        $service = $client->finetune();

        $this->assertInstanceOf(FinetuneService::class, $service);
        $this->assertSame($service, $client->finetune());
    }

    public function test_returns_utility_service(): void
    {
        $client = new FluxClient('test-api-key');

        $service = $client->utility();

        $this->assertInstanceOf(UtilityService::class, $service);
        $this->assertSame($service, $client->utility());
    }

    public function test_get_request_success(): void
    {
        $expectedData = ['result' => 'success'];
        $client = $this->createMockClient([
            $this->createJsonResponse($expectedData),
        ]);

        $result = $client->get('/test', ['param' => 'value']);

        $this->assertSame($expectedData, $result);
    }

    public function test_post_request_success(): void
    {
        $expectedData = ['id' => 'task_123'];
        $client = $this->createMockClient([
            $this->createJsonResponse($expectedData),
        ]);

        $result = $client->post('/test', ['data' => 'value']);

        $this->assertSame($expectedData, $result);
    }

    public function test_handles_400_error(): void
    {
        $client = $this->createMockClient([
            $this->createErrorResponse('Bad request', 400),
        ]);

        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Bad request');
        $this->expectExceptionCode(400);

        $client->get('/test');
    }

    public function test_handles_401_error_as_authentication_exception(): void
    {
        $client = $this->createMockClient([
            $this->createErrorResponse('Invalid API key', 401),
        ]);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid API key');
        $this->expectExceptionCode(401);

        $client->get('/test');
    }

    public function test_handles_network_errors(): void
    {
        $client = $this->createMockClient([
            new RequestException('Network error', new Request('GET', '/test')),
        ]);

        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('GET request failed: Network error');

        $client->get('/test');
    }

    public function test_handles_invalid_json_response(): void
    {
        $client = $this->createMockClient([
            new \GuzzleHttp\Psr7\Response(200, [], 'invalid json'),
        ]);

        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $client->get('/test');
    }

    public function test_accepts_custom_options(): void
    {
        $options = [
            'timeout' => 60,
            'headers' => ['Custom-Header' => 'value'],
        ];

        $client = new FluxClient('test-api-key', $options);

        $this->assertInstanceOf(FluxClient::class, $client);
    }

    public function test_get_http_client_returns_guzzle_client(): void
    {
        $client = new FluxClient('test-api-key');
        $httpClient = $client->getHttpClient();

        $this->assertInstanceOf(\GuzzleHttp\Client::class, $httpClient);
    }

    public function test_get_http_client_returns_same_instance(): void
    {
        $client = new FluxClient('test-api-key');
        $httpClient1 = $client->getHttpClient();
        $httpClient2 = $client->getHttpClient();

        $this->assertSame($httpClient1, $httpClient2);
    }

    public function test_get_http_client_has_correct_configuration(): void
    {
        $client = new FluxClient('test-api-key');
        $httpClient = $client->getHttpClient();

        $config = $httpClient->getConfig();
        
        $this->assertSame('https://api.bfl.ai/v1', $config['base_uri']->__toString());
        $this->assertSame(30, $config['timeout']);
        $this->assertSame('application/json', $config['headers']['Content-Type']);
        $this->assertSame('application/json', $config['headers']['Accept']);
        $this->assertSame('test-api-key', $config['headers']['x-key']);
    }

    public function test_handles_empty_response_body(): void
    {
        $client = $this->createMockClient([
            new \GuzzleHttp\Psr7\Response(200, [], ''),
        ]);

        $result = $client->get('/test');

        $this->assertSame([], $result);
    }

    public function test_handles_null_json_response(): void
    {
        $client = $this->createMockClient([
            new \GuzzleHttp\Psr7\Response(200, [], 'null'),
        ]);

        $result = $client->get('/test');

        $this->assertSame([], $result);
    }

    /**
     * @dataProvider httpErrorStatusProvider
     */
    public function test_handles_various_http_errors(int $statusCode, string $expectedExceptionClass): void
    {
        $client = $this->createMockClient([
            $this->createErrorResponse('Error message', $statusCode),
        ]);

        $this->expectException($expectedExceptionClass);
        $this->expectExceptionCode($statusCode);

        $client->get('/test');
    }

    public static function httpErrorStatusProvider(): array
    {
        return [
            [400, FluxApiException::class],
            [401, AuthenticationException::class],
            [403, FluxApiException::class],
            [404, FluxApiException::class],
            [429, FluxApiException::class],
            [500, FluxApiException::class],
            [502, FluxApiException::class],
            [503, FluxApiException::class],
        ];
    }
}
