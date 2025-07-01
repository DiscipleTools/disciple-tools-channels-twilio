<?php
/**
 * Twilio SDK Upgrade Test Script
 *
 * Use this script to test Twilio functionality during the upgrade process.
 * Run after each upgrade phase to ensure compatibility.
 *
 * Usage: php test-twilio-upgrade.php
 */

// WordPress environment (if running outside WP context)
if ( !defined( 'ABSPATH' ) ) {
    // Adjust path as needed for your WordPress installation
    require_once( __DIR__ . '/../../../../wp-config.php' );
}

require_once( __DIR__ . '/vendor/autoload.php' );
require_once( __DIR__ . '/twilio-api/twilio-api.php' );

use Twilio\Rest\Client;
use Twilio\Security\RequestValidator;

class TwilioUpgradeTest {

    private $client;
    private $test_results = [];
    private $has_credentials = false;

    public function __construct() {
        echo "🔧 Twilio SDK Upgrade Test Suite\n";
        echo "================================\n\n";

        $this->checkCredentials();
        $this->displayVersionInfo();
    }

    private function checkCredentials() {
        $this->has_credentials = Disciple_Tools_Twilio_API::has_credentials();

        if ( $this->has_credentials ) {
            try {
                $this->client = new Client(
                    Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_sid ),
                    Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_token )
                );
                $this->logTest( 'Credentials', true, 'Twilio credentials loaded successfully' );
            } catch ( Exception $e ) {
                $this->logTest( 'Credentials', false, 'Failed to initialize client: ' . $e->getMessage() );
            }
        } else {
            $this->logTest( 'Credentials', false, 'No Twilio credentials configured' );
        }
    }

    private function displayVersionInfo() {
        echo "📋 Version Information:\n";

        // PHP Version
        echo '   PHP Version: ' . PHP_VERSION . "\n";

        // Twilio SDK Version
        $composer_lock = json_decode( file_get_contents( __DIR__ . '/composer.lock' ), true );
        foreach ( $composer_lock['packages'] as $package ) {
            if ( $package['name'] === 'twilio/sdk' ) {
                echo '   Twilio SDK: ' . $package['version'] . "\n";
                break;
            }
        }

        // WordPress version
        if ( function_exists( 'get_bloginfo' ) ) {
            echo '   WordPress: ' . get_bloginfo( 'version' ) . "\n";
        }

        echo "\n";
    }

    public function runAllTests() {
        echo "🧪 Running Test Suite:\n";
        echo "====================\n\n";

        // Core functionality tests
        $this->testClientInitialization();
        $this->testPhoneNumberListing();
        $this->testMessagingServices();
        $this->testRequestValidator();

        // WordPress integration tests
        $this->testWordPressIntegration();

        // Summary
        $this->displayResults();
    }

    private function testClientInitialization() {
        try {
            if ( !$this->has_credentials ) {
                $this->logTest( 'Client Init', 'SKIP', 'No credentials available' );
                return;
            }

            // Test basic client creation
            $client = new Client( 'test', 'test' );
            $this->logTest( 'Client Init', true, 'Client instantiation works' );

            // Test with real credentials (account fetch)
            $account = $this->client->api->accounts->fetch();
            $this->logTest( 'Account Fetch', true, 'Account SID: ' . substr( $account->sid, 0, 8 ) . '...' );

        } catch ( Exception $e ) {
            $this->logTest( 'Client Init', false, $e->getMessage() );
        }
    }

    private function testPhoneNumberListing() {
        try {
            if ( !$this->has_credentials ) {
                $this->logTest( 'Phone Numbers', 'SKIP', 'No credentials available' );
                return;
            }

            $numbers = Disciple_Tools_Twilio_API::list_incoming_phone_numbers();
            $count = count( $numbers );
            $this->logTest( 'Phone Numbers', true, "Listed {$count} phone numbers" );

        } catch ( Exception $e ) {
            $this->logTest( 'Phone Numbers', false, $e->getMessage() );
        }
    }

    private function testMessagingServices() {
        try {
            if ( !$this->has_credentials ) {
                $this->logTest( 'Messaging Services', 'SKIP', 'No credentials available' );
                return;
            }

            $services = Disciple_Tools_Twilio_API::list_messaging_services();
            $count = count( $services );
            $this->logTest( 'Messaging Services', true, "Listed {$count} messaging services" );

        } catch ( Exception $e ) {
            $this->logTest( 'Messaging Services', false, $e->getMessage() );
        }
    }

    private function testRequestValidator() {
        try {
            $token = 'test_token';
            $validator = new RequestValidator( $token );

            // Test signature validation with known values
            $url = 'https://example.com/webhook';
            $params = ['test' => 'value'];
            $signature = $validator->computeSignature( $url, $params );

            $is_valid = $validator->validate( $signature, $url, $params );
            $this->logTest( 'Request Validator', $is_valid, 'Signature validation working' );

        } catch ( Exception $e ) {
            $this->logTest( 'Request Validator', false, $e->getMessage() );
        }
    }

    private function testWordPressIntegration() {
        try {
            // Test API class methods
            $enabled = Disciple_Tools_Twilio_API::is_enabled();
            $this->logTest( 'WP Integration', true, 'API class methods accessible, enabled: ' . ( $enabled ? 'yes' : 'no' ) );

            // Test service listing
            $services = Disciple_Tools_Twilio_API::list_services();
            $this->logTest( 'Service List', count( $services ) > 0, 'Services list: ' . count( $services ) . ' items' );

        } catch ( Exception $e ) {
            $this->logTest( 'WP Integration', false, $e->getMessage() );
        }
    }

    private function logTest( $test_name, $status, $message = '' ) {
        $icon = '❌';
        if ( $status === true ) {
            $icon = '✅';
        } elseif ( $status === 'SKIP' ) {
            $icon = '⏭️';
        }

        $this->test_results[] = [
            'name' => $test_name,
            'status' => $status,
            'message' => $message
        ];

        printf( "   %-20s %s %s\n", $test_name . ':', $icon, $message );
    }

    private function displayResults() {
        echo "\n📊 Test Summary:\n";
        echo "================\n";

        $passed = 0;
        $failed = 0;
        $skipped = 0;

        foreach ( $this->test_results as $result ) {
            if ( $result['status'] === true ) {
                $passed++;
            } elseif ( $result['status'] === 'SKIP' ) {
                $skipped++;
            } else {
                $failed++;
            }
        }

        echo "✅ Passed:  {$passed}\n";
        echo "❌ Failed:  {$failed}\n";
        echo "⏭️ Skipped: {$skipped}\n";

        if ( $failed > 0 ) {
            echo "\n⚠️  Some tests failed. Review the errors above before proceeding.\n";
            exit( 1 );
        } elseif ( $passed === 0 ) {
            echo "\n⚠️  No tests passed. Check your configuration.\n";
            exit( 1 );
        } else {
            echo "\n🎉 All tests passed! Ready to proceed with upgrade.\n";
        }
    }

    public function testSpecificFunction( $function_name ) {
        echo "🎯 Testing specific function: {$function_name}\n";
        echo "=====================================\n\n";

        switch ( $function_name ) {
            case 'sms':
                $this->testSMSSending();
                break;
            case 'whatsapp':
                $this->testWhatsAppSending();
                break;
            case 'webhook':
                $this->testWebhookProcessing();
                break;
            default:
                echo "❌ Unknown function: {$function_name}\n";
                echo "Available: sms, whatsapp, webhook\n";
        }
    }

    private function testSMSSending() {
        // Note: This is a dry run test - doesn't actually send
        try {
            if ( !$this->has_credentials ) {
                $this->logTest( 'SMS Test', 'SKIP', 'No credentials for actual sending' );
                return;
            }

            // Test the method exists and is callable
            $method_exists = method_exists( 'Disciple_Tools_Twilio_API', 'send_sms' );
            $this->logTest( 'SMS Method', $method_exists, 'send_sms method available' );

        } catch ( Exception $e ) {
            $this->logTest( 'SMS Test', false, $e->getMessage() );
        }
    }

    private function testWhatsAppSending() {
        // Note: This is a dry run test - doesn't actually send
        try {
            if ( !$this->has_credentials ) {
                $this->logTest( 'WhatsApp Test', 'SKIP', 'No credentials for actual sending' );
                return;
            }

            // Test the method exists and is callable
            $method_exists = method_exists( 'Disciple_Tools_Twilio_API', 'send_whatsapp' );
            $this->logTest( 'WhatsApp Method', $method_exists, 'send_whatsapp method available' );

        } catch ( Exception $e ) {
            $this->logTest( 'WhatsApp Test', false, $e->getMessage() );
        }
    }

    private function testWebhookProcessing() {
        try {
            // Test webhook class exists
            $class_exists = class_exists( 'Disciple_Tools_Twilio_Rest' );
            $this->logTest( 'Webhook Class', $class_exists, 'Webhook handler class available' );

        } catch ( Exception $e ) {
            $this->logTest( 'Webhook Test', false, $e->getMessage() );
        }
    }
}

// Main execution
if ( isset( $argv[1] ) ) {
    $tester = new TwilioUpgradeTest();
    $tester->testSpecificFunction( $argv[1] );
} else {
    $tester = new TwilioUpgradeTest();
    $tester->runAllTests();
}
