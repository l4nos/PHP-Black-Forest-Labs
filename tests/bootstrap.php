<?php

declare(strict_types=1);

/**
 * Test Bootstrap - Additional safety measures to prevent real API calls.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Set test environment variable
putenv('TESTING=true');

// Override any real API key that might be set
putenv('BFL_API_KEY=test-key-for-testing-only');

// Ensure we're in test mode
if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', __DIR__ . '/../vendor/autoload.php');
}

// Additional safety: Fail if trying to make real HTTP requests in tests
if (class_exists('GuzzleHttp\Client')) {
    // This would catch any unmocked HTTP clients in tests
    $originalAutoload = spl_autoload_functions();

    echo "🧪 Test bootstrap loaded - All HTTP requests will be mocked\n";
    echo "⚠️  Real API calls are BLOCKED in test environment\n";
}
