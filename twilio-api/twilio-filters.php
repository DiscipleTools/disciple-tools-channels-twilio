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

/**
 * Integrate with D.T. Notification Channel Framework.
 */

function channel_notification_enabled(): bool {
    return class_exists( 'Disciple_Tools_Twilio_API', false ) && Disciple_Tools_Twilio_API::is_enabled() && Disciple_Tools_Twilio_API::has_credentials();
}

add_filter( 'dt_get_site_notification_options', 'dt_get_site_notification_options', 10, 1 );
function dt_get_site_notification_options( $notifications ) {

    if ( channel_notification_enabled() ) {

        $channel_key_sms = 'sms';
        $channel_key_whatsapp = 'whatsapp';

        // Ensure required sections are present.
        if ( !isset( $notifications['channels'] ) ){
            $notifications['channels'] = [];
        }
        if ( !isset( $notifications['types'] ) ){
            $notifications['types'] = [];
        }

        // Create dummy data.
        $notifications['channels'][$channel_key_sms] = [
            'label' => __( 'SMS', 'disciple_tools' )
        ];
        $notifications['channels'][$channel_key_whatsapp] = [
            'label' => __( 'WhatsApp', 'disciple_tools' )
        ];

        // As well as creating new types; append to existing ones.
        foreach ( $notifications['types'] as $type => $type_settings ){

            // Only insert, if not already set and default to false.
            if ( !isset( $notifications['types'][$type][$channel_key_sms] ) ){
                $notifications['types'][$type][$channel_key_sms] = false;
            }
            if ( !isset( $notifications['types'][$type][$channel_key_whatsapp] ) ) {
                $notifications['types'][$type][$channel_key_whatsapp] = false;
            }
        }
    } else {

        // Ensure to unset channels and corresponding types.
        unset( $notifications['channels']['sms'] );
        unset( $notifications['channels']['whatsapp'] );

        foreach ( $notifications['types'] ?? [] as $type => $type_settings ) {
            unset( $notifications['types'][$type]['sms'] );
            unset( $notifications['types'][$type]['whatsapp'] );
        }
    }

    return $notifications;
}

add_filter( 'dt_communication_channels', 'dt_communication_channels', 10, 1 );
function dt_communication_channels( $channels ) {

    if ( channel_notification_enabled() ) {

        if ( empty( $channels ) ){
            $channels = [];
        }
        $channels[] = 'sms';
        $channels[] = 'whatsapp';

    } else {

        // Ensure to unset channels.
        unset( $channels['sms'] );
        unset( $channels['whatsapp'] );
    }

    return $channels;
}

add_action( 'send_notification_on_channels', 'send_notification_on_channels', 10, 4 );
function send_notification_on_channels( $user_id, $notification, $notification_type, $already_sent = [] ) {

    if ( channel_notification_enabled() ) {

        // Obtain handle to user meta data and prep keys.
        $user_meta = get_user_meta( $user_id );
        $sms_channel_notification_key = ( $notification_type ?? '' ) . '_sms';
        $whatsapp_channel_notification_key = ( $notification_type ?? '' ) . '_whatsapp';

        // Process sms channels.
        if ( isset( $user_meta[$sms_channel_notification_key] ) && ( boolval( $user_meta[$sms_channel_notification_key][0] ) === true ) ){
            $message = Disciple_Tools_Notifications::get_notification_message_html( $notification, false );
            dt_twilio_direct_send( $user_id, 'wp_user', $message, [ 'service' => 'sms' ] );
        }

        // Process whatsapp channels.
        if ( isset( $user_meta[$whatsapp_channel_notification_key] ) && ( boolval( $user_meta[$whatsapp_channel_notification_key][0] ) === true ) ) {
            $message = Disciple_Tools_Notifications::get_notification_message_html( $notification, false );
            dt_twilio_direct_send( $user_id, 'wp_user', $message, [ 'service' => 'whatsapp' ] );
        }
    }
}
