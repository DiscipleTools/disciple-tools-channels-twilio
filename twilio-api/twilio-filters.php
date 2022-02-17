<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

add_filter( 'dt_sending_channels', 'dt_twilio_sending_channels', 10, 1 );
function dt_twilio_sending_channels( $channels ) {
    $channels[] = [
        'id'      => Disciple_Tools_Twilio_API::$channel_twilio_id,
        'name'    => Disciple_Tools_Twilio_API::$channel_twilio_name,
        'enabled' => Disciple_Tools_Twilio_API::is_enabled(),
        'send'    => function ( $params = [] ) {
            return dt_twilio_sending_channel_send( $params );
        }
    ];

    return $channels;
}

function dt_twilio_sending_channel_send( $params, $args = [] ): bool {
    return Disciple_Tools_Twilio_API::is_enabled() && Disciple_Tools_Twilio_API::send( $params['user'], $params['message'], $args );
}

/**
 * Specify direct twilio send action; which is driven by the following parameters:
 *
 * - id:    Assigned WP user id or post id, depending on type value.
 * - type:  System type; which must be one of the following:
 *              - wp_user
 *              - post
 * - msg:   Actual message to be sent; which must adhere to the pre-defined Twilio
 *          message template shape. For example:
 *
 *          Hi, Please update records -> {{link}} -> Link will expire on {{time}}
 *
 *          {{...}} placeholders to be substituted with actual values.
 * - args:  Ability to specify option overrides during sending. Currently, the following
 *          overrides are supported:
 *              - service: Which of the following twilio services types are to be adopted:
 *                  - sms
 *                  - whatsapp
 */

add_action( 'dt_twilio_send', 'dt_twilio_direct_send', 10, 4 );
function dt_twilio_direct_send( $id, $type, $msg, $args = [] ): bool {
    if ( isset( $id, $type, $msg ) ) {
        return dt_twilio_sending_channel_send( [
            'user'    => (object) [
                'dt_id'    => $id,
                'sys_type' => $type,
                'type'     => 'member'
            ],
            'message' => $msg
        ], $args );
    }

    return false;
}

function dt_twilio_configured(): bool {
    return Disciple_Tools_Twilio_API::has_credentials() && Disciple_Tools_Twilio_API::has_option( Disciple_Tools_Twilio_API::$option_twilio_msg_service_id );
}
