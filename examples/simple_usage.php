<?php

declare(strict_types=1);

/**
 * Simple Usage Example - Quick Start Guide
 * 
 * This is the most basic example showing minimal code needed
 * to generate an image with the FLUX API.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Lanos\PHPBFL\FluxClient;
use Lanos\PHPBFL\Builders\ImageRequestBuilder;

// 1. Initialize the client
$client = new FluxClient('your-api-key-here');

// 2. Build a simple request
$request = ImageRequestBuilder::create()
    ->withPrompt('A beautiful sunset over a mountain lake')
    ->withDimensions(1024, 768)
    ->buildFlux1Pro();

try {
    // 3. Generate the image
    $response = $client->imageGeneration()->flux1Pro($request);
    echo "🚀 Task started! ID: {$response->id}\n";
    
    // 4. Wait for completion
    $result = $client->utility()->waitForCompletion($response->id);
    
    // 5. Get the result
    if ($result->isSuccessful()) {
        $imageUrl = $result->getResultAsString();
        echo "✅ Success! Image URL: {$imageUrl}\n";
    } else {
        echo "❌ Failed: {$result->status->value}\n";
    }
    
} catch (Exception $e) {
    echo "💥 Error: {$e->getMessage()}\n";
}