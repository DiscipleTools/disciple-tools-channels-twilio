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
    public static $option_twilio_service_sms_enabled = 'dt_twilio_service_sms_enabled';
    public static $option_twilio_service_whatsapp_enabled = 'dt_twilio_service_whatsapp_enabled';
    public static $option_twilio_msg_service_id = 'dt_twilio_msg_service_id';
    public static $option_twilio_number_id = 'dt_twilio_number_id';
    public static $option_twilio_assigned_numbers_sms_id = 'dt_twilio_assigned_numbers_sms_id';
    public static $option_twilio_assigned_numbers_whatsapp_id = 'dt_twilio_assigned_numbers_whatsapp_id';
    public static $option_twilio_messaging_templates = 'dt_twilio_messaging_templates';

    public static function fetch_endpoint_list_phone_numbers_url(): string {
        return trailingslashit( site_url() ) . 'wp-json/disciple_tools_channels_twilio/v1/list_phone_numbers';
    }

    public static function fetch_endpoint_upload_messaging_template_url(): string {
        return trailingslashit( site_url() ) . 'wp-json/disciple_tools_channels_twilio/v1/upload_messaging_template';
    }

    public static function fetch_endpoint_submit_messaging_template_url(): string {
        return trailingslashit( site_url() ) . 'wp-json/disciple_tools_channels_twilio/v1/submit_messaging_template';
    }

    public static function fetch_endpoint_reset_messaging_template_url(): string {
        return trailingslashit( site_url() ) . 'wp-json/disciple_tools_channels_twilio/v1/reset_messaging_template';
    }

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

    public static function get_option( $option, $default_value = null ) {
        return get_option( $option, $default_value );
    }

    public static function set_option( $option, $value ): bool {
        return update_option( $option, $value );
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

    public static function list_messaging_templates(): array {
        $messaging_templates = [
            'hello_world' => [
                'id' => 'hello_world',
                'name' => 'Hello World',
                'type' => 'whatsapp',
                'enabled' => true,
                'content_category' => 'UTILITY',
                'content_template' => [
                    'friendly_name' => 'Hello World',
                    'language' => 'en',
                    'variables' => [
                        '1' => '-- name --'
                    ],
                    'types' => [
                        'twilio/text' => [
                            'body' => 'Hi {{1}}, \n Hello World!'
                        ]
                    ]
                ]
            ],
            'created' => [
                'id' => 'created',
                'name' => 'Created',
                'type' => 'whatsapp',
                'enabled' => true,
                'content_category' => 'UTILITY',
                'content_template' => [
                    'friendly_name' => 'Created',
                    'language' => 'en',
                    'variables' => [
                        '1' => '-- name --',
                        '2' => '-- link --'
                    ],
                    'types' => [
                        'twilio/text' => [
                            'body' => 'Disciple.Tools notification: {{1}} was created and assigned to you. \nClick {{2}} to update the record.'
                        ]
                    ]
                ]
            ],
            'assigned_to' => [
                'id' => 'assigned_to',
                'name' => 'Assigned To',
                'type' => 'whatsapp',
                'enabled' => true,
                'content_category' => 'UTILITY',
                'content_template' => [
                    'friendly_name' => 'Assigned To',
                    'language' => 'en',
                    'variables' => [
                        '1' => '-- name --',
                        '2' => '-- link --'
                    ],
                    'types' => [
                        'twilio/text' => [
                            'body' => 'Disciple.Tools notification: You have been assigned: {{1}}. \nClick {{2}} to update the record.'
                        ]
                    ]
                ]
            ],
            'assigned_to_other' => [
                'id' => 'assigned_to_other',
                'name' => 'Assigned To Other',
                'type' => 'whatsapp',
                'enabled' => true,
                'content_category' => 'UTILITY',
                'content_template' => [
                    'friendly_name' => 'Assigned To Other',
                    'language' => 'en',
                    'variables' => [
                        '1' => '-- name1 --',
                        '2' => '-- name2 --',
                        '3' => '-- name3 --',
                        '4' => '-- link --'
                    ],
                    'types' => [
                        'twilio/text' => [
                            'body' => 'Disciple.Tools notification: {{1}} assigned {{2}} to {{3}}. \nClick {{4}} to update the record.'
                        ]
                    ]
                ]
            ],
            'share' => [
                'id' => 'share',
                'name' => 'Share',
                'type' => 'whatsapp',
                'enabled' => true,
                'content_category' => 'UTILITY',
                'content_template' => [
                    'friendly_name' => 'Share',
                    'language' => 'en',
                    'variables' => [
                        '1' => '-- name1 --',
                        '2' => '-- name2 --',
                        '3' => '-- link --'
                    ],
                    'types' => [
                        'twilio/text' => [
                            'body' => 'Disciple.Tools notification: {{1}} shared {{2}} with you. \nClick {{3}} to update the record.'
                        ]
                    ]
                ]
            ],
            'mention' => [
                'id' => 'mention',
                'name' => '@Mentions',
                'type' => 'whatsapp',
                'enabled' => true,
                'content_category' => 'UTILITY',
                'content_template' => [
                    'friendly_name' => '@Mentions',
                    'language' => 'en',
                    'variables' => [
                        '1' => '-- name1 --',
                        '2' => '-- name2 --',
                        '3' => '-- mention --',
                        '4' => '-- link --'
                    ],
                    'types' => [
                        'twilio/text' => [
                            'body' => 'Disciple.Tools notification: {{1}} mentioned you on {{2}} saying: {{3}} \nClick {{4}} to update the record.'
                        ]
                    ]
                ]
            ],
            'comment' => [
                'id' => 'comment',
                'name' => 'New Comments',
                'type' => 'whatsapp',
                'enabled' => true,
                'content_category' => 'UTILITY',
                'content_template' => [
                    'friendly_name' => 'New Comments',
                    'language' => 'en',
                    'variables' => [
                        '1' => '-- name1 --',
                        '2' => '-- name2 --',
                        '3' => '-- comment --',
                        '4' => '-- link --'
                    ],
                    'types' => [
                        'twilio/text' => [
                            'body' => 'Disciple.Tools notification: {{1}} commented on {{2}} saying: {{3}} \nClick {{4}} to update the record.'
                        ]
                    ]
                ]
            ],
            'subassigned' => [
                'id' => 'subassigned',
                'name' => 'Sub-Assigned',
                'type' => 'whatsapp',
                'enabled' => true,
                'content_category' => 'UTILITY',
                'content_template' => [
                    'friendly_name' => 'Sub-Assigned',
                    'language' => 'en',
                    'variables' => [
                        '1' => '-- name1 --',
                        '2' => '-- name2 --',
                        '3' => '-- link --'
                    ],
                    'types' => [
                        'twilio/text' => [
                            'body' => 'Disciple.Tools notification: {{1}} subassigned {{2}} to you. \nClick {{3}} to update the record.'
                        ]
                    ]
                ]
            ],
            'milestone' => [
                'id' => 'milestone',
                'name' => 'Contact Milestones and Group Health metrics',
                'type' => 'whatsapp',
                'enabled' => true,
                'content_category' => 'UTILITY',
                'content_template' => [
                    'friendly_name' => 'Contact Milestones and Group Health metrics',
                    'language' => 'en',
                    'variables' => [
                        '1' => '-- name1 --',
                        '2' => '-- milestone --',
                        '3' => '-- name3 --',
                        '4' => '-- link --'
                    ],
                    'types' => [
                        'twilio/text' => [
                            'body' => 'Disciple.Tools notification: {{1}} added milestone {{2}} on {{3}}. \nClick {{4}} to update the record.'
                        ]
                    ]
                ]
            ],
            'requires_update' => [
                'id' => 'requires_update',
                'name' => 'Requires Update',
                'type' => 'whatsapp',
                'enabled' => true,
                'content_category' => 'UTILITY',
                'content_template' => [
                    'friendly_name' => 'Requires Update',
                    'language' => 'en',
                    'variables' => [
                        '1' => '-- name1 --',
                        '2' => '-- name2 --',
                        '3' => '-- link --'
                    ],
                    'types' => [
                        'twilio/text' => [
                            'body' => 'Disciple.Tools notification: @{{1}}, an update is requested on {{2}}. \nClick {{3}} to update the record.'
                        ]
                    ]
                ]
            ],
            'contact_info_update' => [
                'id' => 'contact_info_update',
                'name' => 'Contact Info Update',
                'type' => 'whatsapp',
                'enabled' => true,
                'content_category' => 'UTILITY',
                'content_template' => [
                    'friendly_name' => 'Contact Info Update',
                    'language' => 'en',
                    'variables' => [
                        '1' => '-- name1 --',
                        '2' => '-- name2 --',
                        '3' => '-- link --'
                    ],
                    'types' => [
                        'twilio/text' => [
                            'body' => 'Disciple.Tools notification: {{1}} modified contact details on {{2}}. \nClick {{3}} to update the record.'
                        ]
                    ]
                ]
            ],
            'assignment_declined' => [
                'id' => 'assignment_declined',
                'name' => 'Assignment Declined',
                'type' => 'whatsapp',
                'enabled' => true,
                'content_category' => 'UTILITY',
                'content_template' => [
                    'friendly_name' => 'Assignment Declined',
                    'language' => 'en',
                    'variables' => [
                        '1' => '-- name1 --',
                        '2' => '-- name2 --',
                        '3' => '-- link --'
                    ],
                    'types' => [
                        'twilio/text' => [
                            'body' => 'Disciple.Tools notification: {{1}} declined assignment on: {{2}}. \nClick {{3}} to update the record.'
                        ]
                    ]
                ]
            ]
        ];

        return apply_filters( 'dt_twilio_messaging_templates', $messaging_templates );
    }

    public static function list_messaging_templates_statuses( $args = [] ): array {
        if ( ! self::has_credentials() ) {
            return [];
        }

        $templates = [];

        try {

            // As there does not seem to be a Twilio API for this operation, a raw HTTP GET request shall be constructed.
            $url = 'https://content.twilio.com/v1/ContentAndApprovals';
            $options = [
                'http' => [
                    'header' => [
                        'Content-Type: application/json',
                        'Authorization: Basic ' . base64_encode( self::get_option( self::$option_twilio_sid, '' ) .':'. self::get_option( self::$option_twilio_token, '' ) )
                    ],
                    'method' => 'GET',
                    'content' => ''
                ]
            ];

            $context = stream_context_create( $options );
            $response = file_get_contents( $url, false, $context );

            if ( !empty( $response ) && !is_wp_error( $response ) ) {
                $content_and_approvals = json_decode( $response, true );
                if ( ! empty( $content_and_approvals ) ) {
                    $messaging_templates = self::list_messaging_templates();
                    foreach ( $content_and_approvals['contents'] ?? [] as $content ) {

                        // Capture content template details
                        if ( isset( $content['sid'] ) ) {
                            $details = [
                                'id' => $content['sid'],
                                'name' => $content['friendly_name'] ?? ''
                            ];

                            if ( isset( $content['approval_requests'] ) ) {
                                $details['approval_status'] = [
                                    'name' => $content['approval_requests']['name'] ?? '',
                                    'status' => $content['approval_requests']['status'] ?? '',
                                    'rejection_reason' => $content['approval_requests']['rejection_reason'] ?? '',
                                    'category' => $content['approval_requests']['category'] ?? '',
                                    'content_type' => $content['approval_requests']['content_type'] ?? ''
                                ];
                            }

                            $templates[ $content['sid'] ] = $details;

                            // If specified, avoid duplicates, by ensuring remotely created template ids, are kept in sync with local settings.
                            if ( isset( $args['avoid_duplicates'] ) && $args['avoid_duplicates'] ) {
                                foreach ( $messaging_templates as $template_id => $template ) {
                                    if ( $template['name'] === $details['name'] ) {
                                        $messaging_templates_settings = self::get_option( self::$option_twilio_messaging_templates, [] );
                                        if ( !isset( $messaging_templates_settings[$template_id], $messaging_templates_settings[$template_id]['content_id'] ) || ( $messaging_templates_settings[$template_id]['content_id'] !== $details['id'] ) ) {
                                            $messaging_templates_settings[$template_id]['content_id'] = $details['id'];
                                            self::set_option( self::$option_twilio_messaging_templates, $messaging_templates_settings );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch ( Exception $e ) {
            dt_write_log( $e );
            return [];
        }

        return $templates;
    }

    public static function upload_messaging_template( $template_id ) {
        if ( ! self::has_credentials() ) {
            return null;
        }

        // Fetch corresponding template and ensure it's enabled and ready for business! ;-)
        $messaging_templates = self::list_messaging_templates();
        if ( isset( $messaging_templates[ $template_id ] ) && $messaging_templates[ $template_id ]['enabled'] && !empty( $messaging_templates[ $template_id ]['content_template'] ) ) {
            try {

                // As there does not seem to be a Twilio API for this operation, a raw HTTP POST request shall be constructed.
                $url = 'https://content.twilio.com/v1/Content';
                $options = [
                    'http' => [
                        'header' => [
                            'Content-Type: application/json',
                            'Authorization: Basic ' . base64_encode( self::get_option( self::$option_twilio_sid, '' ) .':'. self::get_option( self::$option_twilio_token, '' ) )
                        ],
                        'method' => 'POST',
                        'content' => json_encode( $messaging_templates[ $template_id ]['content_template'] )
                    ]
                ];

                $context = stream_context_create( $options );
                $response = file_get_contents( $url, false, $context );

                if ( !empty( $response ) && !is_wp_error( $response ) ) {
                    $content = json_decode( $response, true );

                    if ( !empty( $content['sid'] ) ) {
                        return $content['sid'];
                    }
                }
            } catch ( Exception $e ) {
                dt_write_log( $e );
                return null;
            }
        }

        return null;
    }

    public static function submit_messaging_template( $template_id ): bool {
        if ( !self::has_credentials() ) {
            return false;
        }

        // Fetch corresponding template and ensure it's enabled and ready for business! ;-)
        $messaging_templates = self::list_messaging_templates();
        $messaging_templates_settings = self::get_option( self::$option_twilio_messaging_templates, [] );
        if ( isset( $messaging_templates[ $template_id ] ) && $messaging_templates[ $template_id ]['enabled'] && !empty( $messaging_templates[ $template_id ]['content_template'] ) && isset( $messaging_templates_settings[ $template_id ]['content_id'] ) ) {
            try {

                $content_id = $messaging_templates_settings[ $template_id ]['content_id'];
                $messaging_template = $messaging_templates[ $template_id ];

                // Generate a name, based on template_id; ensuring structure adheres to approval request requirements - Only lowercase alphanumeric characters or underscores.
                $content_name = trim( strtolower( str_replace( ' ', '_', $template_id ) ) );

                // As there does not seem to be a Twilio API for this operation, a raw HTTP POST request shall be constructed.
                $url = 'https://content.twilio.com/v1/Content/' . $content_id . '/ApprovalRequests/whatsapp';
                $options = [
                    'http' => [
                        'header' => [
                            'Content-Type: application/json',
                            'Authorization: Basic ' . base64_encode( self::get_option( self::$option_twilio_sid, '' ) .':'. self::get_option( self::$option_twilio_token, '' ) )
                        ],
                        'method' => 'POST',
                        'content' => json_encode( [
                            'name' => $content_name,
                            'category' => $messaging_template['content_category'] ?? 'UTILITY'
                        ] )
                    ]
                ];

                $context = stream_context_create( $options );
                $response = file_get_contents( $url, false, $context );

                if ( !empty( $response ) && !is_wp_error( $response ) ) {
                    $approval = json_decode( $response, true );
                    return ( !empty( $approval['status'] ) && in_array( $approval['status'], [ 'received' ] ) );
                }
            } catch ( Exception $e ) {
                dt_write_log( $e );
                return false;
            }
        }

        return false;
    }

    public static function delete_messaging_template( $template_id ): bool {
        if ( !self::has_credentials() ) {
            return false;
        }

        // Fetch corresponding template and ensure it's enabled and ready for business! ;-)
        $messaging_templates = self::list_messaging_templates();
        $messaging_templates_settings = self::get_option( self::$option_twilio_messaging_templates, [] );
        if ( isset( $messaging_templates[ $template_id ] ) && $messaging_templates[ $template_id ]['enabled'] && isset( $messaging_templates_settings[ $template_id ]['content_id'] ) ) {
            try {

                $content_id = $messaging_templates_settings[ $template_id ]['content_id'];

                // As there does not seem to be a Twilio API for this operation, a raw HTTP DELETE request shall be constructed.
                $url = 'https://content.twilio.com/v1/Content/' . $content_id;
                $options = [
                    'http' => [
                        'header' => [
                            'Content-Type: application/json',
                            'Authorization: Basic ' . base64_encode( self::get_option( self::$option_twilio_sid, '' ) .':'. self::get_option( self::$option_twilio_token, '' ) )
                        ],
                        'method' => 'DELETE',
                        'content' => ''
                    ]
                ];

                $context = stream_context_create( $options );
                $response = file_get_contents( $url, false, $context );

                return ! is_wp_error( $response );

            } catch ( Exception $e ) {
                dt_write_log( $e );
                return false;
            }
        }

        return false;
    }

    public static function list_incoming_phone_numbers(): array {
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

    public static function list_messaging_services_phone_numbers( $sid ): array {
        if ( ! self::has_credentials() ) {
            return [];
        }

        $phone_numbers_sms = [];
        $phone_numbers_whatsapp = [];

        try {

            // Establish Twilio client session
            $twilio = new Client( self::get_option( self::$option_twilio_sid ), self::get_option( self::$option_twilio_token ) );

            // Fetch available incoming phone numbers
            // phpcs:disable
            $incoming_phone_numbers = $twilio->messaging->v1->services( $sid )->phoneNumbers->read( 20 );
            // phpcs:enable

            // Iterate over results
            if ( ! empty( $incoming_phone_numbers ) ) {
                foreach ( $incoming_phone_numbers as $number ) {

                    // phpcs:disable
                    if ( isset( $number->sid, $number->phoneNumber ) ) {
                        $capture = false;

                        // Capture accordingly, based on service type grouping!
                        if ( in_array( 'SMS', $number->capabilities ?? [] ) || in_array( 'MMS', $number->capabilities ?? [] ) ) {
                            $phone_numbers_sms[] = [
                                'id'     => $number->sid,
                                'number' => $number->phoneNumber,
                                'name'   => $number->phoneNumber . ( !empty( $number->countryCode ) ? ' ['. $number->countryCode .']' : '' )
                            ];
                        }

                        if ( in_array( 'Voice', $number->capabilities ?? [] ) ) {
                            $phone_numbers_whatsapp[] = [
                                'id'     => $number->sid,
                                'number' => $number->phoneNumber,
                                'name'   => $number->phoneNumber . ( !empty( $number->countryCode ) ? ' ['. $number->countryCode .']' : '' )
                            ];
                        }
                    }
                    // phpcs:enable
                }
            }
        } catch ( Exception $e ) {
            return [];
        }

        return [
            'sms' => $phone_numbers_sms,
            'whatsapp' => $phone_numbers_whatsapp
        ];
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

                // Prepare message options.
                $msg_opts = [
                    'body' => $message,
                    'messagingServiceSid' => $messaging_service->sid
                ];

                $assigned_numbers_id = self::get_option( ( ( $current_service === 'sms' ) ? self::$option_twilio_assigned_numbers_sms_id : self::$option_twilio_assigned_numbers_whatsapp_id ) );
                if ( !empty( $assigned_numbers_id ) ) {

                    try {

                        // phpcs:disable
                        $phone_number = $twilio->messaging->v1->services( $messaging_service->sid )
                            ->phoneNumbers( $assigned_numbers_id )
                            ->fetch();

                        if ( !empty( $phone_number->phoneNumber ) ) {
                            $msg_opts['from'] = $service . $phone_number->phoneNumber;
                        }
                        // phpcs:enable

                    } catch ( Exception $e_numbers ) {
                        dt_write_log( $e_numbers );
                    }
                }

                // Iterate over phone numbers
                foreach ( $phone_numbers as $phone ) {
                    if ( ! empty( $phone ) ) {

                        // Dispatch message...!
                        $message_result = $twilio->messages->create(
                            $service . $phone,
                            $msg_opts
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

    public static function send_sms( $phone_number, $message ): bool {
        return self::send_by_service( 'sms', $phone_number, $message );
    }

    public static function send_whatsapp( $phone_number, $message ): bool {
        return self::send_by_service( 'whatsapp', $phone_number, $message );
    }

    private static function send_by_service( $service, $phone_number, $message ): bool {
        if ( ! self::has_credentials() ) {
            return false;
        }

        // Ensure required params are present.
        if ( empty( $service ) || empty( $phone_number ) || empty( $message ) ) {
            return false;
        }

        try {

            // Ensure a valid messaging service id has been set.
            $messaging_service_id = self::get_option( self::$option_twilio_msg_service_id );
            if ( !empty( $messaging_service_id ) ) {

                // List all numbers currently assigned to messaging service.
                $messaging_service_phone_numbers = self::list_messaging_services_phone_numbers( $messaging_service_id );
                if ( !empty( $messaging_service_phone_numbers ) ) {
                    $from_number = null;
                    $messaging_service_phone_numbers_by_service = ( ( $service === 'sms' ) ? $messaging_service_phone_numbers['sms'] ?? [] : $messaging_service_phone_numbers['whatsapp'] ?? [] );

                    // Determine if an assigned number has been specified and fetch corresponding phone number.
                    $assigned_numbers_id = self::get_option( ( ( $service === 'sms' ) ? self::$option_twilio_assigned_numbers_sms_id : self::$option_twilio_assigned_numbers_whatsapp_id ) );
                    if ( !empty( $assigned_numbers_id ) ) {
                        foreach ( $messaging_service_phone_numbers_by_service as $messaging_service_phone_number ) {
                            if ( isset( $messaging_service_phone_number['id'] ) && !empty( $messaging_service_phone_number['number'] ) && ( $messaging_service_phone_number['id'] === $assigned_numbers_id ) ) {
                                $from_number = $messaging_service_phone_number['number'];
                            }
                        }
                    }

                    // If $from_number is still empty, attempt to fetch the first available number!
                    if ( empty( $from_number ) && !empty( $messaging_service_phone_numbers_by_service ) && isset( $messaging_service_phone_numbers_by_service[0]['id'], $messaging_service_phone_numbers_by_service[0]['number'] ) ) {
                        $from_number = $messaging_service_phone_numbers_by_service[0]['number'];
                    }

                    // Only proceed, if a valid from number has been identified!
                    if ( !empty( $from_number ) ) {

                        // Establish Twilio client session
                        $twilio = new Client( self::get_option( self::$option_twilio_sid ), self::get_option( self::$option_twilio_token ) );

                        // Determine service prefix.
                        switch ( $service ) {
                            case 'whatsapp':
                                $service_prefix = 'whatsapp:';
                                break;
                            default:
                                $service_prefix = ''; // Default to SMS
                                break;
                        }

                        // Prepare message options.
                        $msg_opts = [
                            'body' => $message,
                            'from' => $service_prefix . $from_number,
                            'messagingServiceSid' => $messaging_service_id
                        ];

                        // Dispatch message...!
                        $message_result = $twilio->messages->create(
                            $service_prefix . $phone_number,
                            $msg_opts
                        );
                    }
                }
            }
        } catch ( Exception $e ) {
            return false;
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
