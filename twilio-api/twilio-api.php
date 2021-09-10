<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

use Twilio\Rest\Client;

/**
 * Class Disciple_Tools_Twilio_API
 */
class Disciple_Tools_Twilio_API {

    public static $option_twilio_enabled = 'dt_twilio_enabled';
    public static $option_twilio_sid = 'dt_twilio_sid';
    public static $option_twilio_token = 'dt_twilio_token';
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
}
