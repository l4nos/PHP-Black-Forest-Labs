<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Unit\Builders;

use Lanos\PHPBFL\Builders\ImageRequestBuilder;
use Lanos\PHPBFL\DTOs\Requests\Flux1ProRequest;
use Lanos\PHPBFL\Enums\OutputFormat;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * @covers \Lanos\PHPBFL\Builders\ImageRequestBuilder
 */
class ImageRequestBuilderTest extends TestCase
{
    public function test_can_create_builder_instance(): void
    {
        $builder = ImageRequestBuilder::create();
        
        $this->assertInstanceOf(ImageRequestBuilder::class, $builder);
    }

    public function test_can_set_prompt(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withPrompt('A beautiful landscape');
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame('A beautiful landscape', $request->prompt);
    }

    public function test_can_set_image_prompt(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withImagePrompt('base64-encoded-image');
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame('base64-encoded-image', $request->imagePrompt);
    }

    public function test_can_set_dimensions(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withDimensions(512, 768);
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame(512, $request->width);
        $this->assertSame(768, $request->height);
    }

    public function test_can_set_aspect_ratio_16_9(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withAspectRatio('16:9', 1024);
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame(1024, $request->width);
        $this->assertSame(576, $request->height); // 1024 * 9/16 rounded to nearest 32
    }

    public function test_can_set_aspect_ratio_1_1(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withAspectRatio('1:1', 1024);
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame(1024, $request->width);
        $this->assertSame(1024, $request->height);
    }

    public function test_can_set_aspect_ratio_portrait(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withAspectRatio('9:16', 768);
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame(768, $request->height);
        $this->assertSame(448, $request->width); // 768 * 9/16 = 432, rounded to nearest 32 = 448
    }

    public function test_can_set_steps(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withSteps(50);
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame(50, $request->steps);
    }

    public function test_can_enable_prompt_upsampling(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withPromptUpsampling();
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertTrue($request->promptUpsampling);
    }

    public function test_can_disable_prompt_upsampling(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withPromptUpsampling(false);
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertFalse($request->promptUpsampling);
    }

    public function test_can_set_seed(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withSeed(12345);
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame(12345, $request->seed);
    }

    public function test_can_set_random_seed(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withRandomSeed();
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertIsInt($request->seed);
        $this->assertGreaterThan(0, $request->seed);
    }

    public function test_can_set_guidance(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withGuidance(3.5);
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame(3.5, $request->guidance);
    }

    public function test_can_set_interval(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withInterval(2.5);
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame(2.5, $request->interval);
    }

    public function test_can_set_safety_tolerance(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withSafetyTolerance(4);
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame(4, $request->safetyTolerance);
    }

    public function test_can_set_output_format(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withOutputFormat(OutputFormat::PNG);
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame(OutputFormat::PNG, $request->outputFormat);
    }

    public function test_can_set_jpeg_format(): void
    {
        $builder = ImageRequestBuilder::create()
            ->asJpeg();
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame(OutputFormat::JPEG, $request->outputFormat);
    }

    public function test_can_set_png_format(): void
    {
        $builder = ImageRequestBuilder::create()
            ->asPng();
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame(OutputFormat::PNG, $request->outputFormat);
    }

    public function test_can_set_webhook(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withWebhook('https://example.com/webhook', 'secret123');
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame('https://example.com/webhook', $request->webhookUrl);
        $this->assertSame('secret123', $request->webhookSecret);
    }

    public function test_can_set_webhook_without_secret(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withWebhook('https://example.com/webhook');
        
        $request = $builder->buildFlux1Pro();
        
        $this->assertSame('https://example.com/webhook', $request->webhookUrl);
        $this->assertNull($request->webhookSecret);
    }

    public function test_build_array_returns_correct_structure(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withPrompt('Test prompt')
            ->withDimensions(1024, 768)
            ->withSteps(30);
        
        $array = $builder->buildArray();
        
        $this->assertArrayHasKey('prompt', $array);
        $this->assertArrayHasKey('width', $array);
        $this->assertArrayHasKey('height', $array);
        $this->assertArrayHasKey('steps', $array);
        $this->assertSame('Test prompt', $array['prompt']);
        $this->assertSame(1024, $array['width']);
        $this->assertSame(768, $array['height']);
        $this->assertSame(30, $array['steps']);
    }

    public function test_build_array_excludes_null_optional_values(): void
    {
        $builder = ImageRequestBuilder::create()
            ->withDimensions(512, 512);
        
        $array = $builder->buildArray();
        
        $this->assertArrayNotHasKey('prompt', $array);
        $this->assertArrayNotHasKey('image_prompt', $array);
        $this->assertArrayNotHasKey('seed', $array);
        $this->assertArrayNotHasKey('webhook_url', $array);
        $this->assertArrayNotHasKey('webhook_secret', $array);
    }

    public function test_fluent_interface_chaining(): void
    {
        $request = ImageRequestBuilder::create()
            ->withPrompt('A magical castle')
            ->withDimensions(1024, 1024)
            ->withSteps(50)
            ->withGuidance(3.0)
            ->withSafetyTolerance(2)
            ->asPng()
            ->withRandomSeed()
            ->buildFlux1Pro();
        
        $this->assertInstanceOf(Flux1ProRequest::class, $request);
        $this->assertSame('A magical castle', $request->prompt);
        $this->assertSame(1024, $request->width);
        $this->assertSame(1024, $request->height);
        $this->assertSame(50, $request->steps);
        $this->assertSame(3.0, $request->guidance);
        $this->assertSame(2, $request->safetyTolerance);
        $this->assertSame(OutputFormat::PNG, $request->outputFormat);
        $this->assertIsInt($request->seed);
    }

    public function test_uses_default_values(): void
    {
        $request = ImageRequestBuilder::create()->buildFlux1Pro();
        
        $this->assertSame(1024, $request->width);
        $this->assertSame(768, $request->height);
        $this->assertSame(40, $request->steps);
        $this->assertSame(2.5, $request->guidance);
        $this->assertSame(2.0, $request->interval);
        $this->assertSame(2, $request->safetyTolerance);
        $this->assertSame(OutputFormat::JPEG, $request->outputFormat);
        $this->assertFalse($request->promptUpsampling);
    }
}