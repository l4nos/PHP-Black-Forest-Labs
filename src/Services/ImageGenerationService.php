<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Services;

use Lanos\PHPBFL\FluxClient;
use Lanos\PHPBFL\DTOs\Requests\Flux1ProRequest;
use Lanos\PHPBFL\DTOs\Responses\ImageGenerationResponse;
use Lanos\PHPBFL\Exceptions\FluxApiException;

/**
 * Service for handling image generation requests
 *
 * @package Lanos\PHPBFL\Services
 * @author Lanos <https://github.com/l4nos>
 */
class ImageGenerationService
{
    public function __construct(
        private FluxClient $client
    ) {}

    /**
     * Generate an image with FLUX1 Pro model
     *
     * @param Flux1ProRequest $request
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function flux1Pro(Flux1ProRequest $request): ImageGenerationResponse
    {
        $errors = $request->validate();
        if (!empty($errors)) {
            throw new FluxApiException('Invalid request parameters: ' . implode(', ', $errors));
        }

        $response = $this->client->post('/flux-pro', $request->toArray());
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Generate an image with FLUX1 Dev model
     *
     * @param array<string, mixed> $params Request parameters
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function flux1Dev(array $params): ImageGenerationResponse
    {
        $response = $this->client->post('/flux-dev', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Generate an image with FLUX 1.1 Pro model
     *
     * @param array<string, mixed> $params Request parameters
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function flux11Pro(array $params): ImageGenerationResponse
    {
        $response = $this->client->post('/flux-pro-1.1', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Generate an image with FLUX 1.1 Pro Ultra mode
     *
     * @param array<string, mixed> $params Request parameters
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function flux11ProUltra(array $params): ImageGenerationResponse
    {
        $response = $this->client->post('/flux-pro-1.1-ultra', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Edit or create an image with Flux Kontext Pro
     *
     * @param array<string, mixed> $params Request parameters
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function fluxKontextPro(array $params): ImageGenerationResponse
    {
        $response = $this->client->post('/flux-kontext-pro', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Edit or create an image with Flux Kontext Max
     *
     * @param array<string, mixed> $params Request parameters
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function fluxKontextMax(array $params): ImageGenerationResponse
    {
        $response = $this->client->post('/flux-kontext-max', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Fill inpainting with FLUX1 Fill Pro
     *
     * @param array<string, mixed> $params Request parameters including image and optional mask
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function flux1Fill(array $params): ImageGenerationResponse
    {
        if (!isset($params['image'])) {
            throw new FluxApiException('Image parameter is required for fill operations');
        }

        $response = $this->client->post('/flux-pro-1.0-fill', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Expand an image by adding pixels on any side
     *
     * @param array<string, mixed> $params Request parameters including image and expansion directions
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function flux1Expand(array $params): ImageGenerationResponse
    {
        if (!isset($params['image'])) {
            throw new FluxApiException('Image parameter is required for expand operations');
        }

        $response = $this->client->post('/flux-pro-1.0-expand', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Generate an image with FLUX1 Canny Pro using a control image
     *
     * @param array<string, mixed> $params Request parameters including prompt and control_image
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function flux1Canny(array $params): ImageGenerationResponse
    {
        if (!isset($params['prompt'])) {
            throw new FluxApiException('Prompt parameter is required for Canny operations');
        }

        $response = $this->client->post('/flux-pro-1.0-canny', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Generate an image with FLUX1 Depth Pro using a control image
     *
     * @param array<string, mixed> $params Request parameters including prompt and control_image
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function flux1Depth(array $params): ImageGenerationResponse
    {
        if (!isset($params['prompt'])) {
            throw new FluxApiException('Prompt parameter is required for Depth operations');
        }

        $response = $this->client->post('/flux-pro-1.0-depth', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }
}