<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

use Twilio\Rest\Client;
use Twilio\Security\RequestValidator;

class Disciple_Tools_Twilio_Rest
{
    public $permissions = [ 'access_contacts', 'dt_all_access_contacts', 'view_project_metrics' ];


    public function add_api_routes() {
        $namespace = 'dt-public/twilio/v1';

        register_rest_route(
            $namespace, '/webhook', [
                'methods'  => 'GET',
                'callback' => [ $this, 'webhook' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return true;
                },
            ]
        );

        register_rest_route(
            $namespace, '/webhook', [
                'methods'  => 'POST',
                'callback' => [ $this, 'webhook' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return true;
                },
            ]
        );
    }


    public function webhook( WP_REST_Request $request ) {
        $params = $request->get_params();
        $headers = $request->get_headers();

        if ( !isset( $params['From'] ) || !isset( $params['To'] ) || !isset( $params['Body'] ) ) {
            return false;
        }

        $phone_number_location = ( $params['FromCity'] ?? '' ) . ', ' . ( $params['FromState'] ?? '' ) . ', ' . ( $params['FromCountry'] ?? '' ) . ', ' . ( $params['FromZip'] ?? '' );

        //validate https://www.twilio.com/docs/usage/webhooks/webhooks-security
        $token = get_option( Disciple_Tools_Twilio_API::$option_twilio_token );
        if ( empty( $token ) ) {
            return false;
        }
        $signature = $headers['x_twilio_signature'][0] ?? '';
        $validator = new RequestValidator( $token );
        $url = get_site_url() . '/wp-json/dt-public/twilio/v1/webhook';
        if ( !$validator->validate( $signature, $url, $params ) ){
            return false;
        }

        if ( class_exists( 'Communication_Handles' ) ) {

            $conversations_record = Communication_Handles::create_or_update_conversation_record(
                $params['From'],
                [ 'type' => 'phone' ],
            );
            if ( !is_wp_error( $conversations_record ) ){
                DT_Posts::add_post_comment( 'conversations', $conversations_record['ID'], $phone_number_location, 'twilio', [], false, true );
                DT_Posts::add_post_comment( 'conversations', $conversations_record['ID'], $params['Body'], 'twilio', [], false, false );

                $sid = get_option( Disciple_Tools_Twilio_API::$option_twilio_sid );

                $twilio = new Client( $sid, $token );

                $message = $twilio->messages
                    ->create( $params['From'],
                        [
                            'from' => 'whatsapp:+14054496743',
                            'body' => 'Thank you.'
                        ]
                    );
                dt_write_log( $message->sid );
            }
        } else {
            //@todo find contact and add message
            return true;
        }

        return true;
    }

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }
    public function has_permission(){
        $pass = false;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }
}
Disciple_Tools_Twilio_Rest::instance();
