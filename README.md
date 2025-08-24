# PHP Black Forest Labs SDK

A professional PHP SDK for [Black Forest Labs' FLUX API](https://api.bfl.ai/), providing easy integration for AI-powered image generation and fine-tuning services.

[![Latest Version](https://img.shields.io/packagist/v/lanos/php-bfl.svg)](https://packagist.org/packages/lanos/php-bfl)
[![PHP Version](https://img.shields.io/packagist/php-v/lanos/php-bfl.svg)](https://packagist.org/packages/lanos/php-bfl)
[![License](https://img.shields.io/packagist/l/lanos/php-bfl.svg)](https://github.com/l4nos/php-bfl/blob/main/LICENSE.md)
[![Tests](https://github.com/l4nos/php-bfl/workflows/Tests/badge.svg)](https://github.com/l4nos/php-bfl/actions)
[![Coverage](https://codecov.io/gh/l4nos/php-bfl/branch/main/graph/badge.svg)](https://codecov.io/gh/l4nos/php-bfl)

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

## API Reference

For complete API documentation, see the [API Reference](docs/api-reference.md).

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
- [Issues](https://github.com/l4nos/php-bfl/issues)
- [Black Forest Labs API Docs](https://docs.bfl.ai/)

## Disclaimer

This is an unofficial SDK for Black Forest Labs' FLUX API. It is not affiliated with or endorsed by Black Forest Labs.

---

Made with ❤️ by [Lanos](https://github.com/l4nos)