<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Integration;

use Lanos\PHPBFL\Builders\ImageRequestBuilder;
use Lanos\PHPBFL\DTOs\Responses\ImageGenerationResponse;
use Lanos\PHPBFL\DTOs\Responses\GetResultResponse;
use Lanos\PHPBFL\Enums\OutputFormat;
use Lanos\PHPBFL\Enums\ResultStatus;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * Integration tests for complete FLUX API workflows
 *
 * @covers \Lanos\PHPBFL\FluxClient
 * @covers \Lanos\PHPBFL\Services\ImageGenerationService
 * @covers \Lanos\PHPBFL\Services\UtilityService
 * @covers \Lanos\PHPBFL\Builders\ImageRequestBuilder
 */
class FluxWorkflowTest extends TestCase
{
    public function test_complete_image_generation_workflow(): void
    {
        // Mock the API responses for the complete workflow
        $taskResponse = $this->getSampleTaskResponse();
        $pendingResponse = $this->getSampleResultResponse('Pending');
        $completedResponse = $this->getSampleResultResponse('Ready', 'https://example.com/generated.jpg');
        
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),        // Initial submission
            $this->createJsonResponse($pendingResponse),     // First poll - still pending
            $this->createJsonResponse($completedResponse)    // Second poll - completed
        ]);

        // Step 1: Build a request using the fluent builder
        $request = ImageRequestBuilder::create()
            ->withPrompt('A majestic dragon in a fantasy landscape')
            ->withDimensions(1024, 768)
            ->withSteps(50)
            ->withGuidance(3.0)
            ->asPng()
            ->buildFlux1Pro();

        // Step 2: Submit the generation request
        $submissionResponse = $client->imageGeneration()->flux1Pro($request);
        
        $this->assertInstanceOf(ImageGenerationResponse::class, $submissionResponse);
        $this->assertSame($taskResponse['id'], $submissionResponse->id);
        $this->assertSame($taskResponse['polling_url'], $submissionResponse->pollingUrl);

        // Step 3: Poll for the result (first poll - still pending)
        $firstPollResult = $client->utility()->getResult($submissionResponse->id);
        
        $this->assertInstanceOf(GetResultResponse::class, $firstPollResult);
        $this->assertSame(ResultStatus::PENDING, $firstPollResult->status);
        $this->assertTrue($firstPollResult->isInProgress());
        $this->assertFalse($firstPollResult->isComplete());

        // Step 4: Poll again (now completed)
        $finalResult = $client->utility()->getResult($submissionResponse->id);
        
        $this->assertInstanceOf(GetResultResponse::class, $finalResult);
        $this->assertSame(ResultStatus::READY, $finalResult->status);
        $this->assertTrue($finalResult->isComplete());
        $this->assertTrue($finalResult->isSuccessful());
        $this->assertFalse($finalResult->isFailed());
        $this->assertSame('https://example.com/generated.jpg', $finalResult->getResultAsString());
    }

    public function test_image_fill_workflow(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $completedResponse = $this->getSampleResultResponse('Ready', 'https://example.com/filled.jpg');
        
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
            $this->createJsonResponse($completedResponse)
        ]);

        // Step 1: Submit fill request
        $fillResponse = $client->imageGeneration()->flux1Fill([
            'image' => base64_encode('fake-image-data'),
            'mask' => base64_encode('fake-mask-data'),
            'prompt' => 'Fill with beautiful flowers',
            'steps' => 40,
            'guidance' => 50.0,
            'output_format' => OutputFormat::JPEG->value
        ]);

        $this->assertInstanceOf(ImageGenerationResponse::class, $fillResponse);
        
        // Step 2: Get the result
        $result = $client->utility()->getResult($fillResponse->id);
        
        $this->assertTrue($result->isSuccessful());
        $this->assertSame('https://example.com/filled.jpg', $result->getResultAsString());
    }

    public function test_error_handling_workflow(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $errorResponse = $this->getSampleResultResponse('Error');
        
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
            $this->createJsonResponse($errorResponse)
        ]);

        // Submit a request
        $request = ImageRequestBuilder::create()
            ->withPrompt('Test prompt')
            ->buildFlux1Pro();
        
        $submissionResponse = $client->imageGeneration()->flux1Pro($request);
        
        // Check the error result
        $result = $client->utility()->getResult($submissionResponse->id);
        
        $this->assertSame(ResultStatus::ERROR, $result->status);
        $this->assertTrue($result->isComplete());
        $this->assertTrue($result->isFailed());
        $this->assertFalse($result->isSuccessful());
        $this->assertFalse($result->isInProgress());
    }

    public function test_content_moderated_workflow(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $moderatedResponse = $this->getSampleResultResponse('Content Moderated');
        
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
            $this->createJsonResponse($moderatedResponse)
        ]);

        $request = ImageRequestBuilder::create()
            ->withPrompt('Some inappropriate content')
            ->withSafetyTolerance(0) // Strictest setting
            ->buildFlux1Pro();
        
        $submissionResponse = $client->imageGeneration()->flux1Pro($request);
        $result = $client->utility()->getResult($submissionResponse->id);
        
        $this->assertSame(ResultStatus::CONTENT_MODERATED, $result->status);
        $this->assertTrue($result->isComplete());
        $this->assertTrue($result->isFailed());
        $this->assertFalse($result->isSuccessful());
    }

    public function test_finetune_workflow(): void
    {
        $finetunesListResponse = ['finetunes' => ['ft_abc123', 'ft_def456']];
        $finetuneDetailsResponse = [
            'finetune_details' => [
                'id' => 'ft_abc123',
                'status' => 'completed',
                'trigger_word' => 'MYSTYLE'
            ]
        ];
        $taskResponse = $this->getSampleTaskResponse();
        $completedResponse = $this->getSampleResultResponse('Ready', 'https://example.com/finetuned.jpg');
        
        $client = $this->createMockClient([
            $this->createJsonResponse($finetunesListResponse),   // List finetunes
            $this->createJsonResponse($finetuneDetailsResponse), // Get details
            $this->createJsonResponse($taskResponse),            // Generate with finetune
            $this->createJsonResponse($completedResponse)        // Get result
        ]);

        // Step 1: List existing finetunes
        $finetunes = $client->finetune()->listMyFinetunes();
        $this->assertArrayHasKey('finetunes', $finetunes);
        $this->assertCount(2, $finetunes['finetunes']);

        // Step 2: Get details about a finetune
        $details = $client->finetune()->getDetails('ft_abc123');
        $this->assertArrayHasKey('finetune_details', $details);

        // Step 3: Generate with the finetune
        $generateResponse = $client->finetune()->generateWithFinetunedPro([
            'finetune_id' => 'ft_abc123',
            'prompt' => 'MYSTYLE a beautiful portrait',
            'finetune_strength' => 1.2,
            'steps' => 40,
            'width' => 1024,
            'height' => 1024
        ]);

        $this->assertInstanceOf(ImageGenerationResponse::class, $generateResponse);

        // Step 4: Get the final result
        $result = $client->utility()->getResult($generateResponse->id);
        $this->assertTrue($result->isSuccessful());
    }

    public function test_batch_processing_workflow(): void
    {
        // Create multiple task responses for batch processing
        $taskResponses = [
            $this->getSampleTaskResponse(),
            $this->getSampleTaskResponse(),
            $this->getSampleTaskResponse()
        ];
        
        $completedResponses = [
            $this->getSampleResultResponse('Ready', 'https://example.com/image1.jpg'),
            $this->getSampleResultResponse('Ready', 'https://example.com/image2.jpg'),
            $this->getSampleResultResponse('Ready', 'https://example.com/image3.jpg')
        ];

        $mockResponses = array_merge(
            array_map(fn($task) => $this->createJsonResponse($task), $taskResponses),
            array_map(fn($result) => $this->createJsonResponse($result), $completedResponses)
        );

        $client = $this->createMockClient($mockResponses);

        $prompts = [
            'A serene mountain lake at sunset',
            'A bustling cyberpunk street scene',
            'A magical forest with glowing mushrooms'
        ];

        $submittedTasks = [];

        // Submit multiple generation requests
        foreach ($prompts as $prompt) {
            $request = ImageRequestBuilder::create()
                ->withPrompt($prompt)
                ->withDimensions(512, 512)
                ->withSteps(30)
                ->buildFlux1Pro();
            
            $response = $client->imageGeneration()->flux1Pro($request);
            $submittedTasks[] = $response;
        }

        $this->assertCount(3, $submittedTasks);

        // Check all results
        foreach ($submittedTasks as $index => $task) {
            $result = $client->utility()->getResult($task->id);
            $this->assertTrue($result->isSuccessful());
            $this->assertStringContains("image" . ($index + 1), $result->getResultAsString());
        }
    }
}