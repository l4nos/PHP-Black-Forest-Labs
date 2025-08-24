<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Unit\Services;

use Lanos\PHPBFL\DTOs\Requests\Flux1ProRequest;
use Lanos\PHPBFL\DTOs\Responses\ImageGenerationResponse;
use Lanos\PHPBFL\Exceptions\FluxApiException;
use Lanos\PHPBFL\Services\ImageGenerationService;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * @covers \Lanos\PHPBFL\Services\ImageGenerationService
 */
class ImageGenerationServiceTest extends TestCase
{
    private ImageGenerationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $client = $this->createMockClient();
        $this->service = new ImageGenerationService($client);
    }

    public function test_flux1_pro_with_valid_request(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
        ]);
        $service = new ImageGenerationService($client);

        $request = new Flux1ProRequest(prompt: 'A beautiful landscape');
        $response = $service->flux1Pro($request);

        $this->assertInstanceOf(ImageGenerationResponse::class, $response);
        $this->assertSame($taskResponse['id'], $response->id);
        $this->assertSame($taskResponse['polling_url'], $response->pollingUrl);
    }

    public function test_flux1_pro_validates_request(): void
    {
        $request = new Flux1ProRequest(width: 100); // Invalid width (not multiple of 32)

        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Invalid request parameters');

        $this->service->flux1Pro($request);
    }

    public function test_flux1_dev_with_parameters(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
        ]);
        $service = new ImageGenerationService($client);

        $params = [
            'prompt' => 'A cyberpunk city',
            'width' => 1024,
            'height' => 768,
            'steps' => 28,
        ];

        $response = $service->flux1Dev($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $response);
        $this->assertSame($taskResponse['id'], $response->id);
    }

    public function test_flux11_pro_with_parameters(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
        ]);
        $service = new ImageGenerationService($client);

        $params = [
            'prompt' => 'A magical forest',
            'width' => 1024,
            'height' => 1024,
        ];

        $response = $service->flux11Pro($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $response);
        $this->assertSame($taskResponse['id'], $response->id);
    }

    public function test_flux11_pro_ultra_with_parameters(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
        ]);
        $service = new ImageGenerationService($client);

        $params = [
            'prompt' => 'An abstract artwork',
            'aspect_ratio' => '16:9',
            'raw' => true,
        ];

        $response = $service->flux11ProUltra($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $response);
    }

    public function test_flux_kontext_pro_with_parameters(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
        ]);
        $service = new ImageGenerationService($client);

        $params = [
            'prompt' => 'Transform this image',
            'input_image' => base64_encode('fake-image-data'),
            'aspect_ratio' => '1:1',
        ];

        $response = $service->fluxKontextPro($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $response);
    }

    public function test_flux_kontext_max_with_parameters(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
        ]);
        $service = new ImageGenerationService($client);

        $params = [
            'prompt' => 'Edit this image creatively',
            'input_image' => base64_encode('fake-image-data'),
        ];

        $response = $service->fluxKontextMax($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $response);
    }

    public function test_flux1_fill_requires_image_parameter(): void
    {
        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Image parameter is required for fill operations');

        $this->service->flux1Fill(['prompt' => 'Fill this']);
    }

    public function test_flux1_fill_with_valid_parameters(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
        ]);
        $service = new ImageGenerationService($client);

        $params = [
            'image' => base64_encode('fake-image-data'),
            'mask' => base64_encode('fake-mask-data'),
            'prompt' => 'Fill with flowers',
        ];

        $response = $service->flux1Fill($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $response);
    }

    public function test_flux1_expand_requires_image_parameter(): void
    {
        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Image parameter is required for expand operations');

        $this->service->flux1Expand(['top' => 100]);
    }

    public function test_flux1_expand_with_valid_parameters(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
        ]);
        $service = new ImageGenerationService($client);

        $params = [
            'image' => base64_encode('fake-image-data'),
            'top' => 200,
            'bottom' => 100,
            'left' => 150,
            'right' => 150,
        ];

        $response = $service->flux1Expand($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $response);
    }

    public function test_flux1_canny_requires_prompt_parameter(): void
    {
        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Prompt parameter is required for Canny operations');

        $this->service->flux1Canny(['control_image' => 'data']);
    }

    public function test_flux1_canny_with_valid_parameters(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
        ]);
        $service = new ImageGenerationService($client);

        $params = [
            'prompt' => 'A realistic portrait',
            'control_image' => base64_encode('fake-edge-data'),
            'canny_low_threshold' => 50,
            'canny_high_threshold' => 200,
        ];

        $response = $service->flux1Canny($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $response);
    }

    public function test_flux1_depth_requires_prompt_parameter(): void
    {
        $this->expectException(FluxApiException::class);
        $this->expectExceptionMessage('Prompt parameter is required for Depth operations');

        $this->service->flux1Depth(['control_image' => 'data']);
    }

    public function test_flux1_depth_with_valid_parameters(): void
    {
        $taskResponse = $this->getSampleTaskResponse();
        $client = $this->createMockClient([
            $this->createJsonResponse($taskResponse),
        ]);
        $service = new ImageGenerationService($client);

        $params = [
            'prompt' => 'A landscape with depth',
            'control_image' => base64_encode('fake-depth-data'),
            'guidance' => 15.0,
        ];

        $response = $service->flux1Depth($params);

        $this->assertInstanceOf(ImageGenerationResponse::class, $response);
    }
}
