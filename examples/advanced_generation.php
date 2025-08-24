<?php

declare(strict_types=1);

/**
 * Advanced Image Generation Example.
 *
 * This example demonstrates advanced features like image editing,
 * control nets, webhooks, and different model types.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Lanos\PHPBFL\Builders\ImageRequestBuilder;
use Lanos\PHPBFL\Enums\OutputFormat;
use Lanos\PHPBFL\Exceptions\FluxApiException;
use Lanos\PHPBFL\FluxClient;

// Initialize client
$apiKey = getenv('BFL_API_KEY') ?: 'your-api-key-here';
$client = new FluxClient($apiKey);

try {
    echo "🚀 Starting advanced image generation examples...\n\n";

    // Example 1: Image Fill/Inpainting
    echo "🎨 Example 1: Image Fill (Inpainting)\n";
    echo "Note: This example requires input.jpg and mask.png files\n";

    $inputImagePath = __DIR__ . '/sample_images/input.jpg';
    $maskImagePath = __DIR__ . '/sample_images/mask.png';

    if (file_exists($inputImagePath) && file_exists($maskImagePath)) {
        $fillResponse = $client->imageGeneration()->flux1Fill([
            'image' => base64_encode(file_get_contents($inputImagePath)),
            'mask' => base64_encode(file_get_contents($maskImagePath)),
            'prompt' => 'A beautiful garden with colorful flowers',
            'steps' => 50,
            'guidance' => 60.0,
            'safety_tolerance' => 2,
            'output_format' => OutputFormat::PNG->value,
        ]);

        echo "✅ Fill task submitted! ID: {$fillResponse->id}\n";

        $fillResult = $client->utility()->waitForCompletion($fillResponse->id);
        if ($fillResult->isSuccessful()) {
            echo "🎉 Image fill completed successfully!\n";
            echo "🖼️  Result: {$fillResult->getResultAsString()}\n\n";
        }
    } else {
        echo "⚠️  Skipping fill example - sample images not found\n\n";
    }

    // Example 2: Image Expansion
    echo "📏 Example 2: Image Expansion\n";

    if (file_exists($inputImagePath)) {
        $expandResponse = $client->imageGeneration()->flux1Expand([
            'image' => base64_encode(file_get_contents($inputImagePath)),
            'top' => 200,    // Expand 200px at top
            'bottom' => 100, // Expand 100px at bottom
            'left' => 150,   // Expand 150px on left
            'right' => 150,  // Expand 150px on right
            'prompt' => 'Expand with matching scenery and natural continuation',
            'steps' => 45,
            'guidance' => 55.0,
        ]);

        echo "✅ Expand task submitted! ID: {$expandResponse->id}\n\n";
    } else {
        echo "⚠️  Skipping expand example - input image not found\n\n";
    }

    // Example 3: FLUX 1.1 Pro Ultra with Raw Mode
    echo "🔥 Example 3: FLUX 1.1 Pro Ultra with Raw Mode\n";

    $ultraResponse = $client->imageGeneration()->flux11ProUltra([
        'prompt' => 'A hyper-realistic close-up portrait of a wise old wizard with piercing blue eyes',
        'aspect_ratio' => '3:4',
        'raw' => true, // Less processed, more artistic
        'safety_tolerance' => 1, // Stricter filtering
        'output_format' => OutputFormat::PNG->value,
        'seed' => 12345, // Fixed seed for reproducibility
    ]);

    echo "✅ Ultra task submitted! ID: {$ultraResponse->id}\n";

    // Poll with progress updates
    $attempts = 0;
    while ($attempts < 60) {
        $result = $client->utility()->getResult($ultraResponse->id);

        if ($result->isComplete()) {
            if ($result->isSuccessful()) {
                echo "🎉 Ultra generation completed!\n";
                echo "🖼️  Result: {$result->getResultAsString()}\n";
            } else {
                echo "❌ Ultra generation failed: {$result->status->value}\n";
            }
            break;
        }

        $progress = $result->getProgressPercentage();
        if ($progress !== null) {
            $bar = str_repeat('█', (int) ($progress / 5));
            $empty = str_repeat('░', 20 - (int) ($progress / 5));
            echo "\r⏳ Progress: [{$bar}{$empty}] {$progress}%";
        }

        $attempts++;
        sleep(2);
    }
    echo "\n\n";

    // Example 4: Canny Edge Control
    echo "🎯 Example 4: Canny Edge Control\n";

    $edgeImagePath = __DIR__ . '/sample_images/edges.jpg';

    if (file_exists($edgeImagePath)) {
        $cannyResponse = $client->imageGeneration()->flux1Canny([
            'prompt' => 'A realistic portrait of a young woman with flowing hair',
            'control_image' => base64_encode(file_get_contents($edgeImagePath)),
            'canny_low_threshold' => 100,
            'canny_high_threshold' => 200,
            'steps' => 50,
            'guidance' => 45.0,
            'prompt_upsampling' => true, // Enhance the prompt
        ]);

        echo "✅ Canny task submitted! ID: {$cannyResponse->id}\n\n";
    } else {
        echo "⚠️  Skipping Canny example - edge image not found\n\n";
    }

    // Example 5: Depth Control
    echo "🌄 Example 5: Depth Control\n";

    $depthImagePath = __DIR__ . '/sample_images/depth.jpg';

    if (file_exists($depthImagePath)) {
        $depthResponse = $client->imageGeneration()->flux1Depth([
            'prompt' => 'A fantastical alien landscape with multiple moons in the sky',
            'control_image' => base64_encode(file_get_contents($depthImagePath)),
            'steps' => 50,
            'guidance' => 20.0, // Lower guidance for depth control
            'safety_tolerance' => 3,
        ]);

        echo "✅ Depth task submitted! ID: {$depthResponse->id}\n\n";
    } else {
        echo "⚠️  Skipping Depth example - depth map not found\n\n";
    }

    // Example 6: Kontext Pro (Multi-image input)
    echo "🔄 Example 6: Flux Kontext Pro\n";

    $image1Path = __DIR__ . '/sample_images/ref1.jpg';
    $image2Path = __DIR__ . '/sample_images/ref2.jpg';

    if (file_exists($image1Path) && file_exists($image2Path)) {
        $kontextResponse = $client->imageGeneration()->fluxKontextPro([
            'prompt' => 'Combine the styles and elements from the reference images',
            'input_image' => base64_encode(file_get_contents($image1Path)),
            'input_image_2' => base64_encode(file_get_contents($image2Path)),
            'aspect_ratio' => '16:9',
            'prompt_upsampling' => true,
            'safety_tolerance' => 2,
        ]);

        echo "✅ Kontext Pro task submitted! ID: {$kontextResponse->id}\n\n";
    } else {
        echo "⚠️  Skipping Kontext example - reference images not found\n\n";
    }

    // Example 7: Using Webhook
    echo "🔗 Example 7: Generation with Webhook\n";

    $webhookRequest = ImageRequestBuilder::create()
        ->withPrompt('A steampunk airship floating above Victorian London')
        ->withAspectRatio('21:9') // Cinematic aspect ratio
        ->withSteps(60)
        ->withGuidance(4.0)
        ->withWebhook('https://your-domain.com/webhook/flux', 'your-secret-key')
        ->asJpeg()
        ->buildFlux1Pro();

    $webhookResponse = $client->imageGeneration()->flux1Pro($webhookRequest);
    echo "✅ Webhook task submitted! ID: {$webhookResponse->id}\n";
    echo "🔗 Your webhook will be called when complete\n\n";

    // Example 8: Batch Processing
    echo "📦 Example 8: Batch Processing Multiple Images\n";

    $prompts = [
        'A magical forest with glowing mushrooms',
        'A cyberpunk city street at night with neon lights',
        'A peaceful zen garden with a stone bridge',
    ];

    $batchTasks = [];

    foreach ($prompts as $index => $prompt) {
        $request = ImageRequestBuilder::create()
            ->withPrompt($prompt)
            ->withDimensions(512, 512) // Smaller for faster generation
            ->withSteps(30)
            ->withRandomSeed()
            ->buildFlux1Pro();

        $response = $client->imageGeneration()->flux1Pro($request);
        $batchTasks[] = [
            'id' => $response->id,
            'prompt' => $prompt,
            'index' => $index + 1,
        ];

        echo `✅ Batch task {($index + 1)} submitted! ID: {$response->id}\n`;

        // Small delay to avoid rate limiting
        usleep(500000); // 0.5 seconds
    }

    echo "\n⏳ Waiting for all batch tasks to complete...\n";

    // Wait for all tasks to complete
    $completedTasks = 0;
    $totalTasks = count($batchTasks);

    while ($completedTasks < $totalTasks) {
        foreach ($batchTasks as &$task) {
            if (!isset($task['completed'])) {
                $result = $client->utility()->getResult($task['id']);

                if ($result->isComplete()) {
                    $task['completed'] = true;
                    $task['result'] = $result;
                    $completedTasks++;

                    if ($result->isSuccessful()) {
                        echo "✅ Task {$task['index']} completed successfully!\n";
                    } else {
                        echo "❌ Task {$task['index']} failed: {$result->status->value}\n";
                    }
                }
            }
        }

        if ($completedTasks < $totalTasks) {
            echo "⏳ {$completedTasks}/{$totalTasks} tasks completed...\n";
            sleep(3);
        }
    }

    echo "\n🎉 All batch tasks completed!\n";

} catch (FluxApiException $e) {
    echo "🚨 API Error: {$e->getFriendlyMessage()}\n";
    echo "📊 Status Code: {$e->getCode()}\n";
} catch (Exception $e) {
    echo "💥 Unexpected Error: {$e->getMessage()}\n";
}

echo "\n✨ Advanced examples completed!\n";
