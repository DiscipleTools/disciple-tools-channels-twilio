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

        register_rest_route(
            $namespace, '/upload_messaging_template', [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'upload_messaging_template' ],
                'permission_callback' => function ( WP_REST_Request $request ) {
                    return $this->has_permission();
                }
            ]
        );

        register_rest_route(
            $namespace, '/submit_messaging_template', [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'submit_messaging_template' ],
                'permission_callback' => function ( WP_REST_Request $request ) {
                    return $this->has_permission();
                }
            ]
        );

        register_rest_route(
            $namespace, '/reset_messaging_template', [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'reset_messaging_template' ],
                'permission_callback' => function ( WP_REST_Request $request ) {
                    return $this->has_permission();
                }
            ]
        );

        register_rest_route(
            $namespace, '/template_actions', [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'template_actions' ],
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

    public function upload_messaging_template( WP_REST_Request $request ): array {
        $response = [];

        $params = $request->get_params();

        if ( isset( $params['template_id'] ) ) {
            $template_id = $params['template_id'];

            // Execute content template creation on Twilio servers.
            $content_id = $this->handle_upload_messaging_template( $template_id, $params );

            // Capture whatever needs to be captured!
            if ( !empty( $content_id ) ) {
                $response['content_id'] = $content_id;
            }
        }

        return $response;
    }

    public function submit_messaging_template( WP_REST_Request $request ): array {
        $response = [];

        $params = $request->get_params();

        if ( isset( $params['template_id'] ) ) {
            $template_id = $params['template_id'];

            // Execute content template approval submission to Twilio & WhatsApp servers.
            $response['submitted'] = $this->handle_submit_messaging_template( $template_id );
        }

        return $response;
    }

    public function reset_messaging_template( WP_REST_Request $request ): array {
        $response = [];

        $params = $request->get_params();

        if ( isset( $params['template_id'] ) ) {
            $template_id = $params['template_id'];

            // Execute content template deletion on Twilio & WhatsApp servers.
            $reset = Disciple_Tools_Twilio_API::delete_messaging_template( $template_id );

            // If reset, ensure any corresponding content ids are removed.
            if ( $reset ) {
                $messaging_templates_settings = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_messaging_templates, [] );
                if ( isset( $messaging_templates_settings[ $template_id ]['content_id'] ) ) {
                    unset( $messaging_templates_settings[ $template_id ]['content_id'] );
                    Disciple_Tools_Twilio_API::set_option( Disciple_Tools_Twilio_API::$option_twilio_messaging_templates, $messaging_templates_settings );
                }
            }

            $response['reset'] = $reset;
        }

        return $response;
    }

    public function template_actions( WP_REST_Request $request ): array {
        $response = [];

        $params = $request->get_params();

        if ( isset( $params['action'] ) ) {
            $action = $params['action'];
            $processed_counter = 0;

            // Fetch the current state of template settings.
            $messaging_templates = Disciple_Tools_Twilio_API::list_messaging_templates() ?? [];
            $messaging_templates_statuses = Disciple_Tools_Twilio_API::list_messaging_templates_statuses( [ 'avoid_duplicates' => true ] ) ?? [];
            $messaging_templates_settings = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_messaging_templates, [] );

            foreach ( $messaging_templates as $template_id => $template ) {
                if ( isset( $template['enabled'] ) && $template['enabled'] && !empty( $template['content_template'] ) ) {

                    // Determine if template can be uploaded or submitted.
                    $can_upload = ( !isset( $messaging_templates_settings[ $template_id ] ) || !isset( $messaging_templates_settings[ $template_id ]['content_id'] ) );

                    $can_submit = false;
                    if ( isset( $messaging_templates_settings[ $template_id ]['content_id'] ) && isset( $messaging_templates_statuses[ $messaging_templates_settings[ $template_id ]['content_id'] ] ) ) {
                        $content = $messaging_templates_statuses[ $messaging_templates_settings[ $template_id ]['content_id'] ];

                        // Determine content's current status and subsequent actions to be made available.
                        $can_submit = ( $content['approval_status']['status'] === 'unsubmitted' );
                    }

                    // Determine bulk auto action to be carried out across relevant templates.
                    switch ( $action ) {
                        case 'upload':
                            if ( $can_upload && !empty( $this->handle_upload_messaging_template( $template_id ) ) ) {
                                $processed_counter++;
                            }
                            break;
                        case 'submit':
                            if ( $can_submit && $this->handle_submit_messaging_template( $template_id ) ) {
                                $processed_counter++;
                            }
                            break;
                    }
                }
            }

            $response['action'] = $action;
            $response['processed_count'] = $processed_counter;
        }

        return $response;
    }

    private function handle_upload_messaging_template( $template_id, $args = [] ) {
        $content_id = null;

        if ( isset( $template_id ) ) {

            // Execute content template creation on Twilio servers.
            $content_id = Disciple_Tools_Twilio_API::upload_messaging_template( $template_id );

            // If valid and auto save true, capture newly created content id within template settings.
            if ( !empty( $content_id ) && ( $args['auto_save'] ?? true ) ) {
                $messaging_templates_settings = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_messaging_templates, [] );

                if ( !isset( $messaging_templates_settings[ $template_id ] ) ) {
                    $messaging_templates_settings[ $template_id ] = [];
                }

                $messaging_templates_settings[ $template_id ]['content_id'] = $content_id;
                Disciple_Tools_Twilio_API::set_option( Disciple_Tools_Twilio_API::$option_twilio_messaging_templates, $messaging_templates_settings );
            }
        }

        return $content_id;
    }

    private function handle_submit_messaging_template( $template_id ): bool {
        $submitted = false;

        if ( isset( $template_id ) ) {

            // Execute content template approval submission to Twilio & WhatsApp servers.
            $submitted = Disciple_Tools_Twilio_API::submit_messaging_template( $template_id );
        }

        return $submitted;
    }
}

Disciple_Tools_Twilio_Endpoints::instance();
