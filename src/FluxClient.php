<?php

declare(strict_types=1);

namespace Lanos\PHPBFL;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Lanos\PHPBFL\Exceptions\FluxApiException;
use Lanos\PHPBFL\Exceptions\AuthenticationException;
use Lanos\PHPBFL\Services\ImageGenerationService;
use Lanos\PHPBFL\Services\FinetuneService;
use Lanos\PHPBFL\Services\UtilityService;
use Psr\Http\Message\ResponseInterface;

/**
 * Main client for interacting with Black Forest Labs' FLUX API
 * 
 * @package Lanos\PHPBFL
 * @author Lanos <https://github.com/l4nos>
 * @license MIT
 */
final class FluxClient
{
    private const BASE_URL = 'https://api.bfl.ai/v1';
    
    private Client $httpClient;
    private string $apiKey;
    
    private ?ImageGenerationService $imageGeneration = null;
    private ?FinetuneService $finetune = null;
    private ?UtilityService $utility = null;

    /**
     * Create a new FluxClient instance
     *
     * @param string $apiKey The API key for authentication
     * @param array<string, mixed> $options Additional Guzzle client options
     */
    public function __construct(string $apiKey, array $options = [])
    {
        if (empty($apiKey)) {
            throw new AuthenticationException('API key cannot be empty');
        }

        $this->apiKey = $apiKey;
        
        $defaultOptions = [
            'base_uri' => self::BASE_URL,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'x-key' => $this->apiKey,
            ],
        ];

        $this->httpClient = new Client(array_merge($defaultOptions, $options));
    }

    /**
     * Access image generation services
     */
    public function imageGeneration(): ImageGenerationService
    {
        if ($this->imageGeneration === null) {
            $this->imageGeneration = new ImageGenerationService($this);
        }
        
        return $this->imageGeneration;
    }

    /**
     * Access finetune services
     */
    public function finetune(): FinetuneService
    {
        if ($this->finetune === null) {
            $this->finetune = new FinetuneService($this);
        }
        
        return $this->finetune;
    }

    /**
     * Access utility services
     */
    public function utility(): UtilityService
    {
        if ($this->utility === null) {
            $this->utility = new UtilityService($this);
        }
        
        return $this->utility;
    }

    /**
     * Make a GET request to the API
     *
     * @param string $endpoint
     * @param array<string, mixed> $queryParams
     * @return array<string, mixed>
     * @throws FluxApiException
     */
    public function get(string $endpoint, array $queryParams = []): array
    {
        try {
            $response = $this->httpClient->get($endpoint, [
                'query' => $queryParams,
            ]);

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            // Check if this is a 401 HTTP error
            if ($e instanceof RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                if ($response !== null && $response->getStatusCode() === 401) {
                    $body = $response->getBody()->getContents();
                    $errorData = json_decode($body, true);
                    $errorMessage = is_array($errorData) && isset($errorData['message']) ? (string)$errorData['message'] : 'Authentication failed';
                    throw new AuthenticationException($errorMessage, 401);
                }
            }
            
            throw new FluxApiException(
                'GET request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Make a POST request to the API
     *
     * @param string $endpoint
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws FluxApiException
     */
    public function post(string $endpoint, array $data = []): array
    {
        try {
            $response = $this->httpClient->post($endpoint, [
                'json' => $data,
            ]);

            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            // Check if this is a 401 HTTP error
            if ($e instanceof RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                if ($response !== null && $response->getStatusCode() === 401) {
                    $body = $response->getBody()->getContents();
                    $errorData = json_decode($body, true);
                    $errorMessage = is_array($errorData) && isset($errorData['message']) ? (string)$errorData['message'] : 'Authentication failed';
                    throw new AuthenticationException($errorMessage, 401);
                }
            }
            
            throw new FluxApiException(
                'POST request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Handle API response and extract data
     *
     * @param ResponseInterface $response
     * @return array<string, mixed>
     * @throws FluxApiException
     */
    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        if ($statusCode >= 400) {
            $errorData = json_decode($body, true);
            $errorMessage = is_array($errorData) && isset($errorData['message']) ? (string)$errorData['message'] : 'Unknown API error';
            
            if ($statusCode === 401) {
                throw new AuthenticationException($errorMessage, $statusCode);
            }
            
            throw new FluxApiException($errorMessage, $statusCode);
        }

        // Handle empty response body
        if (empty($body)) {
            return [];
        }

        $decodedBody = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new FluxApiException('Invalid JSON response: ' . json_last_error_msg());
        }

        return is_array($decodedBody) ? $decodedBody : [];
    }

    /**
     * Get the API key
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Get the HTTP client instance
     */
    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }
}