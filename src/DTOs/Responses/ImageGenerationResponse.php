<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\DTOs\Responses;

/**
 * Response data for image generation task submission.
 *
 * @author Lanos <https://github.com/l4nos>
 */
class ImageGenerationResponse
{
    /**
     * @param string $id Identifier for the submitted task
     * @param string $pollingUrl URL that can be polled via the Get Result endpoint
     */
    public function __construct(
        public readonly string $id,
        public readonly string $pollingUrl,
    ) {
    }

    /**
     * Create instance from API response array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) && is_string($data['id']) ? $data['id'] : '',
            pollingUrl: isset($data['polling_url']) && is_string($data['polling_url']) ? $data['polling_url'] : '',
        );
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'polling_url' => $this->pollingUrl,
        ];
    }

    /**
     * Extract the task ID from polling URL if needed.
     */
    public function getTaskId(): string
    {
        return $this->id;
    }
}
