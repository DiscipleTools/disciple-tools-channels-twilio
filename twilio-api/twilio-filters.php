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

add_filter( 'dt_magic_link_template_messages', 'dt_twilio_magic_link_template_messages', 10, 1 );
function dt_twilio_magic_link_template_messages( $messages ): array {

    // Fetch default content templates.
    $default_templates = Disciple_Tools_Twilio_API::list_messaging_templates();

    // Hydrate default templates with fetched content ids.
    $twilio_template_statuses = Disciple_Tools_Twilio_API::list_messaging_templates_statuses();
    foreach ( $default_templates as $template_id => $template ) {
        if ( isset( $template['name'] ) ) {
            foreach ( $twilio_template_statuses as $status ) {
                if ( $status['name'] === $template['name'] ) {
                    $template['content_id'] = $status['id'];
                    $messages[ $template_id ] = $template;
                    break;
                }
            }
        }
    }

    return $messages;
}

function dt_twilio_sending_channel_send( $params, $args = [] ): bool {
    if ( !empty( $params['template_message_id'] ) ) {
        $template_message_id = $params['template_message_id'];

        // Fetch template message, assuming we have valid parameter placeholders.
        if ( !empty( $params['placeholders'] ) ) {
            $placeholders = $params['placeholders'];
            $template_messages = apply_filters( 'dt_magic_link_template_messages', [] );

            if ( isset( $template_messages[ $template_message_id ] ) ) {
                $template_message = $template_messages[ $template_message_id ];

                // Capture corresponding content sid.
                if ( isset( $template_message['content_id'] ) ) {
                    $args['content_sid'] = $template_message['content_id'];
                }

                // Convert placeholder mappings to a JSON content variables string.
                if ( isset( $template_message['ml_msg_placeholder_mappings'] ) ) {
                    $args['content_variables'] = str_replace(
                        [
                            '{{name}}',
                            '{{link}}',
                            '{{time}}',
                            '{{time_relative}}'
                        ],
                        [
                            $placeholders['name'],
                            $placeholders['link'],
                            $placeholders['time'],
                            $placeholders['time_relative']
                        ],
                        json_encode( $template_message['ml_msg_placeholder_mappings'] )
                    );
                }
            }
        }
    }

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

    $channel_key_sms = 'sms';
    $channel_key_whatsapp = 'whatsapp';

    if ( channel_notification_enabled() ) {

        // Ensure required sections are present.
        if ( !isset( $notifications['channels'] ) ){
            $notifications['channels'] = [];
        }
        if ( !isset( $notifications['types'] ) ){
            $notifications['types'] = [];
        }

        // Determine the enabled state of the various channel keys.
        $sms_enabled = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_service_sms_enabled, false );
        $whatsapp_enabled = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_service_whatsapp_enabled, false );

        // Create dummy data.
        if ( $sms_enabled ) {
            $notifications['channels'][$channel_key_sms] = [
                'label' => __( 'SMS', 'disciple_tools' )
            ];
        } else {
            unset( $notifications['channels'][$channel_key_sms] );
        }

        if ( $whatsapp_enabled ) {
            $notifications['channels'][$channel_key_whatsapp] = [
                'label' => __( 'WhatsApp', 'disciple_tools' )
            ];
        } else {
            unset( $notifications['channels'][$channel_key_whatsapp] );
        }

        // As well as creating new types; append to existing ones.
        foreach ( $notifications['types'] as $type => $type_settings ){

            // Only insert, if not already set and default to false.
            if ( !isset( $notifications['types'][$type][$channel_key_sms] ) ){
                $notifications['types'][$type][$channel_key_sms] = false;
            }
            if ( !$sms_enabled ) {
                unset( $notifications['types'][$type][$channel_key_sms] );
            }

            if ( !isset( $notifications['types'][$type][$channel_key_whatsapp] ) ) {
                $notifications['types'][$type][$channel_key_whatsapp] = false;
            }
            if ( !$whatsapp_enabled ) {
                unset( $notifications['types'][$type][$channel_key_whatsapp] );
            }
        }
    } else {

        // Ensure to unset channels and corresponding types.
        unset( $notifications['channels'][$channel_key_sms] );
        unset( $notifications['channels'][$channel_key_whatsapp] );

        foreach ( $notifications['types'] ?? [] as $type => $type_settings ) {
            unset( $notifications['types'][$type][$channel_key_sms] );
            unset( $notifications['types'][$type][$channel_key_whatsapp] );
        }
    }

    return $notifications;
}

add_filter( 'dt_communication_channels', 'dt_communication_channels', 10, 1 );
function dt_communication_channels( $channels ) {

    $channel_key_sms = 'sms';
    $channel_key_whatsapp = 'whatsapp';

    if ( channel_notification_enabled() ) {

        if ( empty( $channels ) ){
            $channels = [];
        }

        // Determine the enabled state of the various channel keys.
        $sms_enabled = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_service_sms_enabled, false );
        $whatsapp_enabled = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_service_whatsapp_enabled, false );

        if ( $sms_enabled ) {
            $channels[] = $channel_key_sms;
        } else {
            unset( $channels[$channel_key_sms] );
        }

        if ( $whatsapp_enabled ) {
            $channels[] = $channel_key_whatsapp;
        } else {
            unset( $channels[$channel_key_whatsapp] );
        }
    } else {

        // Ensure to unset channels.
        unset( $channels[$channel_key_sms] );
        unset( $channels[$channel_key_whatsapp] );
    }

    return $channels;
}

add_action( 'send_notification_on_channels', 'send_notification_on_channels', 10, 4 );
function send_notification_on_channels( $user_id, $notification, $notification_type, $already_sent = [] ) {
    if ( empty( $user_id ) || empty( $notification ) ) {
        return;
    }
    if ( !channel_notification_enabled() || !isset( $notification['post_id'] ) ) {
        return;
    }
    $post_id = $notification['post_id'];


    // Obtain handle to user meta data and prep keys.
    $user_meta = get_user_meta( $user_id );

    $sms_channel_notification_key = ( $notification_type ?? '' ) . '_sms';
    $whatsapp_channel_notification_key = ( $notification_type ?? '' ) . '_whatsapp';

    // Determine the enabled state of the various channel keys.
    $sms_enabled = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_service_sms_enabled, false );
    $whatsapp_enabled = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_service_whatsapp_enabled, false );

    // Capture message and build associated link; ensuring to remove all embedded carriage returns.
    $message = str_replace( "\r\n", '', Disciple_Tools_Notifications::get_notification_message_html( $notification, false ) );
    $message = str_replace( "\n", '', $message );
    $link = home_url( '/' ) . get_post_type( $post_id ) . '/' . $post_id;

    // Process sms channels.
    if ( $sms_enabled && isset( $user_meta[$sms_channel_notification_key] ) && ( boolval( $user_meta[$sms_channel_notification_key][0] ) === true ) ) {
        if ( isset( $user_meta['dt_user_work_phone'][0] ) && !empty( trim( $user_meta['dt_user_work_phone'][0] ) ) ) {
            $sent = Disciple_Tools_Twilio_API::send_sms( $user_meta['dt_user_work_phone'][0], $message );
        }
    }

    // Process whatsapp channels.
    if ( $whatsapp_enabled && isset( $user_meta[$whatsapp_channel_notification_key] ) && ( boolval( $user_meta[$whatsapp_channel_notification_key][0] ) === true ) ) {
        if ( isset( $user_meta['dt_user_work_whatsapp'][0] ) && !empty( trim( $user_meta['dt_user_work_whatsapp'][0] ) ) ) {
            $sent = Disciple_Tools_Twilio_API::send_dt_notification_template( $user_meta['dt_user_work_whatsapp'][0], [
                '1' => get_bloginfo( 'name' ),
                '2' => $message,
                '3' => $link
            ] );
        }
    }
}

add_filter( 'dt_communication_channel_notification_endpoints_text', 'dt_communication_channel_notification_endpoints_text', 10, 2 );
function dt_communication_channel_notification_endpoints_text( $endpoints, $user_id ) {
    if ( empty( $user_id ) ) {
        return $endpoints;
    }

    $dt_user_meta = get_user_meta( $user_id );
    if ( Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_service_sms_enabled, false ) ) {
        $endpoints[] = sprintf( _x( 'SMS notifications will be sent to: %s', 'SMS notifications will be sent to: [work number]', 'disciple_tools' ), ( isset( $dt_user_meta['dt_user_work_phone'][0] ) && !empty( trim( $dt_user_meta['dt_user_work_phone'][0] ) ) ? $dt_user_meta['dt_user_work_phone'][0] : esc_html( 'none set', 'disciple-tools-channels-twilio' ) ) );
    }

    if ( Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_service_whatsapp_enabled, false ) ) {
        $endpoints[] = sprintf( _x( 'Whatsapp notifications will be sent to: %s', 'Whatsapp notifications will be sent to: [whatsapp number]', 'disciple_tools' ), ( isset( $dt_user_meta['dt_user_work_whatsapp'][0] ) && !empty( trim( $dt_user_meta['dt_user_work_whatsapp'][0] ) ) ? $dt_user_meta['dt_user_work_whatsapp'][0] : esc_html( 'none set', 'disciple-tools-channels-twilio' ) ) );
    }

    return $endpoints;
}
