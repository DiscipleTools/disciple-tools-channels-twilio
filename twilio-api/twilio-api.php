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
    public static $option_twilio_contact_field = 'dt_twilio_contact_field';

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
            $incoming_phone_numbers = $twilio->incomingPhoneNumbers->read( [], 20 );

            // Iterate over results
            if ( ! empty( $incoming_phone_numbers ) ) {
                foreach ( $incoming_phone_numbers as $number ) {

                    // Capture number details
                    if ( isset( $number->sid ) && isset( $number->phoneNumber ) && isset( $number->friendlyName ) ) {
                        $phone_numbers[] = [
                            'id'     => $number->sid,
                            'number' => $number->phoneNumber,
                            'name'   => $number->friendlyName
                        ];
                    }
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
                    if ( isset( $service->sid ) && isset( $service->friendlyName ) ) {
                        $msg_services[] = [
                            'id'   => $service->sid,
                            'name' => $service->friendlyName
                        ];
                    }
                }
            }
        } catch ( Exception $e ) {
            return [];
        }

        return $msg_services;
    }

    public static function send( $user_id, $message ) {
        if ( ! self::has_credentials() ) {
            return false;
        }

        // Ensure required params are present
        if ( empty( $user_id ) || empty( $message ) ) {
            return false;
        }

        try {

            // Fetch associated user's contacts record id
            $contact_id = self::get_contact_for_user( $user_id );
            if ( ! empty( $contact_id ) && ! is_wp_error( $contact_id ) ) {

                // Fetch and split contact field option; which has the following format: [post_type]+[field_id]
                $option_contact_field = self::get_option( self::$option_twilio_contact_field );
                $post_type            = explode( '+', $option_contact_field )[0];
                $field_id             = explode( '+', $option_contact_field )[1];

                // Fetch contacts post based on identified option post_type
                $user_contact = DT_Posts::get_post( $post_type, $contact_id, true, false );
                if ( ! empty( $user_contact ) && ! is_wp_error( $user_contact ) && isset( $user_contact[ $field_id ] ) ) {

                    // As field is expected to be of type communication_channel; then structure to be an array of phone numbers!
                    if ( is_array( $user_contact[ $field_id ] ) ) {

                        // Establish Twilio client session
                        $twilio = new Client( self::get_option( self::$option_twilio_sid ), self::get_option( self::$option_twilio_token ) );

                        // Fetch messaging service to be used
                        $messaging_service = $twilio->messaging->v1->services( self::get_option( self::$option_twilio_msg_service_id ) )->fetch();

                        // Determine required dispatch service to be used
                        $service         = '';
                        $current_service = self::get_option( self::$option_twilio_service );
                        switch ( $current_service ) {
                            case 'whatsapp':
                                $service = 'whatsapp:';
                                break;
                            default:
                                $service = ''; // Default to SMS
                                break;
                        }

                        // Iterate phone numbers
                        foreach ( $user_contact[ $field_id ] as $phone ) {
                            if ( ! empty( $phone['value'] ) ) {

                                // Dispatch message...!
                                $message = $twilio->messages->create(
                                    $service . $phone['value'],
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
                }
            }
        } catch ( Exception $e ) {
            return new WP_Error( __FUNCTION__, $e->getMessage(), [ 'status' => $e->getCode() ] );
        }

        return false;
    }

    private static function get_contact_for_user( $user_id ) {
        $contact_id = get_user_option( "corresponds_to_contact", $user_id );

        if ( ! empty( $contact_id ) && get_post( $contact_id ) ) {
            return (int) $contact_id;
        }
        $args     = [
            'post_type'  => 'contacts',
            'relation'   => 'AND',
            'meta_query' => [
                [
                    'key'   => "corresponds_to_user",
                    "value" => $user_id
                ],
                [
                    'key'   => "type",
                    "value" => "user"
                ],
            ],
        ];
        $contacts = new WP_Query( $args );
        if ( isset( $contacts->post->ID ) ) {
            update_user_option( $user_id, "corresponds_to_contact", $contacts->post->ID );

            return (int) $contacts->post->ID;
        } else {
            return null;
        }
    }
}
