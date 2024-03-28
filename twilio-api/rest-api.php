<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

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


    public function endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();
        $headers = $request->get_headers();

        dt_write_log( $params );
        dt_write_log( $headers );

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
