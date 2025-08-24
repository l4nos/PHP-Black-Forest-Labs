<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Services;

use Lanos\PHPBFL\FluxClient;
use Lanos\PHPBFL\DTOs\Responses\GetResultResponse;
use Lanos\PHPBFL\Exceptions\FluxApiException;

/**
 * Service for handling utility operations like polling results
 *
 * @package Lanos\PHPBFL\Services
 * @author Lanos <https://github.com/l4nos>
 */
class UtilityService
{
    public function __construct(
        private FluxClient $client
    ) {}

    /**
     * Retrieve the status or final result for a previously submitted task
     *
     * @param string $taskId Task identifier returned when submitting the original request
     * @return GetResultResponse
     * @throws FluxApiException
     */
    public function getResult(string $taskId): GetResultResponse
    {
        if (empty($taskId)) {
            throw new FluxApiException('Task ID cannot be empty');
        }

        $response = $this->client->get('/get_result', [
            'id' => $taskId,
        ]);

        return GetResultResponse::fromArray($response);
    }

    /**
     * Poll for result with automatic retries until completion or timeout
     *
     * @param string $taskId Task identifier
     * @param int $maxAttempts Maximum number of polling attempts
     * @param int $delaySeconds Delay between polling attempts in seconds
     * @return GetResultResponse
     * @throws FluxApiException
     */
    public function pollResult(string $taskId, int $maxAttempts = 60, int $delaySeconds = 5): GetResultResponse
    {
        if (empty($taskId)) {
            throw new FluxApiException('Task ID cannot be empty');
        }

        if ($maxAttempts <= 0) {
            throw new FluxApiException('Max attempts must be greater than 0');
        }

        if ($delaySeconds < 1) {
            throw new FluxApiException('Delay seconds must be at least 1');
        }

        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $result = $this->getResult($taskId);

            if ($result->isComplete()) {
                return $result;
            }

            $attempts++;
            
            // Don't sleep on the last attempt if we're going to timeout
            if ($attempts < $maxAttempts) {
                sleep($delaySeconds);
            }
        }

        throw new FluxApiException("Task polling timed out after {$maxAttempts} attempts");
    }

    /**
     * Wait for task completion and return the result
     * This is an alias for pollResult with sensible defaults
     *
     * @param string $taskId Task identifier
     * @return GetResultResponse
     * @throws FluxApiException
     */
    public function waitForCompletion(string $taskId): GetResultResponse
    {
        return $this->pollResult($taskId, 120, 3); // 6 minutes max wait time
    }

    /**
     * Check if a task is complete without polling
     *
     * @param string $taskId Task identifier
     * @return bool
     * @throws FluxApiException
     */
    public function isTaskComplete(string $taskId): bool
    {
        $result = $this->getResult($taskId);
        return $result->isComplete();
    }

    /**
     * Get task progress if available
     *
     * @param string $taskId Task identifier
     * @return float|null Progress percentage or null if not available
     * @throws FluxApiException
     */
    public function getProgress(string $taskId): ?float
    {
        $result = $this->getResult($taskId);
        return $result->getProgressPercentage();
    }
}