<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Builders;

use Lanos\PHPBFL\Enums\OutputFormat;
use Lanos\PHPBFL\DTOs\Requests\Flux1ProRequest;

/**
 * Fluent builder for constructing image generation requests
 *
 * @package Lanos\PHPBFL\Builders
 * @author Lanos <https://github.com/l4nos>
 */
class ImageRequestBuilder
{
    private ?string $prompt = null;
    private ?string $imagePrompt = null;
    private int $width = 1024;
    private int $height = 768;
    private int $steps = 40;
    private bool $promptUpsampling = false;
    private ?int $seed = null;
    private float $guidance = 2.5;
    private float $interval = 2.0;
    private int $safetyTolerance = 2;
    private OutputFormat $outputFormat = OutputFormat::JPEG;
    private ?string $webhookUrl = null;
    private ?string $webhookSecret = null;

    /**
     * Create a new builder instance
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set the text prompt for image generation
     */
    public function withPrompt(string $prompt): self
    {
        $this->prompt = $prompt;
        return $this;
    }

    /**
     * Set an optional image prompt encoded in base64
     */
    public function withImagePrompt(string $imagePrompt): self
    {
        $this->imagePrompt = $imagePrompt;
        return $this;
    }

    /**
     * Set the dimensions of the output image
     */
    public function withDimensions(int $width, int $height): self
    {
        $this->width = $width;
        $this->height = $height;
        return $this;
    }

    /**
     * Set common aspect ratios with automatic dimension calculation
     */
    public function withAspectRatio(string $ratio, int $baseSize = 1024): self
    {
        [$widthRatio, $heightRatio] = explode(':', $ratio);
        
        $widthRatio = (int) $widthRatio;
        $heightRatio = (int) $heightRatio;
        
        // Calculate dimensions that maintain aspect ratio and are multiples of 32
        if ($widthRatio >= $heightRatio) {
            $this->width = $baseSize;
            $calculatedHeight = ($baseSize * $heightRatio) / $widthRatio;
            $this->height = (int) (round($calculatedHeight / 32) * 32);
        } else {
            $this->height = $baseSize;
            $calculatedWidth = ($baseSize * $widthRatio) / $heightRatio;
            $this->width = (int) (round($calculatedWidth / 32) * 32);
        }
        
        return $this;
    }

    /**
     * Set the number of denoising steps
     */
    public function withSteps(int $steps): self
    {
        $this->steps = $steps;
        return $this;
    }

    /**
     * Enable or disable prompt upsampling
     */
    public function withPromptUpsampling(bool $enabled = true): self
    {
        $this->promptUpsampling = $enabled;
        return $this;
    }

    /**
     * Set a seed for reproducible results
     */
    public function withSeed(int $seed): self
    {
        $this->seed = $seed;
        return $this;
    }

    /**
     * Set a random seed for varied results
     */
    public function withRandomSeed(): self
    {
        $this->seed = random_int(0, PHP_INT_MAX);
        return $this;
    }

    /**
     * Set the classifier guidance scale
     */
    public function withGuidance(float $guidance): self
    {
        $this->guidance = $guidance;
        return $this;
    }

    /**
     * Set the interval for progressive updates
     */
    public function withInterval(float $interval): self
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * Set the safety tolerance level (0 = strict, 6 = lenient)
     */
    public function withSafetyTolerance(int $tolerance): self
    {
        $this->safetyTolerance = $tolerance;
        return $this;
    }

    /**
     * Set the output format
     */
    public function withOutputFormat(OutputFormat $format): self
    {
        $this->outputFormat = $format;
        return $this;
    }

    /**
     * Set output format to JPEG
     */
    public function asJpeg(): self
    {
        $this->outputFormat = OutputFormat::JPEG;
        return $this;
    }

    /**
     * Set output format to PNG
     */
    public function asPng(): self
    {
        $this->outputFormat = OutputFormat::PNG;
        return $this;
    }

    /**
     * Set webhook configuration
     */
    public function withWebhook(string $url, ?string $secret = null): self
    {
        $this->webhookUrl = $url;
        $this->webhookSecret = $secret;
        return $this;
    }

    /**
     * Build the Flux1ProRequest object
     */
    public function buildFlux1Pro(): Flux1ProRequest
    {
        return new Flux1ProRequest(
            prompt: $this->prompt,
            imagePrompt: $this->imagePrompt,
            width: $this->width,
            height: $this->height,
            steps: $this->steps,
            promptUpsampling: $this->promptUpsampling,
            seed: $this->seed,
            guidance: $this->guidance,
            interval: $this->interval,
            safetyTolerance: $this->safetyTolerance,
            outputFormat: $this->outputFormat,
            webhookUrl: $this->webhookUrl,
            webhookSecret: $this->webhookSecret,
        );
    }

    /**
     * Build a generic array for other model types
     *
     * @return array<string, mixed>
     */
    public function buildArray(): array
    {
        $data = [
            'width' => $this->width,
            'height' => $this->height,
            'steps' => $this->steps,
            'prompt_upsampling' => $this->promptUpsampling,
            'guidance' => $this->guidance,
            'interval' => $this->interval,
            'safety_tolerance' => $this->safetyTolerance,
            'output_format' => $this->outputFormat->value,
        ];

        if ($this->prompt !== null) {
            $data['prompt'] = $this->prompt;
        }

        if ($this->imagePrompt !== null) {
            $data['image_prompt'] = $this->imagePrompt;
        }

        if ($this->seed !== null) {
            $data['seed'] = $this->seed;
        }

        if ($this->webhookUrl !== null) {
            $data['webhook_url'] = $this->webhookUrl;
        }

        if ($this->webhookSecret !== null) {
            $data['webhook_secret'] = $this->webhookSecret;
        }

        return $data;
    }
}