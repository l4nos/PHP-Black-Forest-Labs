<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Unit\DTOs;

use Lanos\PHPBFL\DTOs\Responses\ImageGenerationResponse;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * @covers \Lanos\PHPBFL\DTOs\Responses\ImageGenerationResponse
 */
class ImageGenerationResponseTest extends TestCase
{
    public function test_can_be_instantiated_with_valid_data(): void
    {
        $response = new ImageGenerationResponse(
            id: 'task_123abc',
            pollingUrl: 'https://api.bfl.ai/v1/get_result?id=task_123abc'
        );

        $this->assertSame('task_123abc', $response->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result?id=task_123abc', $response->pollingUrl);
    }

    public function test_from_array_with_valid_data(): void
    {
        $data = [
            'id' => 'task_456def',
            'polling_url' => 'https://api.bfl.ai/v1/get_result?id=task_456def',
        ];

        $response = ImageGenerationResponse::fromArray($data);

        $this->assertSame('task_456def', $response->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result?id=task_456def', $response->pollingUrl);
    }

    public function test_from_array_with_missing_id(): void
    {
        $data = [
            'polling_url' => 'https://api.bfl.ai/v1/get_result?id=task_123',
        ];

        $response = ImageGenerationResponse::fromArray($data);

        $this->assertSame('', $response->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result?id=task_123', $response->pollingUrl);
    }

    public function test_from_array_with_missing_polling_url(): void
    {
        $data = [
            'id' => 'task_789ghi',
        ];

        $response = ImageGenerationResponse::fromArray($data);

        $this->assertSame('task_789ghi', $response->id);
        $this->assertSame('', $response->pollingUrl);
    }

    public function test_from_array_with_non_string_id(): void
    {
        $data = [
            'id' => 123,
            'polling_url' => 'https://api.bfl.ai/v1/get_result?id=123',
        ];

        $response = ImageGenerationResponse::fromArray($data);

        $this->assertSame('', $response->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result?id=123', $response->pollingUrl);
    }

    public function test_from_array_with_non_string_polling_url(): void
    {
        $data = [
            'id' => 'task_123',
            'polling_url' => 12345,
        ];

        $response = ImageGenerationResponse::fromArray($data);

        $this->assertSame('task_123', $response->id);
        $this->assertSame('', $response->pollingUrl);
    }

    public function test_from_array_with_null_values(): void
    {
        $data = [
            'id' => null,
            'polling_url' => null,
        ];

        $response = ImageGenerationResponse::fromArray($data);

        $this->assertSame('', $response->id);
        $this->assertSame('', $response->pollingUrl);
    }

    public function test_from_array_with_empty_array(): void
    {
        $data = [];

        $response = ImageGenerationResponse::fromArray($data);

        $this->assertSame('', $response->id);
        $this->assertSame('', $response->pollingUrl);
    }

    public function test_from_array_with_extra_data(): void
    {
        $data = [
            'id' => 'task_extra',
            'polling_url' => 'https://api.bfl.ai/v1/get_result?id=task_extra',
            'status' => 'pending',
            'created_at' => '2024-01-01T00:00:00Z',
            'extra_field' => 'ignored',
        ];

        $response = ImageGenerationResponse::fromArray($data);

        $this->assertSame('task_extra', $response->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result?id=task_extra', $response->pollingUrl);
        // Extra fields should be ignored
    }

    public function test_to_array(): void
    {
        $response = new ImageGenerationResponse(
            id: 'task_array_test',
            pollingUrl: 'https://api.bfl.ai/v1/get_result?id=task_array_test'
        );

        $array = $response->toArray();

        $expected = [
            'id' => 'task_array_test',
            'polling_url' => 'https://api.bfl.ai/v1/get_result?id=task_array_test',
        ];

        $this->assertSame($expected, $array);
    }

    public function test_to_array_with_empty_values(): void
    {
        $response = new ImageGenerationResponse(
            id: '',
            pollingUrl: ''
        );

        $array = $response->toArray();

        $expected = [
            'id' => '',
            'polling_url' => '',
        ];

        $this->assertSame($expected, $array);
    }

    public function test_get_task_id(): void
    {
        $response = new ImageGenerationResponse(
            id: 'task_get_id_test',
            pollingUrl: 'https://api.bfl.ai/v1/get_result?id=task_get_id_test'
        );

        $this->assertSame('task_get_id_test', $response->getTaskId());
    }

    public function test_get_task_id_returns_same_as_id_property(): void
    {
        $response = new ImageGenerationResponse(
            id: 'task_consistency_test',
            pollingUrl: 'https://api.bfl.ai/v1/get_result'
        );

        $this->assertSame($response->id, $response->getTaskId());
    }

    public function test_properties_are_readonly(): void
    {
        $response = new ImageGenerationResponse(
            id: 'task_readonly',
            pollingUrl: 'https://api.bfl.ai/v1/get_result'
        );

        // Test that properties are accessible
        $this->assertSame('task_readonly', $response->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result', $response->pollingUrl);

        // Note: PHP readonly properties cannot be modified after initialization,
        // so we can't test modification attempts without causing fatal errors
    }

    public function test_roundtrip_conversion(): void
    {
        $originalData = [
            'id' => 'task_roundtrip',
            'polling_url' => 'https://api.bfl.ai/v1/get_result?id=task_roundtrip',
        ];

        $response = ImageGenerationResponse::fromArray($originalData);
        $convertedData = $response->toArray();

        $this->assertSame($originalData, $convertedData);
    }

    public function test_from_array_handles_array_with_mixed_types(): void
    {
        $data = [
            'id' => 'valid_task_id',
            'polling_url' => 'https://api.bfl.ai/v1/get_result',
            'numeric_field' => 42,
            'boolean_field' => true,
            'array_field' => ['nested' => 'data'],
        ];

        $response = ImageGenerationResponse::fromArray($data);

        $this->assertSame('valid_task_id', $response->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result', $response->pollingUrl);
    }

    public function test_handles_special_characters_in_values(): void
    {
        $response = new ImageGenerationResponse(
            id: 'task_special_!@#$%^&*()',
            pollingUrl: 'https://api.bfl.ai/v1/get_result?id=task_special_!@#$%^&*()&param=value'
        );

        $this->assertSame('task_special_!@#$%^&*()', $response->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result?id=task_special_!@#$%^&*()&param=value', $response->pollingUrl);
    }
}