<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Unit\DTOs;

use Lanos\PHPBFL\DTOs\Requests\Flux1ProRequest;
use Lanos\PHPBFL\Enums\OutputFormat;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * @covers \Lanos\PHPBFL\DTOs\Requests\Flux1ProRequest
 */
class Flux1ProRequestTest extends TestCase
{
    public function test_can_be_instantiated_with_defaults(): void
    {
        $request = new Flux1ProRequest();
        
        $this->assertNull($request->prompt);
        $this->assertNull($request->imagePrompt);
        $this->assertSame(1024, $request->width);
        $this->assertSame(768, $request->height);
        $this->assertSame(40, $request->steps);
        $this->assertFalse($request->promptUpsampling);
        $this->assertNull($request->seed);
        $this->assertSame(2.5, $request->guidance);
        $this->assertSame(2.0, $request->interval);
        $this->assertSame(2, $request->safetyTolerance);
        $this->assertSame(OutputFormat::JPEG, $request->outputFormat);
    }

    public function test_can_be_instantiated_with_custom_values(): void
    {
        $request = new Flux1ProRequest(
            prompt: 'A beautiful sunset',
            imagePrompt: 'base64-image',
            width: 512,
            height: 512,
            steps: 50,
            promptUpsampling: true,
            seed: 12345,
            guidance: 3.0,
            interval: 1.5,
            safetyTolerance: 1,
            outputFormat: OutputFormat::PNG,
            webhookUrl: 'https://example.com/webhook',
            webhookSecret: 'secret123'
        );
        
        $this->assertSame('A beautiful sunset', $request->prompt);
        $this->assertSame('base64-image', $request->imagePrompt);
        $this->assertSame(512, $request->width);
        $this->assertSame(512, $request->height);
        $this->assertSame(50, $request->steps);
        $this->assertTrue($request->promptUpsampling);
        $this->assertSame(12345, $request->seed);
        $this->assertSame(3.0, $request->guidance);
        $this->assertSame(1.5, $request->interval);
        $this->assertSame(1, $request->safetyTolerance);
        $this->assertSame(OutputFormat::PNG, $request->outputFormat);
        $this->assertSame('https://example.com/webhook', $request->webhookUrl);
        $this->assertSame('secret123', $request->webhookSecret);
    }

    public function test_from_array_creates_correct_instance(): void
    {
        $data = [
            'prompt' => 'Test prompt',
            'image_prompt' => 'base64-data',
            'width' => 512,
            'height' => 768,
            'steps' => 30,
            'prompt_upsampling' => true,
            'seed' => 54321,
            'guidance' => 4.0,
            'interval' => 3.0,
            'safety_tolerance' => 3,
            'output_format' => 'png',
            'webhook_url' => 'https://test.com',
            'webhook_secret' => 'test-secret'
        ];
        
        $request = Flux1ProRequest::fromArray($data);
        
        $this->assertSame('Test prompt', $request->prompt);
        $this->assertSame('base64-data', $request->imagePrompt);
        $this->assertSame(512, $request->width);
        $this->assertSame(768, $request->height);
        $this->assertSame(30, $request->steps);
        $this->assertTrue($request->promptUpsampling);
        $this->assertSame(54321, $request->seed);
        $this->assertSame(4.0, $request->guidance);
        $this->assertSame(3.0, $request->interval);
        $this->assertSame(3, $request->safetyTolerance);
        $this->assertSame(OutputFormat::PNG, $request->outputFormat);
        $this->assertSame('https://test.com', $request->webhookUrl);
        $this->assertSame('test-secret', $request->webhookSecret);
    }

    public function test_from_array_uses_defaults_for_missing_values(): void
    {
        $data = ['prompt' => 'Minimal prompt'];
        
        $request = Flux1ProRequest::fromArray($data);
        
        $this->assertSame('Minimal prompt', $request->prompt);
        $this->assertSame(1024, $request->width);
        $this->assertSame(768, $request->height);
        $this->assertSame(40, $request->steps);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $request = new Flux1ProRequest(
            prompt: 'Test prompt',
            width: 512,
            height: 512,
            steps: 25
        );
        
        $array = $request->toArray();
        
        $this->assertArrayHasKey('prompt', $array);
        $this->assertArrayHasKey('width', $array);
        $this->assertArrayHasKey('height', $array);
        $this->assertArrayHasKey('steps', $array);
        $this->assertSame('Test prompt', $array['prompt']);
        $this->assertSame(512, $array['width']);
        $this->assertSame(512, $array['height']);
        $this->assertSame(25, $array['steps']);
    }

    public function test_to_array_excludes_null_optional_values(): void
    {
        $request = new Flux1ProRequest();
        
        $array = $request->toArray();
        
        $this->assertArrayNotHasKey('prompt', $array);
        $this->assertArrayNotHasKey('image_prompt', $array);
        $this->assertArrayNotHasKey('seed', $array);
        $this->assertArrayNotHasKey('webhook_url', $array);
        $this->assertArrayNotHasKey('webhook_secret', $array);
    }

    public function test_validate_returns_empty_array_for_valid_request(): void
    {
        $request = new Flux1ProRequest(
            prompt: 'Valid prompt',
            width: 1024,
            height: 768,
            steps: 50,
            guidance: 3.0,
            interval: 2.0,
            safetyTolerance: 2
        );
        
        $errors = $request->validate();
        
        $this->assertEmpty($errors);
    }

    public function test_validate_returns_error_for_invalid_width(): void
    {
        $request = new Flux1ProRequest(width: 100); // Not multiple of 32
        
        $errors = $request->validate();
        
        $this->assertContains('Width must be a multiple of 32 and at least 32 pixels', $errors);
    }

    public function test_validate_returns_error_for_invalid_height(): void
    {
        $request = new Flux1ProRequest(height: 50); // Not multiple of 32
        
        $errors = $request->validate();
        
        $this->assertContains('Height must be a multiple of 32 and at least 32 pixels', $errors);
    }

    public function test_validate_returns_error_for_invalid_steps(): void
    {
        $request = new Flux1ProRequest(steps: 0);
        
        $errors = $request->validate();
        
        $this->assertContains('Steps must be between 1 and 100', $errors);
    }

    public function test_validate_returns_error_for_invalid_guidance(): void
    {
        $request = new Flux1ProRequest(guidance: 1.0);
        
        $errors = $request->validate();
        
        $this->assertContains('Guidance must be between 1.5 and 5.0', $errors);
    }

    public function test_validate_returns_error_for_invalid_interval(): void
    {
        $request = new Flux1ProRequest(interval: 0.5);
        
        $errors = $request->validate();
        
        $this->assertContains('Interval must be between 1.0 and 4.0', $errors);
    }

    public function test_validate_returns_error_for_invalid_safety_tolerance(): void
    {
        $request = new Flux1ProRequest(safetyTolerance: 10);
        
        $errors = $request->validate();
        
        $this->assertContains('Safety tolerance must be between 0 and 6', $errors);
    }

    public function test_validate_returns_error_when_no_prompt_or_image_prompt(): void
    {
        $request = new Flux1ProRequest();
        
        $errors = $request->validate();
        
        $this->assertContains('Either prompt or image_prompt must be provided', $errors);
    }

    public function test_validate_returns_multiple_errors(): void
    {
        $request = new Flux1ProRequest(
            width: 100,    // Invalid
            height: 50,    // Invalid
            steps: 0,      // Invalid
            guidance: 1.0  // Invalid
        );
        
        $errors = $request->validate();
        
        $this->assertCount(5, $errors); // 4 parameter errors + missing prompt error
    }

    public function test_validate_passes_with_image_prompt_only(): void
    {
        $request = new Flux1ProRequest(
            imagePrompt: 'base64-image-data',
            width: 1024,
            height: 768
        );
        
        $errors = $request->validate();
        
        $this->assertEmpty($errors);
    }
}