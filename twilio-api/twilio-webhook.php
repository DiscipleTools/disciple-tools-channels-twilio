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


    /**
     * See https://www.twilio.com/docs/messaging/tutorials/how-to-receive-and-reply/php
     * See https://www.twilio.com/docs/usage/webhooks/webhooks-security
     * See parameters: https://www.twilio.com/docs/messaging/guides/webhook-request
     * @param WP_REST_Request $request
     * @return bool
     */
    public function webhook( WP_REST_Request $request ) {
        $params = $request->get_params();
        $params = dt_recursive_sanitize_array( $params );
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

        $type = str_contains( $params['From'], 'whatsapp' ) ? 'whatsapp' : 'sms';

        if ( class_exists( 'DT_Conversations_API' ) ) {

            $conversations_record = DT_Conversations_API::create_or_update_conversation_record(
                DT_Conversations_API::validate_and_format_phone( $params['From'] ),
                [ 'type' => 'phone' ],
            );
            if ( !is_wp_error( $conversations_record ) ){
                #DT_Posts::add_post_comment( 'conversations', $conversations_record['ID'], $phone_number_location, 'twilio', [], false, true );
                DT_Posts::add_post_comment( 'conversations', $conversations_record['ID'], $params['Body'], $type, [
                    'user_id'        => 0,
                    'comment_author' => DT_Conversations_API::validate_and_format_phone( $params['From'] ),
                ], false, false );


                do_action( 'dt_twilio_message_received', $type, $params, $conversations_record['ID'] );
            }
        } else {
            do_action( 'dt_twilio_message_received', $type, $params );
            return true;
        }

        return true;
    }

    public function dt_comment_created( $post_type, $post_id, $comment_id, $type ){
        //@todo check 24 window
        if ( $post_type === 'conversations' && $type === 'whatsapp' && class_exists( 'DT_Conversations_API' ) && Disciple_Tools_Twilio_API::is_enabled() ){
            $conversations_record = DT_Posts::get_post( 'conversations', $post_id );
            if ( !is_wp_error( $conversations_record ) ){
                $phone = $conversations_record['name'];
                $comment = get_comment( $comment_id );
                $message = $comment->comment_content;
                Disciple_Tools_Twilio_API::send_whatsapp( $phone, $message );
            }
        }
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
        add_action( 'dt_comment_created', [ $this, 'dt_comment_created' ], 10, 4 );

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
