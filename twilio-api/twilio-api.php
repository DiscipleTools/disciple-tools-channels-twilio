<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

use Twilio\Rest\Client;

/**
 * Class Disciple_Tools_Twilio_API
 */
class Disciple_Tools_Twilio_API {

    public static $channel_twilio_id = 'dt_channel_twilio';
    public static $channel_twilio_name = 'Twilio Sending Channel';

    public static $option_twilio_enabled = 'dt_twilio_enabled';
    public static $option_twilio_sid = 'dt_twilio_sid';
    public static $option_twilio_token = 'dt_twilio_token';
    public static $option_twilio_service = 'dt_twilio_service';
    public static $option_twilio_msg_service_id = 'dt_twilio_msg_service_id';
    public static $option_twilio_number_id = 'dt_twilio_number_id';

    public static function is_enabled(): bool {
        $enabled = get_option( self::$option_twilio_enabled );

        return isset( $enabled ) && boolval( $enabled ) === true;
    }

    public static function has_credentials(): bool {
        return self::has_option( self::$option_twilio_sid ) && self::has_option( self::$option_twilio_token );
    }

    public static function has_option( $option ): bool {
        return ! empty( get_option( $option ) );
    }

    public static function get_option( $option ): string {
        return get_option( $option );
    }

    public static function set_option( $option, $value ) {
        update_option( $option, $value );
    }

    public static function list_services(): array {
        return [
            [
                'id'   => 'sms',
                'name' => 'SMS'
            ],
            [
                'id'   => 'whatsapp',
                'name' => 'WhatsApp'
            ]
        ];
    }

    public static function list_phone_numbers(): array {
        if ( ! self::has_credentials() ) {
            return [];
        }

        $phone_numbers = [];

        try {

            // Establish Twilio client session
            $twilio = new Client( self::get_option( self::$option_twilio_sid ), self::get_option( self::$option_twilio_token ) );

            // Fetch available incoming phone numbers
            // phpcs:disable
            $incoming_phone_numbers = $twilio->incomingPhoneNumbers->read( [], 20 );
            // phpcs:enable

            // Iterate over results
            if ( ! empty( $incoming_phone_numbers ) ) {
                foreach ( $incoming_phone_numbers as $number ) {

                    // Capture number details
                    // phpcs:disable
                    if ( isset( $number->sid ) && isset( $number->phoneNumber ) && isset( $number->friendlyName ) ) {
                        $phone_numbers[] = [
                            'id'     => $number->sid,
                            'number' => $number->phoneNumber,
                            'name'   => $number->friendlyName
                        ];
                    }
                    // phpcs:enable
                }
            }
        } catch ( Exception $e ) {
            return [];
        }

        return $phone_numbers;
    }

    public static function list_messaging_services(): array {
        if ( ! self::has_credentials() ) {
            return [];
        }

        $msg_services = [];

        try {

            // Establish Twilio client session
            $twilio = new Client( self::get_option( self::$option_twilio_sid ), self::get_option( self::$option_twilio_token ) );

            // Fetch available messaging service
            $services = $twilio->messaging->v1->services->read( 20 );

            // Iterate over results
            if ( ! empty( $services ) ) {
                foreach ( $services as $service ) {

                    // Capture service details
                    // phpcs:disable
                    if ( isset( $service->sid ) && isset( $service->friendlyName ) ) {
                        $msg_services[] = [
                            'id'   => $service->sid,
                            'name' => $service->friendlyName
                        ];
                    }
                    // phpcs:enable
                }
            }
        } catch ( Exception $e ) {
            return [];
        }

        return $msg_services;
    }

    public static function send( $user, $message, $args = [] ) {
        if ( ! self::has_credentials() ) {
            return false;
        }

        // Ensure required params are present
        if ( empty( $user ) || empty( $message ) ) {
            return false;
        }

        try {

            // Fetch phone numbers to be used during dispatch
            $phone_numbers = self::get_user_phone_numbers( $user );

            // A quick sanity check prior to iteration!
            if ( ! empty( $phone_numbers ) ) {

                // Establish Twilio client session
                $twilio = new Client( self::get_option( self::$option_twilio_sid ), self::get_option( self::$option_twilio_token ) );

                // Fetch messaging service to be used
                $messaging_service = $twilio->messaging->v1->services( self::get_option( self::$option_twilio_msg_service_id ) )->fetch();

                // Determine required dispatch service to be used
                if ( ! empty( $args ) && isset( $args['service'] ) ) {
                    $current_service = $args['service'];
                } else {
                    $current_service = self::get_option( self::$option_twilio_service );
                }
                // Reformat service syntax
                switch ( $current_service ) {
                    case 'whatsapp':
                        $service = 'whatsapp:';
                        break;
                    default:
                        $service = ''; // Default to SMS
                        break;
                }

                // Iterate over phone numbers
                foreach ( $phone_numbers as $phone ) {
                    if ( ! empty( $phone ) ) {

                        // Dispatch message...!
                        $message_result = $twilio->messages->create(
                            $service . $phone,
                            [
                                'body'                => $message,
                                'messagingServiceSid' => $messaging_service->sid
                            ]
                        );
                    }
                }

                // Return true if this point has been reached
                return true;
            }
        } catch ( Exception $e ) {
            return new WP_Error( __FUNCTION__, $e->getMessage(), [ 'status' => $e->getCode() ] );
        }

        return false;
    }

    public static function get_user_phone_numbers( $user ): array {
        $field        = [];
        $user_contact = null;

        switch ( self::determine_assigned_user_type( $user ) ) {
            case 'users':

                // Attempt to fetch phone numbers in a specific order.
                $wp_user = get_user_by( 'id', $user->dt_id );
                if ( !empty( $wp_user ) ) {

                    // Compares the site settings in the config area with the fields available in the user meta table.
                    $dt_user_fields = dt_build_user_fields_display( get_user_meta( $wp_user->ID ) );

                    // Check for numbers in specific order.
                    foreach ( $dt_user_fields as $dt_field ) {
                        if ( empty( $field ) ) {
                            if ( $dt_field['key'] === 'dt_user_personal_phone' && !empty( $dt_field['value'] ) ) {
                                $field[] = $dt_field['value'];

                            } elseif ( $dt_field['key'] === 'dt_user_work_phone' && !empty( $dt_field['value'] ) ) {
                                $field[] = $dt_field['value'];
                            }
                        }
                    }
                }

                // Check contact record, if no phone numbers found at the user level.
                if ( empty( $field ) ) {
                    $user_contact = DT_Posts::get_post( 'contacts', self::get_contact_id_by_user_id( $user->dt_id ), true, false );
                }
                break;

            case 'contacts':
                $user_contact = DT_Posts::get_post( 'contacts', $user->dt_id, true, false );
                break;
        }

        // Assuming we have a valid user contact record hit, proceed with extraction of phone numbers!
        if ( empty( $field ) && ! empty( $user_contact ) && ! is_wp_error( $user_contact ) && isset( $user_contact['contact_phone'] ) ) {
            foreach ( $user_contact['contact_phone'] as $phone ) {

                // To avoid spamming all associated numbers, only proceed with the first number encountered.
                if ( empty( $field ) && ! empty( $phone['value'] ) ) {
                    $field[] = $phone['value'];
                }
            }
        }

        return $field;
    }

    private static function determine_assigned_user_type( $user ): string {
        if ( in_array( strtolower( trim( $user->type ) ), [ 'user', 'member', 'contact' ] ) ) {
            switch ( strtolower( trim( $user->sys_type ) ) ) {
                case 'wp_user':
                    return 'users';
                case 'post':
                    return 'contacts';
            }
        }

        return '';
    }

    private static function get_contact_id_by_user_id( $user_id ): ?int {
        $contact_id = get_user_option( 'corresponds_to_contact', $user_id );

        if ( ! empty( $contact_id ) && get_post( $contact_id ) ) {
            return (int) $contact_id;
        }
        $args     = [
            'post_type'  => 'contacts',
            'relation'   => 'AND',
            'meta_query' => [
                [
                    'key'   => 'corresponds_to_user',
                    'value' => $user_id
                ],
                [
                    'key'   => 'type',
                    'value' => 'user'
                ],
            ],
        ];
        $contacts = new WP_Query( $args );
        if ( isset( $contacts->post->ID ) ) {
            update_user_option( $user_id, 'corresponds_to_contact', $contacts->post->ID );

            return $contacts->post->ID;
        } else {
            return null;
        }
    }
}
