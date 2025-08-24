<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Unit\Enums;

use Lanos\PHPBFL\Enums\ResultStatus;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * @covers \Lanos\PHPBFL\Enums\ResultStatus
 */
class ResultStatusTest extends TestCase
{
    public function test_enum_cases_exist(): void
    {
        $this->assertTrue(enum_exists(ResultStatus::class));

        $cases = ResultStatus::cases();
        $this->assertCount(6, $cases);

        $caseNames = array_map(fn ($case) => $case->name, $cases);
        $expectedCases = ['TASK_NOT_FOUND', 'PENDING', 'REQUEST_MODERATED', 'CONTENT_MODERATED', 'READY', 'ERROR'];

        $this->assertSame($expectedCases, $caseNames);
    }

    public function test_enum_values_are_correct(): void
    {
        $this->assertSame('Task not found', ResultStatus::TASK_NOT_FOUND->value);
        $this->assertSame('Pending', ResultStatus::PENDING->value);
        $this->assertSame('Request Moderated', ResultStatus::REQUEST_MODERATED->value);
        $this->assertSame('Content Moderated', ResultStatus::CONTENT_MODERATED->value);
        $this->assertSame('Ready', ResultStatus::READY->value);
        $this->assertSame('Error', ResultStatus::ERROR->value);
    }

    public function test_can_create_from_string_values(): void
    {
        $this->assertSame(ResultStatus::TASK_NOT_FOUND, ResultStatus::from('Task not found'));
        $this->assertSame(ResultStatus::PENDING, ResultStatus::from('Pending'));
        $this->assertSame(ResultStatus::REQUEST_MODERATED, ResultStatus::from('Request Moderated'));
        $this->assertSame(ResultStatus::CONTENT_MODERATED, ResultStatus::from('Content Moderated'));
        $this->assertSame(ResultStatus::READY, ResultStatus::from('Ready'));
        $this->assertSame(ResultStatus::ERROR, ResultStatus::from('Error'));
    }

    public function test_try_from_with_valid_values(): void
    {
        $this->assertSame(ResultStatus::TASK_NOT_FOUND, ResultStatus::tryFrom('Task not found'));
        $this->assertSame(ResultStatus::PENDING, ResultStatus::tryFrom('Pending'));
        $this->assertSame(ResultStatus::REQUEST_MODERATED, ResultStatus::tryFrom('Request Moderated'));
        $this->assertSame(ResultStatus::CONTENT_MODERATED, ResultStatus::tryFrom('Content Moderated'));
        $this->assertSame(ResultStatus::READY, ResultStatus::tryFrom('Ready'));
        $this->assertSame(ResultStatus::ERROR, ResultStatus::tryFrom('Error'));
    }

    public function test_try_from_with_invalid_value(): void
    {
        $this->assertNull(ResultStatus::tryFrom('invalid'));
        $this->assertNull(ResultStatus::tryFrom(''));
        $this->assertNull(ResultStatus::tryFrom('pending'));
        $this->assertNull(ResultStatus::tryFrom('PENDING'));
        $this->assertNull(ResultStatus::tryFrom('ready'));
        $this->assertNull(ResultStatus::tryFrom('error'));
    }

    public function test_from_throws_exception_with_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        ResultStatus::from('invalid');
    }

    public function test_is_complete_returns_true_for_final_states(): void
    {
        // Complete states should return true
        $this->assertTrue(ResultStatus::READY->isComplete());
        $this->assertTrue(ResultStatus::ERROR->isComplete());
        $this->assertTrue(ResultStatus::CONTENT_MODERATED->isComplete());
        $this->assertTrue(ResultStatus::TASK_NOT_FOUND->isComplete());
    }

    public function test_is_complete_returns_false_for_in_progress_states(): void
    {
        // In-progress states should return false
        $this->assertFalse(ResultStatus::PENDING->isComplete());
        $this->assertFalse(ResultStatus::REQUEST_MODERATED->isComplete());
    }

    public function test_is_failed_returns_true_for_error_states(): void
    {
        // Failed states should return true
        $this->assertTrue(ResultStatus::ERROR->isFailed());
        $this->assertTrue(ResultStatus::CONTENT_MODERATED->isFailed());
        $this->assertTrue(ResultStatus::TASK_NOT_FOUND->isFailed());
    }

    public function test_is_failed_returns_false_for_success_and_progress_states(): void
    {
        // Success and in-progress states should return false
        $this->assertFalse(ResultStatus::READY->isFailed());
        $this->assertFalse(ResultStatus::PENDING->isFailed());
        $this->assertFalse(ResultStatus::REQUEST_MODERATED->isFailed());
    }

    public function test_is_successful_returns_true_only_for_ready(): void
    {
        // Only READY should return true
        $this->assertTrue(ResultStatus::READY->isSuccessful());

        // All other states should return false
        $this->assertFalse(ResultStatus::TASK_NOT_FOUND->isSuccessful());
        $this->assertFalse(ResultStatus::PENDING->isSuccessful());
        $this->assertFalse(ResultStatus::REQUEST_MODERATED->isSuccessful());
        $this->assertFalse(ResultStatus::CONTENT_MODERATED->isSuccessful());
        $this->assertFalse(ResultStatus::ERROR->isSuccessful());
    }

    public function test_is_in_progress_returns_true_for_active_states(): void
    {
        // In-progress states should return true
        $this->assertTrue(ResultStatus::PENDING->isInProgress());
        $this->assertTrue(ResultStatus::REQUEST_MODERATED->isInProgress());
    }

    public function test_is_in_progress_returns_false_for_final_states(): void
    {
        // Final states should return false
        $this->assertFalse(ResultStatus::READY->isInProgress());
        $this->assertFalse(ResultStatus::ERROR->isInProgress());
        $this->assertFalse(ResultStatus::CONTENT_MODERATED->isInProgress());
        $this->assertFalse(ResultStatus::TASK_NOT_FOUND->isInProgress());
    }

    public function test_status_state_consistency(): void
    {
        foreach (ResultStatus::cases() as $status) {
            // A status cannot be both successful and failed
            if ($status->isSuccessful()) {
                $this->assertFalse($status->isFailed(), "Status {$status->name} cannot be both successful and failed");
            }

            // A status cannot be both in progress and complete
            if ($status->isInProgress()) {
                $this->assertFalse($status->isComplete(), "Status {$status->name} cannot be both in progress and complete");
            }

            // A successful status must be complete
            if ($status->isSuccessful()) {
                $this->assertTrue($status->isComplete(), "Status {$status->name} must be complete if successful");
            }

            // A failed status must be complete
            if ($status->isFailed()) {
                $this->assertTrue($status->isComplete(), "Status {$status->name} must be complete if failed");
            }
        }
    }

    public function test_each_status_has_unique_behavior(): void
    {
        $behaviors = [];

        foreach (ResultStatus::cases() as $status) {
            $behavior = [
                'complete' => $status->isComplete(),
                'failed' => $status->isFailed(),
                'successful' => $status->isSuccessful(),
                'in_progress' => $status->isInProgress(),
            ];

            $behaviorKey = json_encode($behavior);

            if (!isset($behaviors[$behaviorKey])) {
                $behaviors[$behaviorKey] = [];
            }

            $behaviors[$behaviorKey][] = $status->name;
        }

        // Check that we have meaningful behavior distinctions
        $this->assertGreaterThan(1, count($behaviors), 'Status behaviors should be diverse');
    }

    /**
     * @dataProvider statusBehaviorProvider
     */
    public function test_specific_status_behaviors(
        ResultStatus $status,
        bool $expectedComplete,
        bool $expectedFailed,
        bool $expectedSuccessful,
        bool $expectedInProgress
    ): void {
        $this->assertSame($expectedComplete, $status->isComplete(), "isComplete() for {$status->name}");
        $this->assertSame($expectedFailed, $status->isFailed(), "isFailed() for {$status->name}");
        $this->assertSame($expectedSuccessful, $status->isSuccessful(), "isSuccessful() for {$status->name}");
        $this->assertSame($expectedInProgress, $status->isInProgress(), "isInProgress() for {$status->name}");
    }

    public static function statusBehaviorProvider(): array
    {
        return [
            'TASK_NOT_FOUND' => [ResultStatus::TASK_NOT_FOUND, true, true, false, false],
            'PENDING' => [ResultStatus::PENDING, false, false, false, true],
            'REQUEST_MODERATED' => [ResultStatus::REQUEST_MODERATED, false, false, false, true],
            'CONTENT_MODERATED' => [ResultStatus::CONTENT_MODERATED, true, true, false, false],
            'READY' => [ResultStatus::READY, true, false, true, false],
            'ERROR' => [ResultStatus::ERROR, true, true, false, false],
        ];
    }

    public function test_enum_is_instance_of_correct_type(): void
    {
        $status = ResultStatus::READY;

        $this->assertInstanceOf(ResultStatus::class, $status);
        $this->assertInstanceOf(\UnitEnum::class, $status);
        $this->assertInstanceOf(\BackedEnum::class, $status);
    }

    public function test_enum_comparison(): void
    {
        $status1 = ResultStatus::READY;
        $status2 = ResultStatus::READY;
        $status3 = ResultStatus::ERROR;

        $this->assertSame($status1, $status2);
        $this->assertTrue($status1 === $status2);
        $this->assertNotSame($status1, $status3);
        $this->assertFalse($status1 === $status3);
    }

    public function test_enum_serialization(): void
    {
        $status = ResultStatus::READY;

        // Test that we can serialize and get the string value
        $this->assertSame('Ready', (string) $status->value);

        // Test JSON serialization
        $json = json_encode($status);
        $this->assertSame('"Ready"', $json);
    }

    public function test_enum_in_array_operations(): void
    {
        $statuses = [ResultStatus::READY, ResultStatus::ERROR];

        $this->assertTrue(in_array(ResultStatus::READY, $statuses, true));
        $this->assertTrue(in_array(ResultStatus::ERROR, $statuses, true));
        $this->assertFalse(in_array(ResultStatus::PENDING, $statuses, true));
    }

    public function test_enum_switch_statement(): void
    {
        foreach (ResultStatus::cases() as $status) {
            $result = match ($status) {
                ResultStatus::TASK_NOT_FOUND => 'not_found',
                ResultStatus::PENDING => 'pending',
                ResultStatus::REQUEST_MODERATED => 'request_moderated',
                ResultStatus::CONTENT_MODERATED => 'content_moderated',
                ResultStatus::READY => 'ready',
                ResultStatus::ERROR => 'error',
            };

            $this->assertIsString($result);
            $this->assertNotEmpty($result);
        }
    }

    public function test_enum_name_and_value_properties(): void
    {
        $testCases = [
            ['TASK_NOT_FOUND', 'Task not found'],
            ['PENDING', 'Pending'],
            ['REQUEST_MODERATED', 'Request Moderated'],
            ['CONTENT_MODERATED', 'Content Moderated'],
            ['READY', 'Ready'],
            ['ERROR', 'Error'],
        ];

        foreach ($testCases as [$expectedName, $expectedValue]) {
            $status = ResultStatus::from($expectedValue);
            $this->assertSame($expectedName, $status->name);
            $this->assertSame($expectedValue, $status->value);
        }
    }

    public function test_status_values_are_human_readable(): void
    {
        foreach (ResultStatus::cases() as $status) {
            $value = $status->value;

            // Values should be human-readable strings
            $this->assertIsString($value);
            $this->assertNotEmpty($value);
            $this->assertGreaterThan(3, strlen($value));

            // Should contain at least one letter (not just symbols/numbers)
            $this->assertMatchesRegularExpression('/[a-zA-Z]/', $value);
        }
    }

    public function test_logical_status_groupings(): void
    {
        // Test that statuses are logically grouped
        $completeStatuses = array_filter(
            ResultStatus::cases(),
            fn ($status) => $status->isComplete()
        );

        $inProgressStatuses = array_filter(
            ResultStatus::cases(),
            fn ($status) => $status->isInProgress()
        );

        // All statuses should be either complete or in progress, but not both
        $this->assertCount(6, $completeStatuses + $inProgressStatuses);

        // No overlap between complete and in-progress
        $completeNames = array_map(fn ($s) => $s->name, $completeStatuses);
        $inProgressNames = array_map(fn ($s) => $s->name, $inProgressStatuses);
        $this->assertEmpty(array_intersect($completeNames, $inProgressNames));
    }
}
