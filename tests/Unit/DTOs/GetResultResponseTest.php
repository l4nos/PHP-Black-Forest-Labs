<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Unit\DTOs;

use Lanos\PHPBFL\DTOs\Responses\GetResultResponse;
use Lanos\PHPBFL\Enums\ResultStatus;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * @covers \Lanos\PHPBFL\DTOs\Responses\GetResultResponse
 */
class GetResultResponseTest extends TestCase
{
    public function test_can_be_instantiated(): void
    {
        $response = new GetResultResponse(
            id: 'task_123',
            status: ResultStatus::READY,
            result: 'https://example.com/image.jpg',
            progress: 100.0,
            details: ['model' => 'flux-pro'],
            preview: ['thumbnail' => 'url']
        );
        
        $this->assertSame('task_123', $response->id);
        $this->assertSame(ResultStatus::READY, $response->status);
        $this->assertSame('https://example.com/image.jpg', $response->result);
        $this->assertSame(100.0, $response->progress);
        $this->assertSame(['model' => 'flux-pro'], $response->details);
        $this->assertSame(['thumbnail' => 'url'], $response->preview);
    }

    public function test_from_array_creates_correct_instance(): void
    {
        $data = [
            'id' => 'task_456',
            'status' => 'Pending',
            'result' => null,
            'progress' => 50.5,
            'details' => ['step' => 'processing'],
            'preview' => null
        ];
        
        $response = GetResultResponse::fromArray($data);
        
        $this->assertSame('task_456', $response->id);
        $this->assertSame(ResultStatus::PENDING, $response->status);
        $this->assertNull($response->result);
        $this->assertSame(50.5, $response->progress);
        $this->assertSame(['step' => 'processing'], $response->details);
        $this->assertNull($response->preview);
    }

    public function test_from_array_handles_missing_optional_fields(): void
    {
        $data = [
            'id' => 'task_789',
            'status' => 'Ready'
        ];
        
        $response = GetResultResponse::fromArray($data);
        
        $this->assertSame('task_789', $response->id);
        $this->assertSame(ResultStatus::READY, $response->status);
        $this->assertNull($response->result);
        $this->assertNull($response->progress);
        $this->assertNull($response->details);
        $this->assertNull($response->preview);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $response = new GetResultResponse(
            id: 'task_test',
            status: ResultStatus::READY,
            result: 'image_url',
            progress: 100.0
        );
        
        $array = $response->toArray();
        
        $this->assertSame([
            'id' => 'task_test',
            'status' => 'Ready',
            'result' => 'image_url',
            'progress' => 100.0,
            'details' => null,
            'preview' => null
        ], $array);
    }

    public function test_is_complete_returns_true_for_ready_status(): void
    {
        $response = new GetResultResponse('task', ResultStatus::READY);
        
        $this->assertTrue($response->isComplete());
    }

    public function test_is_complete_returns_true_for_error_status(): void
    {
        $response = new GetResultResponse('task', ResultStatus::ERROR);
        
        $this->assertTrue($response->isComplete());
    }

    public function test_is_complete_returns_false_for_pending_status(): void
    {
        $response = new GetResultResponse('task', ResultStatus::PENDING);
        
        $this->assertFalse($response->isComplete());
    }

    public function test_is_failed_returns_true_for_error_status(): void
    {
        $response = new GetResultResponse('task', ResultStatus::ERROR);
        
        $this->assertTrue($response->isFailed());
    }

    public function test_is_failed_returns_true_for_content_moderated(): void
    {
        $response = new GetResultResponse('task', ResultStatus::CONTENT_MODERATED);
        
        $this->assertTrue($response->isFailed());
    }

    public function test_is_failed_returns_false_for_ready_status(): void
    {
        $response = new GetResultResponse('task', ResultStatus::READY);
        
        $this->assertFalse($response->isFailed());
    }

    public function test_is_successful_returns_true_only_for_ready(): void
    {
        $response = new GetResultResponse('task', ResultStatus::READY);
        
        $this->assertTrue($response->isSuccessful());
    }

    public function test_is_successful_returns_false_for_other_statuses(): void
    {
        $statuses = [
            ResultStatus::PENDING,
            ResultStatus::ERROR,
            ResultStatus::CONTENT_MODERATED,
            ResultStatus::TASK_NOT_FOUND
        ];
        
        foreach ($statuses as $status) {
            $response = new GetResultResponse('task', $status);
            $this->assertFalse($response->isSuccessful(), "Status {$status->value} should not be successful");
        }
    }

    public function test_is_in_progress_returns_true_for_pending_status(): void
    {
        $response = new GetResultResponse('task', ResultStatus::PENDING);
        
        $this->assertTrue($response->isInProgress());
    }

    public function test_is_in_progress_returns_true_for_request_moderated(): void
    {
        $response = new GetResultResponse('task', ResultStatus::REQUEST_MODERATED);
        
        $this->assertTrue($response->isInProgress());
    }

    public function test_is_in_progress_returns_false_for_complete_statuses(): void
    {
        $statuses = [
            ResultStatus::READY,
            ResultStatus::ERROR,
            ResultStatus::CONTENT_MODERATED,
            ResultStatus::TASK_NOT_FOUND
        ];
        
        foreach ($statuses as $status) {
            $response = new GetResultResponse('task', $status);
            $this->assertFalse($response->isInProgress(), "Status {$status->value} should not be in progress");
        }
    }

    public function test_get_result_as_array_returns_array_result(): void
    {
        $arrayResult = ['images' => ['url1', 'url2']];
        $response = new GetResultResponse('task', ResultStatus::READY, $arrayResult);
        
        $this->assertSame($arrayResult, $response->getResultAsArray());
    }

    public function test_get_result_as_array_returns_null_for_non_array(): void
    {
        $response = new GetResultResponse('task', ResultStatus::READY, 'string result');
        
        $this->assertNull($response->getResultAsArray());
    }

    public function test_get_result_as_string_returns_string_result(): void
    {
        $response = new GetResultResponse('task', ResultStatus::READY, 'https://example.com/image.jpg');
        
        $this->assertSame('https://example.com/image.jpg', $response->getResultAsString());
    }

    public function test_get_result_as_string_returns_null_for_non_string(): void
    {
        $response = new GetResultResponse('task', ResultStatus::READY, ['array' => 'result']);
        
        $this->assertNull($response->getResultAsString());
    }

    public function test_get_progress_percentage_returns_progress(): void
    {
        $response = new GetResultResponse('task', ResultStatus::PENDING, progress: 75.5);
        
        $this->assertSame(75.5, $response->getProgressPercentage());
    }

    public function test_get_progress_percentage_returns_null_when_no_progress(): void
    {
        $response = new GetResultResponse('task', ResultStatus::PENDING);
        
        $this->assertNull($response->getProgressPercentage());
    }
}