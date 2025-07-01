<?php
/**
 * Simple Twilio Upgrade Test Script
 * Run this after each upgrade phase to verify functionality
 */

require_once(__DIR__ . '/vendor/autoload.php');

use Twilio\Rest\Client;
use Twilio\Security\RequestValidator;

echo "🔧 Twilio SDK Upgrade Test\n";
echo "==========================\n\n";

// Check Twilio SDK version
$composer_lock = json_decode(file_get_contents(__DIR__ . '/composer.lock'), true);
foreach ($composer_lock['packages'] as $package) {
    if ($package['name'] === 'twilio/sdk') {
        echo "📦 Twilio SDK Version: " . $package['version'] . "\n";
        break;
    }
}

echo "🐘 PHP Version: " . PHP_VERSION . "\n\n";

// Test 1: Basic class loading
try {
    $test_client = new Client('test_sid', 'test_token');
    echo "✅ Client class instantiation: PASS\n";
} catch (Exception $e) {
    echo "❌ Client class instantiation: FAIL - " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Request Validator
try {
    $validator = new RequestValidator('test_token');
    echo "✅ RequestValidator class: PASS\n";
} catch (Exception $e) {
    echo "❌ RequestValidator class: FAIL - " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Check for WordPress integration (if available)
if (file_exists(__DIR__ . '/twilio-api/twilio-api.php')) {
    require_once(__DIR__ . '/twilio-api/twilio-api.php');
    
    if (class_exists('Disciple_Tools_Twilio_API')) {
        echo "✅ WordPress integration class: PASS\n";
        
        // Test method availability
        $methods = ['is_enabled', 'has_credentials', 'list_services', 'send_sms', 'send_whatsapp'];
        foreach ($methods as $method) {
            if (method_exists('Disciple_Tools_Twilio_API', $method)) {
                echo "✅ Method {$method}: PASS\n";
            } else {
                echo "❌ Method {$method}: FAIL\n";
            }
        }
    } else {
        echo "❌ WordPress integration class: FAIL\n";
    }
}

echo "\n🎉 Basic compatibility tests completed!\n";
echo "Run full WordPress tests in your environment.\n"; 