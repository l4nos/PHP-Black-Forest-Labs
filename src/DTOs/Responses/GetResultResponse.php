<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\DTOs\Responses;

use Lanos\PHPBFL\Enums\ResultStatus;

/**
 * Response data for task result queries
 *
 * @package Lanos\PHPBFL\DTOs\Responses
 * @author Lanos <https://github.com/l4nos>
 */
class GetResultResponse
{
    /**
     * @param string $id Unique identifier for the task
     * @param ResultStatus $status Status of the generation task
     * @param mixed $result Task result, varies by task type
     * @param float|null $progress Generation progress as a percentage
     * @param array<string, mixed>|null $details Additional task details
     * @param array<string, mixed>|null $preview Preview of the generated image
     */
    public function __construct(
        public readonly string $id,
        public readonly ResultStatus $status,
        public readonly mixed $result = null,
        public readonly ?float $progress = null,
        public readonly ?array $details = null,
        public readonly ?array $preview = null,
    ) {}

    /**
     * Create instance from API response array
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) && is_string($data['id']) ? $data['id'] : '',
            status: ResultStatus::from(isset($data['status']) && is_string($data['status']) ? $data['status'] : ''),
            result: $data['result'] ?? null,
            progress: isset($data['progress']) && is_numeric($data['progress']) ? (float) $data['progress'] : null,
            details: isset($data['details']) && is_array($data['details']) ? $data['details'] : null,
            preview: isset($data['preview']) && is_array($data['preview']) ? $data['preview'] : null,
        );
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'result' => $this->result,
            'progress' => $this->progress,
            'details' => $this->details,
            'preview' => $this->preview,
        ];
    }

    /**
     * Check if the task is complete
     */
    public function isComplete(): bool
    {
        return $this->status->isComplete();
    }

    /**
     * Check if the task failed
     */
    public function isFailed(): bool
    {
        return $this->status->isFailed();
    }

    /**
     * Check if the task was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status->isSuccessful();
    }

    /**
     * Check if the task is still in progress
     */
    public function isInProgress(): bool
    {
        return $this->status->isInProgress();
    }

    /**
     * Get the result as an array if it's an array, otherwise null
     *
     * @return array<string, mixed>|null
     */
    public function getResultAsArray(): ?array
    {
        return is_array($this->result) ? $this->result : null;
    }

    /**
     * Get the result as a string if it's a string, otherwise null
     */
    public function getResultAsString(): ?string
    {
        return is_string($this->result) ? $this->result : null;
    }

    /**
     * Get progress as a percentage (0-100)
     */
    public function getProgressPercentage(): ?float
    {
        return $this->progress;
    }
}