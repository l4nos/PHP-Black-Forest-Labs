# PHP Black Forest Labs SDK

A professional PHP SDK for [Black Forest Labs' FLUX API](https://api.bfl.ai/), providing easy integration for AI-powered image generation and fine-tuning services.

[![Latest Version](https://img.shields.io/packagist/v/lanos/php-bfl.svg?style=flat-square)](https://packagist.org/packages/lanos/php-bfl)
[![PHP Version](https://img.shields.io/packagist/php-v/lanos/php-bfl.svg?style=flat-square)](https://packagist.org/packages/lanos/php-bfl)
[![License](https://img.shields.io/packagist/l/lanos/php-bfl.svg?style=flat-square)](https://github.com/l4nos/PHP-Black-Forest-Labs/blob/main/LICENSE.md)
[![Tests](https://github.com/l4nos/PHP-Black-Forest-Labs/actions/workflows/tests.yml/badge.svg)](https://github.com/l4nos/PHP-Black-Forest-Labs/actions)
[![Coverage Status](https://coveralls.io/repos/github/l4nos/PHP-Black-Forest-Labs/badge.svg)](https://coveralls.io/github/l4nos/PHP-Black-Forest-Labs)

## Features

- **Complete API Coverage**: All FLUX API endpoints including image generation, fine-tuning, and utilities
- **Type-Safe**: Full PHP 8.1+ type hints and strict types for better IDE support and error detection
- **Fluent API**: Intuitive builder pattern for constructing complex requests
- **Error Handling**: Comprehensive exception handling with detailed error messages
- **Async Support**: Built-in polling mechanisms for handling async image generation tasks
- **PSR Standards**: Follows PSR-4 autoloading and PSR-12 coding standards
- **Well Documented**: Extensive inline documentation and examples

## Installation

Install via Composer:

```bash
composer require lanos/php-bfl
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use Lanos\PHPBFL\FluxClient;
use Lanos\PHPBFL\Builders\ImageRequestBuilder;

// Initialize the client with your API key
$client = new FluxClient('your-api-key-here');

// Generate an image with FLUX1 Pro
$request = ImageRequestBuilder::create()
    ->withPrompt('A majestic dragon soaring through clouds at sunset')
    ->withDimensions(1024, 768)
    ->asJpeg()
    ->buildFlux1Pro();

$response = $client->imageGeneration()->flux1Pro($request);

// Wait for the image to be generated
$result = $client->utility()->waitForCompletion($response->id);

if ($result->isSuccessful()) {
    echo "Image generated successfully: " . $result->getResultAsString();
} else {
    echo "Generation failed: " . $result->status->value;
}
```

## API Key Setup

1. Sign up at [Black Forest Labs](https://blackforestlabs.ai)
2. Get your API key from the dashboard
3. Set it as an environment variable or pass it directly to the client:

```php
// Option 1: Environment variable
$client = new FluxClient(getenv('BFL_API_KEY'));

// Option 2: Direct assignment
$client = new FluxClient('your-api-key-here');
```

## Core Services

The SDK is organized into three main services:

### Image Generation Service

Generate images using various FLUX models:

```php
// FLUX1 Pro - High quality, balanced performance
$request = ImageRequestBuilder::create()
    ->withPrompt('A cyberpunk cityscape at night')
    ->withAspectRatio('16:9')
    ->withSteps(50)
    ->buildFlux1Pro();

$response = $client->imageGeneration()->flux1Pro($request);

// FLUX1 Dev - Development model, faster generation
$response = $client->imageGeneration()->flux1Dev([
    'prompt' => 'A serene mountain landscape',
    'width' => 512,
    'height' => 512,
    'steps' => 28
]);

// FLUX 1.1 Pro Ultra - Highest quality
$response = $client->imageGeneration()->flux11ProUltra([
    'prompt' => 'An abstract artistic composition',
    'aspect_ratio' => '1:1',
    'raw' => false
]);
```

### Advanced Image Operations

```php
// Image Fill/Inpainting
$response = $client->imageGeneration()->flux1Fill([
    'image' => base64_encode(file_get_contents('input.jpg')),
    'mask' => base64_encode(file_get_contents('mask.png')),
    'prompt' => 'A beautiful garden'
]);

// Image Expansion
$response = $client->imageGeneration()->flux1Expand([
    'image' => base64_encode(file_get_contents('input.jpg')),
    'top' => 100,
    'bottom' => 100,
    'left' => 50,
    'right' => 50,
    'prompt' => 'Expand with matching scenery'
]);

// Canny Edge Control
$response = $client->imageGeneration()->flux1Canny([
    'prompt' => 'A realistic portrait',
    'control_image' => base64_encode(file_get_contents('edges.jpg')),
    'canny_low_threshold' => 50,
    'canny_high_threshold' => 200
]);
```

### Fine-tuning Service

Train custom models and use them for generation:

```php
// Create a fine-tune
$finetuneResponse = $client->finetune()->create([
    'file_data' => base64_encode(file_get_contents('training_data.zip')),
    'finetune_comment' => 'My custom style',
    'mode' => 'style',
    'trigger_word' => 'MYSTYLE',
    'iterations' => 500
]);

// List your fine-tunes
$finetunes = $client->finetune()->listMyFinetunes();
print_r($finetunes['finetunes']);

// Generate with a fine-tuned model
$response = $client->finetune()->generateWithFinetunedPro([
    'finetune_id' => 'your-finetune-id',
    'prompt' => 'MYSTYLE a beautiful landscape',
    'finetune_strength' => 1.2
]);

// Delete a fine-tune
$deleteResponse = $client->finetune()->delete('finetune-id-to-delete');
```

### Utility Service

Handle task polling and result retrieval:

```php
// Get task status
$result = $client->utility()->getResult('task-id');

// Poll with custom settings
$result = $client->utility()->pollResult(
    taskId: 'task-id',
    maxAttempts: 100,
    delaySeconds: 3
);

// Check if task is complete
$isComplete = $client->utility()->isTaskComplete('task-id');

// Get progress if available
$progress = $client->utility()->getProgress('task-id');
if ($progress !== null) {
    echo "Progress: {$progress}%";
}
```

## Request Builder

Use the fluent builder pattern for complex requests:

```php
$request = ImageRequestBuilder::create()
    ->withPrompt('A magical forest with glowing mushrooms')
    ->withImagePrompt(base64_encode(file_get_contents('reference.jpg')))
    ->withAspectRatio('4:3', 1024) // 4:3 ratio with base size 1024
    ->withSteps(75)
    ->withGuidance(3.5)
    ->withSafetyTolerance(1) // Stricter content filtering
    ->withRandomSeed() // Generate random seed
    ->asPng() // PNG output format
    ->withWebhook('https://your-site.com/webhook', 'secret-key')
    ->buildFlux1Pro();

$response = $client->imageGeneration()->flux1Pro($request);
```

## Error Handling

The SDK provides comprehensive error handling:

```php
use Lanos\PHPBFL\Exceptions\FluxApiException;
use Lanos\PHPBFL\Exceptions\AuthenticationException;

try {
    $response = $client->imageGeneration()->flux1Pro($request);
    $result = $client->utility()->waitForCompletion($response->id);
    
    if ($result->isFailed()) {
        echo "Generation failed: " . $result->status->value;
    }
    
} catch (AuthenticationException $e) {
    echo "Authentication failed: " . $e->getMessage();
} catch (FluxApiException $e) {
    if ($e->isClientError()) {
        echo "Client error: " . $e->getFriendlyMessage();
    } elseif ($e->isServerError()) {
        echo "Server error: " . $e->getFriendlyMessage();
    } else {
        echo "API error: " . $e->getMessage();
    }
}
```

## Response Objects

All responses are returned as typed objects:

```php
$result = $client->utility()->getResult('task-id');

// Check status
if ($result->isComplete()) {
    echo "Task completed!";
}

if ($result->isSuccessful()) {
    // Get the result
    $imageUrl = $result->getResultAsString();
    $imageData = $result->getResultAsArray();
}

if ($result->isInProgress()) {
    $progress = $result->getProgressPercentage();
    echo "Progress: {$progress}%";
}

// Access raw properties
echo "Task ID: " . $result->id;
echo "Status: " . $result->status->value;
```

## Configuration Options

Customize the HTTP client:

```php
$client = new FluxClient('your-api-key', [
    'timeout' => 60,
    'connect_timeout' => 10,
    'headers' => [
        'User-Agent' => 'My App/1.0'
    ],
    'proxy' => 'http://proxy.example.com:8080'
]);
```

## Best Practices

### 1. Always Handle Async Operations

```php
// ✅ Good: Wait for completion
$response = $client->imageGeneration()->flux1Pro($request);
$result = $client->utility()->waitForCompletion($response->id);

// ❌ Bad: Don't forget to poll for results
$response = $client->imageGeneration()->flux1Pro($request);
// Result is not ready immediately!
```

### 2. Use Appropriate Safety Tolerance

```php
// For strict content filtering
$builder->withSafetyTolerance(0);

// For balanced filtering (default)
$builder->withSafetyTolerance(2);

// For lenient filtering
$builder->withSafetyTolerance(6);
```

### 3. Optimize Image Dimensions

```php
// ✅ Good: Use multiples of 32
$builder->withDimensions(1024, 768);

// ❌ Bad: Non-multiple of 32
$builder->withDimensions(1000, 750); // Will cause validation errors
```

### 4. Handle Rate Limits

```php
try {
    $response = $client->imageGeneration()->flux1Pro($request);
} catch (FluxApiException $e) {
    if ($e->getCode() === 429) {
        // Rate limited, wait and retry
        sleep(60);
        $response = $client->imageGeneration()->flux1Pro($request);
    }
}
```

## Examples

See the [`examples/`](examples/) directory for complete working examples:

- [`examples/basic_generation.php`](examples/basic_generation.php) - Basic image generation
- [`examples/advanced_generation.php`](examples/advanced_generation.php) - Advanced features
- [`examples/fine_tuning.php`](examples/fine_tuning.php) - Fine-tuning workflow
- [`examples/image_editing.php`](examples/image_editing.php) - Fill, expand, and control
- [`examples/polling_patterns.php`](examples/polling_patterns.php) - Different polling strategies

## Complete API Reference

This section provides comprehensive documentation for all available SDK methods and endpoints.

### FluxClient

The main client class providing access to all services.

#### Constructor
```php
new FluxClient(string $apiKey, array $options = [])
```

**Parameters:**
- `$apiKey` (string) - Your Black Forest Labs API key
- `$options` (array) - Optional Guzzle HTTP client configuration

#### Service Access Methods
- [`imageGeneration()`](#imagegeneration-service) - Access image generation services
- [`finetune()`](#finetune-service) - Access fine-tuning services
- [`utility()`](#utility-service) - Access utility services

---

### ImageGeneration Service

Access via `$client->imageGeneration()`. Handles all image generation operations.

#### Core Generation Methods

##### `flux1Pro(Flux1ProRequest $request): ImageGenerationResponse`
Generate images with FLUX1 Pro model - highest quality, balanced performance.

**Endpoint:** `POST /flux-pro`

**Parameters:** Use [`ImageRequestBuilder`](#imagerequestbuilder) to construct the request object.

**Example:**
```php
$request = ImageRequestBuilder::create()
    ->withPrompt('A majestic dragon')
    ->withDimensions(1024, 768)
    ->buildFlux1Pro();
    
$response = $client->imageGeneration()->flux1Pro($request);
```

##### `flux1Dev(array $params): ImageGenerationResponse`
Generate images with FLUX1 Dev model - development model, faster generation.

**Endpoint:** `POST /flux-dev`

**Parameters:**
- `prompt` (string) - Text description of desired image
- `width` (int, optional) - Image width (default: 1024)
- `height` (int, optional) - Image height (default: 768)
- `steps` (int, optional) - Denoising steps (default: 28)
- `guidance` (float, optional) - Guidance scale (default: 2.5)
- `seed` (int, optional) - Seed for reproducible results

**Example:**
```php
$response = $client->imageGeneration()->flux1Dev([
    'prompt' => 'A serene mountain landscape',
    'width' => 512,
    'height' => 512,
    'steps' => 28
]);
```

##### `flux11Pro(array $params): ImageGenerationResponse`
Generate images with FLUX 1.1 Pro model - improved quality and performance.

**Endpoint:** `POST /flux-pro-1.1`

**Parameters:**
- `prompt` (string) - Text description
- `width` (int, optional) - Image width
- `height` (int, optional) - Image height
- `steps` (int, optional) - Denoising steps
- `guidance` (float, optional) - Guidance scale
- `seed` (int, optional) - Seed value

##### `flux11ProUltra(array $params): ImageGenerationResponse`
Generate images with FLUX 1.1 Pro Ultra model - highest quality available.

**Endpoint:** `POST /flux-pro-1.1-ultra`

**Parameters:**
- `prompt` (string) - Text description
- `aspect_ratio` (string, optional) - Aspect ratio (e.g., '1:1', '16:9')
- `raw` (bool, optional) - Output raw format
- `safety_tolerance` (int, optional) - Safety filtering level (0-6)

**Example:**
```php
$response = $client->imageGeneration()->flux11ProUltra([
    'prompt' => 'An abstract artistic composition',
    'aspect_ratio' => '1:1',
    'raw' => false
]);
```

#### Contextual Generation Methods

##### `fluxKontextPro(array $params): ImageGenerationResponse`
Edit or create images with Flux Kontext Pro.

**Endpoint:** `POST /flux-kontext-pro`

##### `fluxKontextMax(array $params): ImageGenerationResponse`
Edit or create images with Flux Kontext Max.

**Endpoint:** `POST /flux-kontext-max`

#### Image Editing Methods

##### `flux1Fill(array $params): ImageGenerationResponse`
Fill/inpaint specific areas of an image using FLUX1 Fill Pro.

**Endpoint:** `POST /flux-pro-1.0-fill`

**Required Parameters:**
- `image` (string) - Base64 encoded input image
- `prompt` (string) - Description for the fill area

**Optional Parameters:**
- `mask` (string) - Base64 encoded mask image
- `width` (int) - Output width
- `height` (int) - Output height

**Example:**
```php
$response = $client->imageGeneration()->flux1Fill([
    'image' => base64_encode(file_get_contents('input.jpg')),
    'mask' => base64_encode(file_get_contents('mask.png')),
    'prompt' => 'A beautiful garden'
]);
```

##### `flux1Expand(array $params): ImageGenerationResponse`
Expand an image by adding pixels on any side.

**Endpoint:** `POST /flux-pro-1.0-expand`

**Required Parameters:**
- `image` (string) - Base64 encoded input image

**Optional Parameters:**
- `top` (int) - Pixels to add on top
- `bottom` (int) - Pixels to add on bottom
- `left` (int) - Pixels to add on left
- `right` (int) - Pixels to add on right
- `prompt` (string) - Description for expanded areas

**Example:**
```php
$response = $client->imageGeneration()->flux1Expand([
    'image' => base64_encode(file_get_contents('input.jpg')),
    'top' => 100,
    'bottom' => 100,
    'prompt' => 'Expand with matching scenery'
]);
```

#### Control Methods

##### `flux1Canny(array $params): ImageGenerationResponse`
Generate images using Canny edge detection as control guidance.

**Endpoint:** `POST /flux-pro-1.0-canny`

**Required Parameters:**
- `prompt` (string) - Text description
- `control_image` (string) - Base64 encoded control image

**Optional Parameters:**
- `canny_low_threshold` (int) - Low threshold for edge detection (default: 50)
- `canny_high_threshold` (int) - High threshold for edge detection (default: 200)

**Example:**
```php
$response = $client->imageGeneration()->flux1Canny([
    'prompt' => 'A realistic portrait',
    'control_image' => base64_encode(file_get_contents('edges.jpg')),
    'canny_low_threshold' => 50,
    'canny_high_threshold' => 200
]);
```

##### `flux1Depth(array $params): ImageGenerationResponse`
Generate images using depth information as control guidance.

**Endpoint:** `POST /flux-pro-1.0-depth`

**Required Parameters:**
- `prompt` (string) - Text description
- `control_image` (string) - Base64 encoded depth control image

---

### Finetune Service

Access via `$client->finetune()`. Handles fine-tuning operations and fine-tuned model usage.

#### Management Methods

##### `create(array $params): array`
Create a new fine-tuned model from training images.

**Endpoint:** `POST /finetune`

**Required Parameters:**
- `file_data` (string) - Base64 encoded training data ZIP file
- `finetune_comment` (string) - Description of the fine-tune
- `mode` (string) - Training mode ('style', 'object', etc.)
- `trigger_word` (string) - Trigger word for the model
- `iterations` (int) - Number of training iterations

**Example:**
```php
$response = $client->finetune()->create([
    'file_data' => base64_encode(file_get_contents('training_data.zip')),
    'finetune_comment' => 'My custom style',
    'mode' => 'style',
    'trigger_word' => 'MYSTYLE',
    'iterations' => 500
]);
```

##### `listMyFinetunes(): array`
List all fine-tunes belonging to the authenticated user.

**Endpoint:** `GET /my_finetunes`

**Returns:** Array containing finetunes list

**Example:**
```php
$finetunes = $client->finetune()->listMyFinetunes();
print_r($finetunes['finetunes']);
```

##### `getDetails(string $finetuneId): array`
Get detailed information about a specific fine-tune.

**Endpoint:** `GET /finetune_details`

**Parameters:**
- `finetune_id` (string) - ID of the fine-tune

##### `delete(string $finetuneId): array`
Delete a previously created fine-tune.

**Endpoint:** `POST /delete_finetune`

**Parameters:**
- `finetune_id` (string) - ID of the fine-tune to delete

#### Generation Methods with Fine-tuned Models

##### `generateWithFinetunedPro(array $params): ImageGenerationResponse`
Generate images using a fine-tuned FLUX Pro model.

**Endpoint:** `POST /flux-pro-finetuned`

**Required Parameters:**
- `finetune_id` (string) - ID of the fine-tuned model
- `prompt` (string) - Text description (should include trigger word)

**Optional Parameters:**
- `finetune_strength` (float) - Strength of fine-tuning effect (default: 1.0)
- `width`, `height`, `steps`, `guidance`, etc. - Standard generation parameters

**Example:**
```php
$response = $client->finetune()->generateWithFinetunedPro([
    'finetune_id' => 'your-finetune-id',
    'prompt' => 'MYSTYLE a beautiful landscape',
    'finetune_strength' => 1.2
]);
```

##### `generateWithFinetunedUltra(array $params): ImageGenerationResponse`
Generate images using a fine-tuned FLUX 1.1 Pro Ultra model.

**Endpoint:** `POST /flux-pro-1.1-ultra-finetune`

**Required Parameters:**
- `finetune_id` (string) - ID of the fine-tuned model
- `prompt` (string) - Text description

##### `generateWithFinetunedDepth(array $params): ImageGenerationResponse`
Generate images using fine-tuned FLUX1 Depth Pro with control guidance.

**Endpoint:** `POST /flux-pro-1.0-depth-finetune`

**Required Parameters:**
- `finetune_id` (string) - ID of the fine-tuned model
- `prompt` (string) - Text description
- `control_image` (string) - Base64 encoded depth control image

##### `generateWithFinetunedCanny(array $params): ImageGenerationResponse`
Generate images using fine-tuned FLUX1 Canny Pro with edge control.

**Endpoint:** `POST /flux-pro-1.0-canny-finetune`

**Required Parameters:**
- `finetune_id` (string) - ID of the fine-tuned model
- `prompt` (string) - Text description
- `control_image` (string) - Base64 encoded edge control image

##### `generateWithFinetunedFill(array $params): ImageGenerationResponse`
Generate images using fine-tuned FLUX1 Fill Pro for inpainting.

**Endpoint:** `POST /flux-pro-1.0-fill-finetune`

**Required Parameters:**
- `finetune_id` (string) - ID of the fine-tuned model
- `image` (string) - Base64 encoded input image

**Optional Parameters:**
- `mask` (string) - Base64 encoded mask image

---

### Utility Service

Access via `$client->utility()`. Handles task status checking and result polling.

##### `getResult(string $taskId): GetResultResponse`
Retrieve the current status or final result for a task.

**Endpoint:** `GET /get_result`

**Parameters:**
- `id` (string) - Task identifier from generation request

**Returns:** [`GetResultResponse`](#getresultresponse) object

**Example:**
```php
$result = $client->utility()->getResult('task-id-12345');

if ($result->isSuccessful()) {
    $imageUrl = $result->getResultAsString();
}
```

##### `pollResult(string $taskId, int $maxAttempts = 60, int $delaySeconds = 5): GetResultResponse`
Poll for task completion with automatic retries.

**Parameters:**
- `taskId` (string) - Task identifier
- `maxAttempts` (int) - Maximum polling attempts (default: 60)
- `delaySeconds` (int) - Delay between attempts in seconds (default: 5)

**Example:**
```php
$result = $client->utility()->pollResult('task-id', 100, 3);
```

##### `waitForCompletion(string $taskId): GetResultResponse`
Wait for task completion with sensible defaults (120 attempts, 3 second delay).

**Parameters:**
- `taskId` (string) - Task identifier

**Example:**
```php
$result = $client->utility()->waitForCompletion('task-id');
```

##### `isTaskComplete(string $taskId): bool`
Check if a task is complete without polling.

**Returns:** Boolean indicating completion status

##### `getProgress(string $taskId): ?float`
Get task progress percentage if available.

**Returns:** Progress as float (0.0-100.0) or null if not available

---

### ImageRequestBuilder

Fluent builder for constructing complex image generation requests.

#### Factory Method

##### `create(): ImageRequestBuilder`
Create a new builder instance.

**Example:**
```php
$builder = ImageRequestBuilder::create();
```

#### Configuration Methods

##### `withPrompt(string $prompt): ImageRequestBuilder`
Set the text prompt for image generation.

##### `withImagePrompt(string $imagePrompt): ImageRequestBuilder`
Set an optional image prompt (base64 encoded).

##### `withDimensions(int $width, int $height): ImageRequestBuilder`
Set exact pixel dimensions for the output image.

**Example:**
```php
$builder->withDimensions(1024, 768);
```

##### `withAspectRatio(string $ratio, int $baseSize = 1024): ImageRequestBuilder`
Set dimensions using aspect ratio with automatic calculation.

**Parameters:**
- `ratio` (string) - Aspect ratio like '16:9', '4:3', '1:1'
- `baseSize` (int) - Base dimension for calculation (default: 1024)

**Example:**
```php
$builder->withAspectRatio('16:9', 1024); // Results in 1024x576
```

##### `withSteps(int $steps): ImageRequestBuilder`
Set the number of denoising steps (more steps = higher quality, slower).

##### `withPromptUpsampling(bool $enabled = true): ImageRequestBuilder`
Enable or disable prompt upsampling for enhanced prompt processing.

##### `withSeed(int $seed): ImageRequestBuilder`
Set a specific seed for reproducible results.

##### `withRandomSeed(): ImageRequestBuilder`
Generate and set a random seed for varied results.

##### `withGuidance(float $guidance): ImageRequestBuilder`
Set classifier guidance scale (how closely to follow the prompt).

**Typical values:** 1.0-10.0 (default: 2.5)

##### `withInterval(float $interval): ImageRequestBuilder`
Set interval for progressive updates.

##### `withSafetyTolerance(int $tolerance): ImageRequestBuilder`
Set safety filtering level.

**Values:**
- `0` - Strictest filtering
- `2` - Balanced (default)
- `6` - Most lenient

##### `withOutputFormat(OutputFormat $format): ImageRequestBuilder`
Set output format using enum.

##### `asJpeg(): ImageRequestBuilder`
Set output format to JPEG (shorthand method).

##### `asPng(): ImageRequestBuilder`
Set output format to PNG (shorthand method).

##### `withWebhook(string $url, ?string $secret = null): ImageRequestBuilder`
Configure webhook for result delivery.

**Parameters:**
- `url` (string) - Webhook URL
- `secret` (string, optional) - Secret for webhook verification

#### Build Methods

##### `buildFlux1Pro(): Flux1ProRequest`
Build a typed request object for FLUX1 Pro generation.

##### `buildArray(): array`
Build a generic parameter array for other model types.

**Example:**
```php
$request = ImageRequestBuilder::create()
    ->withPrompt('A magical forest')
    ->withAspectRatio('4:3', 1024)
    ->withSteps(50)
    ->withGuidance(3.5)
    ->withRandomSeed()
    ->asPng()
    ->buildFlux1Pro();
```

---

### Response Objects

#### ImageGenerationResponse

Returned by all image generation methods.

**Properties:**
- `id` (string) - Task identifier for polling

**Methods:**
- Access task ID for polling: `$response->id`

#### GetResultResponse

Returned by utility methods for task results.

**Properties:**
- `id` (string) - Task identifier
- `status` (ResultStatus) - Current task status
- `result` (mixed) - Task result when complete

**Status Methods:**
- `isComplete(): bool` - Check if task is finished
- `isSuccessful(): bool` - Check if task completed successfully
- `isFailed(): bool` - Check if task failed
- `isInProgress(): bool` - Check if task is still processing

**Result Methods:**
- `getResultAsString(): ?string` - Get result as string (typically image URL)
- `getResultAsArray(): ?array` - Get result as array
- `getProgressPercentage(): ?float` - Get progress percentage if available

**Example:**
```php
$result = $client->utility()->getResult('task-id');

if ($result->isSuccessful()) {
    echo "Image URL: " . $result->getResultAsString();
} elseif ($result->isFailed()) {
    echo "Task failed: " . $result->status->value;
} elseif ($result->isInProgress()) {
    $progress = $result->getProgressPercentage();
    echo "Progress: {$progress}%";
}
```

## Requirements

- PHP 8.1 or higher
- Guzzle HTTP client 7.0+
- JSON extension

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Testing & Quality Assurance

This package includes comprehensive testing across multiple PHP versions and dependency combinations.

### Local Testing

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer phpstan

# Check code style
composer cs-check

# Fix code style
composer cs-fix

# Run all quality checks
composer check-all
```

### CI/CD Pipeline

The package is automatically tested using GitHub Actions across:

- **PHP Versions**: 8.1, 8.2, 8.3
- **Dependencies**: `prefer-lowest` and `prefer-stable`
- **Code Quality**: PHPStan (max level), PHP CS Fixer
- **Security**: Composer audit and security advisories

### Test Coverage

- **100+ Unit Tests**: All components individually tested
- **Integration Tests**: End-to-end workflow validation
- **Mock-based**: Zero real API calls during testing
- **Multiple Scenarios**: Success, error, and edge cases

### Quality Gates

All PRs must pass:
- ✅ Tests on all PHP versions
- ✅ Static analysis (PHPStan level max)
- ✅ Code style (PSR-12 + custom rules)
- ✅ Security audit
- ✅ No known vulnerabilities

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- [Documentation](docs/)
- [Issues](https://github.com/l4nos/PHP-Black-Forest-Labs/issues)
- [Black Forest Labs API Docs](https://docs.bfl.ai/)

## Disclaimer

This is an unofficial SDK for Black Forest Labs' FLUX API. It is not affiliated with or endorsed by Black Forest Labs.

---

Made with ❤️ by [l4nos](https://github.com/l4nos)