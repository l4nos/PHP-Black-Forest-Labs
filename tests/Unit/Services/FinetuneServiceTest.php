<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Unit\Services;

use Lanos\PHPBFL\DTOs\Responses\ImageGenerationResponse;
use Lanos\PHPBFL\Exceptions\FluxApiException;
use Lanos\PHPBFL\Services\FinetuneService;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * @covers \Lanos\PHPBFL\Services\FinetuneService
 */
class FinetuneServiceTest extends TestCase
{
    private FinetuneService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FinetuneService($this->createMockClient());
    }

    public function test_get_details_with_valid_id(): void
    {
        $expectedResponse = [
            'finetune_id' => 'ft_123',
            'status' => 'completed',
            'created_at' => '2024-01-01T00:00:00Z',
        ];

        $client = $this->createMockClient([
            $this->createJsonResponse($expectedResponse),
        ]);

        $service = new FinetuneService($client);
        $result = $service->getDetails('ft_123');

        $this->assertSame($expectedResponse, $result);
    }

    public function test_get_details_throws_exception_for_empty_id(): void
    {
        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Finetune ID cannot be empty');

        $this->service->getDetails('');
    }

    public function test_list_my_finetunes(): void
    {
        $expectedResponse = [
            'finetunes' => [
                ['id' => 'ft_1', 'name' => 'Model 1'],
                ['id' => 'ft_2', 'name' => 'Model 2'],
            ],
        ];

        $client = $this->createMockClient([
            $this->createJsonResponse($expectedResponse),
        ]);

        $service = new FinetuneService($client);
        $result = $service->listMyFinetunes();

        $this->assertSame($expectedResponse, $result);
    }

    public function test_delete_with_valid_id(): void
    {
        $expectedResponse = [
            'success' => true,
            'message' => 'Finetune deleted successfully',
        ];

        $client = $this->createMockClient([
            $this->createJsonResponse($expectedResponse),
        ]);

        $service = new FinetuneService($client);
        $result = $service->delete('ft_123');

        $this->assertSame($expectedResponse, $result);
    }

    public function test_delete_throws_exception_for_empty_id(): void
    {
        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Finetune ID cannot be empty');

        $this->service->delete('');
    }

    public function test_create_with_valid_params(): void
    {
        $params = [
            'file_data' => 'base64_encoded_data',
            'finetune_comment' => 'Test finetune',
            'mode' => 'lora',
            'trigger_word' => 'mytrigger',
            'iterations' => 1000,
        ];

        $expectedResponse = [
            'finetune_id' => 'ft_new_123',
            'status' => 'processing',
        ];

        $client = $this->createMockClient([
            $this->createJsonResponse($expectedResponse),
        ]);

        $service = new FinetuneService($client);
        $result = $service->create($params);

        $this->assertSame($expectedResponse, $result);
    }

    public function test_create_throws_exception_for_missing_required_fields(): void
    {
        $params = [
            'file_data' => 'base64_encoded_data',
            'finetune_comment' => 'Test finetune',
            // Missing: mode, trigger_word, iterations
        ];

        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage("Required field 'mode' is missing");

        $this->service->create($params);
    }

    public function test_create_validates_all_required_fields(): void
    {
        $requiredFields = ['file_data', 'finetune_comment', 'mode', 'trigger_word', 'iterations'];

        foreach ($requiredFields as $missingField) {
            $params = [
                'file_data' => 'base64_encoded_data',
                'finetune_comment' => 'Test finetune',
                'mode' => 'lora',
                'trigger_word' => 'mytrigger',
                'iterations' => 1000,
            ];

            unset($params[$missingField]);

            try {
                $this->service->create($params);
                $this->fail("Expected exception for missing field: {$missingField}");
            } catch (FluxApiException $e) {
                $this->assertStringContainsString($missingField, $e->getMessage());
            }
        }
    }

    public function test_generate_with_finetuned_pro(): void
    {
        $params = [
            'finetune_id' => 'ft_123',
            'prompt' => 'A beautiful landscape',
            'width' => 1024,
            'height' => 768,
        ];

        $responseData = [
            'id' => 'task_123',
            'polling_url' => 'https://api.bfl.ai/v1/get_result?id=task_123',
        ];

        $client = $this->createMockClient([
            $this->createJsonResponse($responseData),
        ]);

        $service = new FinetuneService($client);
        $result = $service->generateWithFinetunedPro($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $result);
        $this->assertSame('task_123', $result->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result?id=task_123', $result->pollingUrl);
    }

    public function test_generate_with_finetuned_pro_throws_exception_for_missing_finetune_id(): void
    {
        $params = [
            'prompt' => 'A beautiful landscape',
        ];

        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Finetune ID is required');

        $this->service->generateWithFinetunedPro($params);
    }

    public function test_generate_with_finetuned_ultra(): void
    {
        $params = [
            'finetune_id' => 'ft_ultra_123',
            'prompt' => 'Ultra high quality image',
        ];

        $responseData = [
            'id' => 'task_ultra_123',
            'polling_url' => 'https://api.bfl.ai/v1/get_result?id=task_ultra_123',
        ];

        $client = $this->createMockClient([
            $this->createJsonResponse($responseData),
        ]);

        $service = new FinetuneService($client);
        $result = $service->generateWithFinetunedUltra($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $result);
        $this->assertSame('task_ultra_123', $result->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result?id=task_ultra_123', $result->pollingUrl);
    }

    public function test_generate_with_finetuned_ultra_throws_exception_for_missing_finetune_id(): void
    {
        $params = [
            'prompt' => 'Ultra high quality image',
        ];

        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Finetune ID is required');

        $this->service->generateWithFinetunedUltra($params);
    }

    public function test_generate_with_finetuned_depth(): void
    {
        $params = [
            'finetune_id' => 'ft_depth_123',
            'prompt' => 'Depth-controlled image',
            'control_image' => 'base64_control_image',
        ];

        $responseData = [
            'id' => 'task_depth_123',
            'polling_url' => 'https://api.bfl.ai/v1/get_result?id=task_depth_123',
        ];

        $client = $this->createMockClient([
            $this->createJsonResponse($responseData),
        ]);

        $service = new FinetuneService($client);
        $result = $service->generateWithFinetunedDepth($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $result);
        $this->assertSame('task_depth_123', $result->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result?id=task_depth_123', $result->pollingUrl);
    }

    public function test_generate_with_finetuned_depth_validates_required_fields(): void
    {
        $requiredFields = ['finetune_id', 'prompt', 'control_image'];

        foreach ($requiredFields as $missingField) {
            $params = [
                'finetune_id' => 'ft_depth_123',
                'prompt' => 'Depth-controlled image',
                'control_image' => 'base64_control_image',
            ];

            unset($params[$missingField]);

            try {
                $this->service->generateWithFinetunedDepth($params);
                $this->fail("Expected exception for missing field: {$missingField}");
            } catch (FluxApiException $e) {
                $this->assertStringContainsString($missingField, $e->getMessage());
            }
        }
    }

    public function test_generate_with_finetuned_canny(): void
    {
        $params = [
            'finetune_id' => 'ft_canny_123',
            'prompt' => 'Canny edge detection',
            'control_image' => 'base64_control_image',
        ];

        $responseData = [
            'id' => 'task_canny_123',
            'polling_url' => 'https://api.bfl.ai/v1/get_result?id=task_canny_123',
        ];

        $client = $this->createMockClient([
            $this->createJsonResponse($responseData),
        ]);

        $service = new FinetuneService($client);
        $result = $service->generateWithFinetunedCanny($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $result);
        $this->assertSame('task_canny_123', $result->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result?id=task_canny_123', $result->pollingUrl);
    }

    public function test_generate_with_finetuned_canny_validates_required_fields(): void
    {
        $requiredFields = ['finetune_id', 'prompt', 'control_image'];

        foreach ($requiredFields as $missingField) {
            $params = [
                'finetune_id' => 'ft_canny_123',
                'prompt' => 'Canny edge detection',
                'control_image' => 'base64_control_image',
            ];

            unset($params[$missingField]);

            try {
                $this->service->generateWithFinetunedCanny($params);
                $this->fail("Expected exception for missing field: {$missingField}");
            } catch (FluxApiException $e) {
                $this->assertStringContainsString($missingField, $e->getMessage());
            }
        }
    }

    public function test_generate_with_finetuned_fill(): void
    {
        $params = [
            'finetune_id' => 'ft_fill_123',
            'image' => 'base64_input_image',
            'mask' => 'base64_mask_image',
        ];

        $responseData = [
            'id' => 'task_fill_123',
            'polling_url' => 'https://api.bfl.ai/v1/get_result?id=task_fill_123',
        ];

        $client = $this->createMockClient([
            $this->createJsonResponse($responseData),
        ]);

        $service = new FinetuneService($client);
        $result = $service->generateWithFinetunedFill($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $result);
        $this->assertSame('task_fill_123', $result->id);
        $this->assertSame('https://api.bfl.ai/v1/get_result?id=task_fill_123', $result->pollingUrl);
    }

    public function test_generate_with_finetuned_fill_validates_required_fields(): void
    {
        $requiredFields = ['finetune_id', 'image'];

        foreach ($requiredFields as $missingField) {
            $params = [
                'finetune_id' => 'ft_fill_123',
                'image' => 'base64_input_image',
            ];

            unset($params[$missingField]);

            try {
                $this->service->generateWithFinetunedFill($params);
                $this->fail("Expected exception for missing field: {$missingField}");
            } catch (FluxApiException $e) {
                $this->assertStringContainsString($missingField, $e->getMessage());
            }
        }
    }

    public function test_service_handles_api_errors_correctly(): void
    {
        $client = $this->createMockClient([
            $this->createErrorResponse('API rate limit exceeded', 429),
        ]);

        $service = new FinetuneService($client);

        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('API rate limit exceeded');
        $this->expectExceptionCode(429);

        $service->listMyFinetunes();
    }
}