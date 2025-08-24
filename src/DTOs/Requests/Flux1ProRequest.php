<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\DTOs\Requests;

use Lanos\PHPBFL\Enums\OutputFormat;

/**
 * Request data for FLUX1 Pro image generation.
 *
 * @author Lanos <https://github.com/l4nos>
 */
class Flux1ProRequest
{
    /**
     * @param string|null $prompt Text prompt for image generation
     * @param string|null $imagePrompt Optional image prompt encoded in base64
     * @param int $width Width of the output image (multiple of 32)
     * @param int $height Height of the output image (multiple of 32)
     * @param int $steps Number of denoising steps
     * @param bool $promptUpsampling Whether to upsample the prompt
     * @param int|null $seed Seed for reproducibility
     * @param float $guidance Classifier guidance scale
     * @param float $interval Interval for progressive updates
     * @param int $safetyTolerance Tolerance level for input and output moderation
     * @param OutputFormat $outputFormat Output image format
     * @param string|null $webhookUrl URL to receive webhook notifications
     * @param string|null $webhookSecret Optional secret used to verify webhook signature
     */
    public function __construct(
        public ?string $prompt = null,
        public ?string $imagePrompt = null,
        public int $width = 1024,
        public int $height = 768,
        public int $steps = 40,
        public bool $promptUpsampling = false,
        public ?int $seed = null,
        public float $guidance = 2.5,
        public float $interval = 2.0,
        public int $safetyTolerance = 2,
        public OutputFormat $outputFormat = OutputFormat::JPEG,
        public ?string $webhookUrl = null,
        public ?string $webhookSecret = null,
    ) {
    }

    /**
     * Create instance from array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            prompt: isset($data['prompt']) && is_string($data['prompt']) ? $data['prompt'] : null,
            imagePrompt: isset($data['image_prompt']) && is_string($data['image_prompt']) ? $data['image_prompt'] : null,
            width: isset($data['width']) && is_numeric($data['width']) ? (int) $data['width'] : 1024,
            height: isset($data['height']) && is_numeric($data['height']) ? (int) $data['height'] : 768,
            steps: isset($data['steps']) && is_numeric($data['steps']) ? (int) $data['steps'] : 40,
            promptUpsampling: (bool) ($data['prompt_upsampling'] ?? false),
            seed: isset($data['seed']) && is_numeric($data['seed']) ? (int) $data['seed'] : null,
            guidance: isset($data['guidance']) && is_numeric($data['guidance']) ? (float) $data['guidance'] : 2.5,
            interval: isset($data['interval']) && is_numeric($data['interval']) ? (float) $data['interval'] : 2.0,
            safetyTolerance: isset($data['safety_tolerance']) && is_numeric($data['safety_tolerance']) ? (int) $data['safety_tolerance'] : 2,
            outputFormat: isset($data['output_format']) && is_string($data['output_format']) ? OutputFormat::from($data['output_format']) : OutputFormat::JPEG,
            webhookUrl: isset($data['webhook_url']) && is_string($data['webhook_url']) ? $data['webhook_url'] : null,
            webhookSecret: isset($data['webhook_secret']) && is_string($data['webhook_secret']) ? $data['webhook_secret'] : null,
        );
    }

    /**
     * Convert to API request array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
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

        // Add optional fields only if they have values
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

    /**
     * Validate the request parameters.
     *
     * @return array<string> Array of validation errors
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->width < 32 || $this->width % 32 !== 0) {
            $errors[] = 'Width must be a multiple of 32 and at least 32 pixels';
        }

        if ($this->height < 32 || $this->height % 32 !== 0) {
            $errors[] = 'Height must be a multiple of 32 and at least 32 pixels';
        }

        if ($this->steps < 1 || $this->steps > 100) {
            $errors[] = 'Steps must be between 1 and 100';
        }

        if ($this->guidance < 1.5 || $this->guidance > 5.0) {
            $errors[] = 'Guidance must be between 1.5 and 5.0';
        }

        if ($this->interval < 1.0 || $this->interval > 4.0) {
            $errors[] = 'Interval must be between 1.0 and 4.0';
        }

        if ($this->safetyTolerance < 0 || $this->safetyTolerance > 6) {
            $errors[] = 'Safety tolerance must be between 0 and 6';
        }

        if ($this->prompt === null && $this->imagePrompt === null) {
            $errors[] = 'Either prompt or image_prompt must be provided';
        }

        return $errors;
    }
}
