<?php

declare(strict_types=1);

/**
 * Basic Image Generation Example.
 *
 * This example demonstrates how to generate images using the FLUX API SDK
 * with the most common parameters and error handling patterns.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Lanos\PHPBFL\Builders\ImageRequestBuilder;
use Lanos\PHPBFL\Exceptions\AuthenticationException;
use Lanos\PHPBFL\Exceptions\FluxApiException;
use Lanos\PHPBFL\FluxClient;

// Initialize client with your API key
$apiKey = getenv('BFL_API_KEY') ?: 'your-api-key-here';
$client = new FluxClient($apiKey);

try {
    echo "🚀 Starting basic image generation...\n\n";

    // Example 1: Simple prompt with FLUX1 Pro
    echo "📝 Generating image with simple prompt...\n";

    $request = ImageRequestBuilder::create()
        ->withPrompt('A majestic dragon soaring through fluffy white clouds at golden sunset')
        ->withDimensions(1024, 768)
        ->withSteps(40)
        ->asJpeg()
        ->buildFlux1Pro();

    $response = $client->imageGeneration()->flux1Pro($request);

    echo "✅ Task submitted! ID: {$response->id}\n";
    echo "🔄 Polling URL: {$response->pollingUrl}\n\n";

    // Wait for completion
    echo "⏳ Waiting for image generation to complete...\n";
    $result = $client->utility()->waitForCompletion($response->id);

    if ($result->isSuccessful()) {
        $imageUrl = $result->getResultAsString();
        echo "🎉 Generation successful!\n";
        echo "🖼️  Image URL: $imageUrl\n\n";

        // Optionally download the image
        if ($imageUrl) {
            $imageData = file_get_contents($imageUrl);
            $filename = 'generated_dragon_' . date('Y-m-d_H-i-s') . '.jpg';
            file_put_contents(__DIR__ . '/' . $filename, $imageData);
            echo "💾 Image saved as: $filename\n\n";
        }
    } else {
        echo "❌ Generation failed with status: {$result->status->value}\n";
        if ($result->details) {
            echo '📋 Details: ' . json_encode($result->details, JSON_PRETTY_PRINT) . "\n";
        }
    }

    // Example 2: Generate with different aspect ratio
    echo "📝 Generating landscape image...\n";

    $landscapeRequest = ImageRequestBuilder::create()
        ->withPrompt('A serene mountain landscape with a crystal clear lake reflection')
        ->withAspectRatio('16:9') // Will calculate appropriate dimensions
        ->withSteps(50)
        ->withGuidance(3.0) // Higher guidance for more prompt adherence
        ->withRandomSeed() // Use random seed for variation
        ->asPng()
        ->buildFlux1Pro();

    $landscapeResponse = $client->imageGeneration()->flux1Pro($landscapeRequest);
    echo "✅ Landscape task submitted! ID: {$landscapeResponse->id}\n";

    // Poll manually to show progress
    $attempts = 0;
    $maxAttempts = 60;

    while ($attempts < $maxAttempts) {
        $currentResult = $client->utility()->getResult($landscapeResponse->id);

        if ($currentResult->isComplete()) {
            if ($currentResult->isSuccessful()) {
                $landscapeUrl = $currentResult->getResultAsString();
                echo "🎉 Landscape generation successful!\n";
                echo "🖼️  Image URL: $landscapeUrl\n";

                if ($landscapeUrl) {
                    $imageData = file_get_contents($landscapeUrl);
                    $filename = 'generated_landscape_' . date('Y-m-d_H-i-s') . '.png';
                    file_put_contents(__DIR__ . '/' . $filename, $imageData);
                    echo "💾 Image saved as: $filename\n";
                }
            } else {
                echo "❌ Landscape generation failed: {$currentResult->status->value}\n";
            }
            break;
        }

        $progress = $currentResult->getProgressPercentage();
        if ($progress !== null) {
            echo "⏳ Progress: {$progress}%\n";
        } else {
            echo "⏳ Status: {$currentResult->status->value}\n";
        }

        $attempts++;
        sleep(3);
    }

    if ($attempts >= $maxAttempts) {
        echo "⏰ Timeout reached while waiting for completion\n";
    }

} catch (AuthenticationException $e) {
    echo "🔐 Authentication Error: {$e->getMessage()}\n";
    echo "💡 Please check your API key is correct and has sufficient credits\n";
} catch (FluxApiException $e) {
    echo "🚨 API Error: {$e->getFriendlyMessage()}\n";
    echo "📊 HTTP Status: {$e->getCode()}\n";
    echo "🔍 Details: {$e->getMessage()}\n";
} catch (Exception $e) {
    echo "💥 Unexpected Error: {$e->getMessage()}\n";
}

echo "\n✨ Example completed!\n";
