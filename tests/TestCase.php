<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Lanos\PHPBFL\FluxClient;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case with common utilities
 *
 * @package Lanos\PHPBFL\Tests
 */
abstract class TestCase extends BaseTestCase
{
    protected function createMockClient(array $responses = []): FluxClient
    {
        $mockHandler = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mockHandler);
        $mockHttpClient = new Client(['handler' => $handlerStack]);

        $client = new FluxClient('test-api-key');
        
        // Use reflection to inject the mock HTTP client
        $reflection = new \ReflectionClass($client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockHttpClient);

        return $client;
    }

    protected function createJsonResponse(array $data, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($data));
    }

    protected function createErrorResponse(string $message, int $status = 400): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode([
            'error' => $message,
            'message' => $message
        ]));
    }

    /**
     * Assert that an array has the expected structure
     *
     * @param array<string> $expectedKeys
     * @param array<string, mixed> $actual
     */
    protected function assertArrayStructure(array $expectedKeys, array $actual): void
    {
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $actual, "Missing key: {$key}");
        }
    }

    /**
     * Generate a sample task response
     *
     * @return array<string, mixed>
     */
    protected function getSampleTaskResponse(): array
    {
        return [
            'id' => 'task_' . uniqid(),
            'polling_url' => 'https://api.bfl.ai/v1/get_result?id=task_' . uniqid()
        ];
    }

    /**
     * Generate a sample result response
     *
     * @return array<string, mixed>
     */
    protected function getSampleResultResponse(string $status = 'Ready', mixed $result = null): array
    {
        return [
            'id' => 'task_' . uniqid(),
            'status' => $status,
            'result' => $result ?? 'https://example.com/generated-image.jpg',
            'progress' => $status === 'Ready' ? 100.0 : 50.0,
            'details' => ['model' => 'flux-pro'],
            'preview' => null
        ];
    }
}