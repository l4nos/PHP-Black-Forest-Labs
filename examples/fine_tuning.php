<?php

declare(strict_types=1);

/**
 * Fine-tuning Workflow Example.
 *
 * This example demonstrates the complete fine-tuning workflow:
 * 1. Creating a fine-tune
 * 2. Monitoring training progress
 * 3. Using the trained model for generation
 * 4. Managing fine-tunes
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Lanos\PHPBFL\Enums\FinetuneMode;
use Lanos\PHPBFL\Exceptions\FluxApiException;
use Lanos\PHPBFL\FluxClient;

// Initialize client
$apiKey = getenv('BFL_API_KEY') ?: 'your-api-key-here';
$client = new FluxClient($apiKey);

try {
    echo "🚀 Starting fine-tuning workflow example...\n\n";

    // Example 1: List existing fine-tunes
    echo "📋 Step 1: Listing your existing fine-tunes...\n";

    $myFinetunes = $client->finetune()->listMyFinetunes();

    if (!empty($myFinetunes['finetunes'])) {
        echo '✅ Found ' . count($myFinetunes['finetunes']) . " existing fine-tunes:\n";
        foreach ($myFinetunes['finetunes'] as $finetuneId) {
            echo "  - {$finetuneId}\n";
        }
    } else {
        echo "📝 No existing fine-tunes found\n";
    }
    echo "\n";

    // Example 2: Get details for an existing fine-tune (if any)
    if (!empty($myFinetunes['finetunes'])) {
        $firstFinetuneId = $myFinetunes['finetunes'][0];
        echo "🔍 Step 2: Getting details for fine-tune: {$firstFinetuneId}\n";

        try {
            $finetuneDetails = $client->finetune()->getDetails($firstFinetuneId);
            echo "✅ Fine-tune details retrieved:\n";
            echo json_encode($finetuneDetails, JSON_PRETTY_PRINT) . "\n\n";
        } catch (FluxApiException $e) {
            echo "⚠️  Could not get fine-tune details: {$e->getMessage()}\n\n";
        }
    }

    // Example 3: Create a new fine-tune (commented out as it requires actual training data)
    echo "🎓 Step 3: Creating a new fine-tune (example parameters)\n";
    echo "Note: This example shows the parameters but doesn't actually create a fine-tune\n";
    echo "      as it requires a real training dataset ZIP file.\n\n";

    /*
    // Uncomment and modify this section when you have training data

    $trainingDataPath = __DIR__ . '/training_data/my_style_training.zip';

    if (file_exists($trainingDataPath)) {
        echo "📦 Training data file found, creating fine-tune...\n";

        $finetuneParams = [
            'file_data' => base64_encode(file_get_contents($trainingDataPath)),
            'finetune_comment' => 'My Custom Art Style v1.0',
            'mode' => FinetuneMode::STYLE->value,
            'trigger_word' => 'MYSTYLE',
            'iterations' => 400,
            'learning_rate' => 0.0001,
            'captioning' => true,
            'priority' => 'quality',
            'finetune_type' => 'lora',
            'lora_rank' => 32
        ];

        $createResponse = $client->finetune()->create($finetuneParams);
        echo "✅ Fine-tune creation started!\n";
        echo "📋 Response: " . json_encode($createResponse, JSON_PRETTY_PRINT) . "\n\n";

        // Note: Fine-tuning can take several hours to complete
        // You would typically use webhooks or periodic polling to check status

    } else {
        echo "⚠️  Training data file not found at: {$trainingDataPath}\n";
        echo "💡 To create a fine-tune, prepare a ZIP file with:\n";
        echo "   - Training images (JPEG/PNG)\n";
        echo "   - Caption files (.txt) with same names as images\n";
        echo "   - 10-100 high-quality training examples\n\n";
    }
    */

    // Example parameters for reference
    $exampleParams = [
        'file_data' => '[BASE64_ENCODED_ZIP_FILE]',
        'finetune_comment' => 'Portrait Style Fine-tune',
        'mode' => FinetuneMode::CHARACTER->value,
        'trigger_word' => 'PORTRAITSTYLE',
        'iterations' => 300,
        'learning_rate' => 0.0001, // Optional: let the system choose if not specified
        'captioning' => true,
        'priority' => 'quality', // 'speed', 'quality', or 'high_res_only'
        'finetune_type' => 'lora', // 'lora' or 'full'
        'lora_rank' => 32, // 16 or 32 for LoRA
    ];

    echo "📝 Example fine-tune parameters:\n";
    echo json_encode($exampleParams, JSON_PRETTY_PRINT) . "\n\n";

    // Example 4: Generate with a fine-tuned model (requires existing fine-tune)
    if (!empty($myFinetunes['finetunes'])) {
        $finetuneId = $myFinetunes['finetunes'][0];
        echo "🎨 Step 4: Generating image with fine-tuned model: {$finetuneId}\n";

        try {
            // Generate with fine-tuned FLUX Pro
            $generateResponse = $client->finetune()->generateWithFinetunedPro([
                'finetune_id' => $finetuneId,
                'prompt' => 'A beautiful portrait in the trained style',
                'finetune_strength' => 1.2, // How strongly to apply the fine-tune
                'steps' => 40,
                'guidance' => 2.5,
                'width' => 1024,
                'height' => 1024,
                'safety_tolerance' => 2,
                'output_format' => 'png',
            ]);

            echo "✅ Fine-tuned generation started! Task ID: {$generateResponse->id}\n";

            // Wait for completion
            echo "⏳ Waiting for generation to complete...\n";
            $result = $client->utility()->waitForCompletion($generateResponse->id);

            if ($result->isSuccessful()) {
                $imageUrl = $result->getResultAsString();
                echo "🎉 Fine-tuned image generated successfully!\n";
                echo "🖼️  Image URL: {$imageUrl}\n";

                if ($imageUrl) {
                    $imageData = file_get_contents($imageUrl);
                    $filename = 'finetuned_' . date('Y-m-d_H-i-s') . '.png';
                    file_put_contents(__DIR__ . '/' . $filename, $imageData);
                    echo "💾 Image saved as: {$filename}\n";
                }
            } else {
                echo "❌ Fine-tuned generation failed: {$result->status->value}\n";
            }
            echo "\n";

        } catch (FluxApiException $e) {
            echo "⚠️  Error generating with fine-tuned model: {$e->getMessage()}\n\n";
        }
    }

    // Example 5: Generate with fine-tuned Ultra model
    if (!empty($myFinetunes['finetunes'])) {
        $finetuneId = $myFinetunes['finetunes'][0];
        echo "⚡ Step 5: Generating with fine-tuned Ultra model\n";

        try {
            $ultraResponse = $client->finetune()->generateWithFinetunedUltra([
                'finetune_id' => $finetuneId,
                'prompt' => 'An epic landscape in the trained artistic style',
                'finetune_strength' => 1.0,
                'aspect_ratio' => '16:9',
                'safety_tolerance' => 1,
                'output_format' => 'jpeg',
            ]);

            echo "✅ Fine-tuned Ultra generation started! Task ID: {$ultraResponse->id}\n\n";

        } catch (FluxApiException $e) {
            echo "⚠️  Error with fine-tuned Ultra: {$e->getMessage()}\n\n";
        }
    }

    // Example 6: Fine-tuned control nets
    if (!empty($myFinetunes['finetunes'])) {
        $finetuneId = $myFinetunes['finetunes'][0];
        echo "🎯 Step 6: Fine-tuned control net examples\n";

        // Fine-tuned Depth control
        $controlImagePath = __DIR__ . '/sample_images/depth_control.jpg';
        if (file_exists($controlImagePath)) {
            echo "📐 Generating with fine-tuned Depth control...\n";

            try {
                $depthResponse = $client->finetune()->generateWithFinetunedDepth([
                    'finetune_id' => $finetuneId,
                    'prompt' => 'A magical scene with the trained style and depth structure',
                    'control_image' => base64_encode(file_get_contents($controlImagePath)),
                    'finetune_strength' => 1.1,
                    'steps' => 50,
                    'guidance' => 18.0,
                ]);

                echo "✅ Fine-tuned Depth task submitted! ID: {$depthResponse->id}\n";

            } catch (FluxApiException $e) {
                echo "⚠️  Error with fine-tuned Depth: {$e->getMessage()}\n";
            }
        }

        // Fine-tuned Fill operation
        $fillImagePath = __DIR__ . '/sample_images/fill_input.jpg';
        if (file_exists($fillImagePath)) {
            echo "🎨 Generating with fine-tuned Fill...\n";

            try {
                $fillResponse = $client->finetune()->generateWithFinetunedFill([
                    'finetune_id' => $finetuneId,
                    'image' => base64_encode(file_get_contents($fillImagePath)),
                    'prompt' => 'Complete this image in the trained artistic style',
                    'finetune_strength' => 1.3,
                    'steps' => 45,
                    'guidance' => 65.0,
                ]);

                echo "✅ Fine-tuned Fill task submitted! ID: {$fillResponse->id}\n";

            } catch (FluxApiException $e) {
                echo "⚠️  Error with fine-tuned Fill: {$e->getMessage()}\n";
            }
        }
        echo "\n";
    }

    // Example 7: Fine-tune management
    echo "🗂️  Step 7: Fine-tune Management\n";

    if (!empty($myFinetunes['finetunes']) && count($myFinetunes['finetunes']) > 1) {
        // Example of deleting a fine-tune (commented out for safety)
        $oldFinetuneId = end($myFinetunes['finetunes']);

        echo "🗑️  Example: Deleting fine-tune {$oldFinetuneId}\n";
        echo "Note: Deletion is commented out for safety\n";

        /*
        try {
            $deleteResponse = $client->finetune()->delete($oldFinetuneId);
            echo "✅ Fine-tune deleted successfully:\n";
            echo json_encode($deleteResponse, JSON_PRETTY_PRINT) . "\n";
        } catch (FluxApiException $e) {
            echo "⚠️  Error deleting fine-tune: {$e->getMessage()}\n";
        }
        */
    }

    // Best Practices Summary
    echo "\n💡 Fine-tuning Best Practices:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. 📸 Use 10-100 high-quality, diverse training images\n";
    echo "2. 📝 Write detailed, consistent captions for each image\n";
    echo "3. 🎨 Choose appropriate mode: 'character', 'style', 'product', 'general'\n";
    echo "4. 🔤 Pick a unique trigger word (avoid common words)\n";
    echo "5. ⚙️  Start with default parameters, then experiment\n";
    echo "6. 🔄 Use webhooks for long training processes\n";
    echo "7. 📊 Test different finetune_strength values (0.8-1.5)\n";
    echo "8. 💾 Keep your training data organized and backed up\n";
    echo "9. 🧪 Experiment with LoRA vs full fine-tuning\n";
    echo "10. 🎯 Use fine-tuned models with control nets for precision\n";

} catch (FluxApiException $e) {
    echo "🚨 API Error: {$e->getFriendlyMessage()}\n";
    echo "📊 Status Code: {$e->getCode()}\n";
} catch (Exception $e) {
    echo "💥 Unexpected Error: {$e->getMessage()}\n";
}

echo "\n✨ Fine-tuning workflow example completed!\n";
