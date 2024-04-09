<?php
if ( !defined( 'ABSPATH' ) ){
    exit;
} // Exit if accessed directly.


class Disciple_Tools_Twilio_Endpoints {

    /**
     * @todo Set the permissions your endpoint needs
     * @link https://github.com/DiscipleTools/Documentation/blob/master/theme-core/capabilities.md
     * @var string[]
     */
    public $permissions = [ 'manage_dt' ];

    private static $_instance = null;

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    public function has_permission() {
        $pass = false;
        foreach ( $this->permissions as $permission ) {
            if ( current_user_can( $permission ) ) {
                $pass = true;
            }
        }

        return $pass;
    }

    /**
     * @todo define the name of the $namespace
     * @todo define the name of the rest route
     * @todo defne method (CREATABLE, READABLE)
     * @todo apply permission strategy. '__return_true' essentially skips the permission check.
     */
    //See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
    public function add_api_routes() {
        $namespace = 'disciple_tools_channels_twilio/v1';

        register_rest_route(
            $namespace, '/list_phone_numbers', [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'list_phone_numbers' ],
                'permission_callback' => function ( WP_REST_Request $request ) {
                    return $this->has_permission();
                }
            ]
        );
    }

    public function list_phone_numbers( WP_REST_Request $request ): array {
        $response = [];

        $params = $request->get_params();

        // Handle accordingly, by specified list type.
        switch ( $params['type'] ?? '' ) {
            case 'messaging':
                if ( isset( $params['msg_service_id'] ) ) {
                    $response['messaging'] = Disciple_Tools_Twilio_API::list_messaging_services_phone_numbers( $params['msg_service_id'] );
                }
                break;
            case 'incoming':
                $response['incoming'] = Disciple_Tools_Twilio_API::list_incoming_phone_numbers();
                break;
        }

        return $response;
    }
}

Disciple_Tools_Twilio_Endpoints::instance();
