<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Unit\Services;

use Lanos\PHPBFL\DTOs\Responses\GetResultResponse;
use Lanos\PHPBFL\Enums\ResultStatus;
use Lanos\PHPBFL\Exceptions\FluxApiException;
use Lanos\PHPBFL\Services\UtilityService;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * @covers \Lanos\PHPBFL\Services\UtilityService
 */
class UtilityServiceTest extends TestCase
{
    private UtilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $client = $this->createMockClient();
        $this->service = new UtilityService($client);
    }

    public function test_get_result_with_valid_task_id(): void
    {
        $resultData = $this->getSampleResultResponse();
        $client = $this->createMockClient([
            $this->createJsonResponse($resultData)
        ]);
        $service = new UtilityService($client);

        $result = $service->getResult('task_123');

        $this->assertInstanceOf(GetResultResponse::class, $result);
        $this->assertSame($resultData['id'], $result->id);
        $this->assertSame(ResultStatus::READY, $result->status);
        $this->assertSame($resultData['result'], $result->result);
    }

    public function test_get_result_throws_exception_for_empty_task_id(): void
    {
        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Task ID cannot be empty');

        $this->service->getResult('');
    }

    public function test_poll_result_returns_immediately_when_complete(): void
    {
        $resultData = $this->getSampleResultResponse('Ready');
        $client = $this->createMockClient([
            $this->createJsonResponse($resultData)
        ]);
        $service = new UtilityService($client);

        $result = $service->pollResult('task_123', 5, 1);

        $this->assertTrue($result->isComplete());
        $this->assertTrue($result->isSuccessful());
    }

    public function test_poll_result_throws_exception_for_empty_task_id(): void
    {
        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Task ID cannot be empty');

        $this->service->pollResult('');
    }

    public function test_poll_result_throws_exception_for_invalid_max_attempts(): void
    {
        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Max attempts must be greater than 0');

        $this->service->pollResult('task_123', 0);
    }

    public function test_poll_result_throws_exception_for_invalid_delay(): void
    {
        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Delay seconds must be at least 1');

        $this->service->pollResult('task_123', 5, 0);
    }

    public function test_poll_result_times_out_after_max_attempts(): void
    {
        $pendingData = $this->getSampleResultResponse('Pending');
        $client = $this->createMockClient([
            $this->createJsonResponse($pendingData),
            $this->createJsonResponse($pendingData),
        ]);
        $service = new UtilityService($client);

        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Task polling timed out after 2 attempts');

        $service->pollResult('task_123', 2, 1);
    }

    public function test_wait_for_completion_uses_default_parameters(): void
    {
        $resultData = $this->getSampleResultResponse('Ready');
        $client = $this->createMockClient([
            $this->createJsonResponse($resultData)
        ]);
        $service = new UtilityService($client);

        $result = $service->waitForCompletion('task_123');

        $this->assertTrue($result->isComplete());
    }

    public function test_is_task_complete_returns_true_for_ready_status(): void
    {
        $resultData = $this->getSampleResultResponse('Ready');
        $client = $this->createMockClient([
            $this->createJsonResponse($resultData)
        ]);
        $service = new UtilityService($client);

        $isComplete = $service->isTaskComplete('task_123');

        $this->assertTrue($isComplete);
    }

    public function test_is_task_complete_returns_false_for_pending_status(): void
    {
        $resultData = $this->getSampleResultResponse('Pending');
        $client = $this->createMockClient([
            $this->createJsonResponse($resultData)
        ]);
        $service = new UtilityService($client);

        $isComplete = $service->isTaskComplete('task_123');

        $this->assertFalse($isComplete);
    }

    public function test_get_progress_returns_progress_value(): void
    {
        $resultData = $this->getSampleResultResponse('Pending');
        $client = $this->createMockClient([
            $this->createJsonResponse($resultData)
        ]);
        $service = new UtilityService($client);

        $progress = $service->getProgress('task_123');

        $this->assertSame(50.0, $progress);
    }

    public function test_get_progress_returns_null_when_no_progress(): void
    {
        $resultData = array_merge(
            $this->getSampleResultResponse('Pending'),
            ['progress' => null]
        );
        $client = $this->createMockClient([
            $this->createJsonResponse($resultData)
        ]);
        $service = new UtilityService($client);

        $progress = $service->getProgress('task_123');

        $this->assertNull($progress);
    }

    /**
     * @dataProvider taskStatusProvider
     */
    public function test_handles_different_task_statuses(string $status, bool $shouldBeComplete): void
    {
        $resultData = $this->getSampleResultResponse($status);
        $client = $this->createMockClient([
            $this->createJsonResponse($resultData)
        ]);
        $service = new UtilityService($client);

        $result = $service->getResult('task_123');

        $this->assertSame($shouldBeComplete, $result->isComplete());
    }

    public static function taskStatusProvider(): array
    {
        return [
            ['Ready', true],
            ['Error', true],
            ['Content Moderated', true],
            ['Pending', false],
            ['Request Moderated', false],
            ['Task not found', true],
        ];
    }
}