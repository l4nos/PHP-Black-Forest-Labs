<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Services;

use Lanos\PHPBFL\FluxClient;
use Lanos\PHPBFL\DTOs\Responses\ImageGenerationResponse;
use Lanos\PHPBFL\Exceptions\FluxApiException;

/**
 * Service for handling fine-tuning operations
 *
 * @package Lanos\PHPBFL\Services
 * @author Lanos <https://github.com/l4nos>
 */
class FinetuneService
{
    public function __construct(
        private FluxClient $client
    ) {}

    /**
     * Get details about a specific fine-tune
     *
     * @param string $finetuneId Identifier of the fine-tune
     * @return array<string, mixed>
     * @throws FluxApiException
     */
    public function getDetails(string $finetuneId): array
    {
        if (empty($finetuneId)) {
            throw new FluxApiException('Finetune ID cannot be empty');
        }

        $response = $this->client->get('/finetune_details', [
            'finetune_id' => $finetuneId,
        ]);

        return $response;
    }

    /**
     * List all fine-tunes belonging to the authenticated user
     *
     * @return array<string, mixed>
     * @throws FluxApiException
     */
    public function listMyFinetunes(): array
    {
        $response = $this->client->get('/my_finetunes');

        return $response;
    }

    /**
     * Delete a previously created fine-tune
     *
     * @param string $finetuneId ID of the fine-tune to delete
     * @return array<string, mixed>
     * @throws FluxApiException
     */
    public function delete(string $finetuneId): array
    {
        if (empty($finetuneId)) {
            throw new FluxApiException('Finetune ID cannot be empty');
        }

        $response = $this->client->post('/delete_finetune', [
            'finetune_id' => $finetuneId,
        ]);

        return $response;
    }

    /**
     * Create a new fine-tuned model from a set of training images
     *
     * @param array<string, mixed> $params Fine-tune parameters
     * @return array<string, mixed>
     * @throws FluxApiException
     */
    public function create(array $params): array
    {
        $requiredFields = ['file_data', 'finetune_comment', 'mode', 'trigger_word', 'iterations'];
        
        foreach ($requiredFields as $field) {
            if (!isset($params[$field])) {
                throw new FluxApiException("Required field '{$field}' is missing");
            }
        }

        $response = $this->client->post('/finetune', $params);

        return $response;
    }

    /**
     * Generate an image with a fine-tuned FLUX Pro model
     *
     * @param array<string, mixed> $params Request parameters including finetune_id
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function generateWithFinetunedPro(array $params): ImageGenerationResponse
    {
        if (!isset($params['finetune_id'])) {
            throw new FluxApiException('Finetune ID is required');
        }

        $response = $this->client->post('/flux-pro-finetuned', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Generate an image with a fine-tuned FLUX 1.1 Pro Ultra model
     *
     * @param array<string, mixed> $params Request parameters including finetune_id
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function generateWithFinetunedUltra(array $params): ImageGenerationResponse
    {
        if (!isset($params['finetune_id'])) {
            throw new FluxApiException('Finetune ID is required');
        }

        $response = $this->client->post('/flux-pro-1.1-ultra-finetune', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Generate an image with fine-tuned FLUX1 Depth Pro using a control image
     *
     * @param array<string, mixed> $params Request parameters including finetune_id, prompt, and control_image
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function generateWithFinetunedDepth(array $params): ImageGenerationResponse
    {
        $requiredFields = ['finetune_id', 'prompt', 'control_image'];
        
        foreach ($requiredFields as $field) {
            if (!isset($params[$field])) {
                throw new FluxApiException("Required field '{$field}' is missing");
            }
        }

        $response = $this->client->post('/flux-pro-1.0-depth-finetune', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Generate an image with fine-tuned FLUX1 Canny Pro using a control image
     *
     * @param array<string, mixed> $params Request parameters including finetune_id, prompt, and control_image
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function generateWithFinetunedCanny(array $params): ImageGenerationResponse
    {
        $requiredFields = ['finetune_id', 'prompt', 'control_image'];
        
        foreach ($requiredFields as $field) {
            if (!isset($params[$field])) {
                throw new FluxApiException("Required field '{$field}' is missing");
            }
        }

        $response = $this->client->post('/flux-pro-1.0-canny-finetune', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }

    /**
     * Generate an image with fine-tuned FLUX1 Fill Pro using an input image and mask
     *
     * @param array<string, mixed> $params Request parameters including finetune_id and image
     * @return ImageGenerationResponse
     * @throws FluxApiException
     */
    public function generateWithFinetunedFill(array $params): ImageGenerationResponse
    {
        $requiredFields = ['finetune_id', 'image'];
        
        foreach ($requiredFields as $field) {
            if (!isset($params[$field])) {
                throw new FluxApiException("Required field '{$field}' is missing");
            }
        }

        $response = $this->client->post('/flux-pro-1.0-fill-finetune', $params);
        
        return ImageGenerationResponse::fromArray($response);
    }
}