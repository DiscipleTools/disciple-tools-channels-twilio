<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Channels_Twilio_Tab_General
 */
class Disciple_Tools_Channels_Twilio_Tab_General {

    public function __construct() {

        // First, handle update submissions
        $this->process_updates();

        // Load scripts and styles
        wp_enqueue_script( 'dt_channels_twilio_general_script', plugin_dir_url( __FILE__ ) . 'js/general-tab.js', [
            'jquery',
            'lodash'
        ], filemtime( dirname( __FILE__ ) . '/js/general-tab.js' ), true );
        wp_localize_script(
            'dt_channels_twilio_general_script', 'dt_channels_twilio', array(
                'dt_endpoint_list_phone_numbers' => Disciple_Tools_Twilio_API::fetch_endpoint_list_phone_numbers_url(),
                'assigned_numbers_sms_id' => Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_assigned_numbers_sms_id ),
                'assigned_numbers_whatsapp_id' => Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_assigned_numbers_whatsapp_id )
            )
        );
    }

    private function process_updates() {
        if ( isset( $_POST['twilio_main_col_manage_form_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['twilio_main_col_manage_form_nonce'] ) ), 'twilio_main_col_manage_form_nonce' ) ) {
            if ( isset( $_POST['twilio_main_col_manage_form_enabled'] ) ) {
                Disciple_Tools_Twilio_API::set_option( Disciple_Tools_Twilio_API::$option_twilio_enabled, sanitize_text_field( wp_unslash( $_POST['twilio_main_col_manage_form_enabled'] ) ) );
            }
            if ( isset( $_POST['twilio_main_col_manage_form_sid'] ) ) {
                Disciple_Tools_Twilio_API::set_option( Disciple_Tools_Twilio_API::$option_twilio_sid, sanitize_text_field( wp_unslash( $_POST['twilio_main_col_manage_form_sid'] ) ) );
            }
            if ( isset( $_POST['twilio_main_col_manage_form_token'] ) ) {
                Disciple_Tools_Twilio_API::set_option( Disciple_Tools_Twilio_API::$option_twilio_token, sanitize_text_field( wp_unslash( $_POST['twilio_main_col_manage_form_token'] ) ) );
            }
            if ( isset( $_POST['twilio_main_col_manage_form_service'] ) ) {
                Disciple_Tools_Twilio_API::set_option( Disciple_Tools_Twilio_API::$option_twilio_service, sanitize_text_field( wp_unslash( $_POST['twilio_main_col_manage_form_service'] ) ) );
            }
            if ( isset( $_POST['twilio_main_col_manage_form_msg_service_id'] ) ) {
                Disciple_Tools_Twilio_API::set_option( Disciple_Tools_Twilio_API::$option_twilio_msg_service_id, sanitize_text_field( wp_unslash( $_POST['twilio_main_col_manage_form_msg_service_id'] ) ) );
            }
            if ( isset( $_POST['twilio_main_col_manage_form_msg_service_assigned_numbers_sms_id'] ) ) {
                Disciple_Tools_Twilio_API::set_option( Disciple_Tools_Twilio_API::$option_twilio_assigned_numbers_sms_id, sanitize_text_field( wp_unslash( $_POST['twilio_main_col_manage_form_msg_service_assigned_numbers_sms_id'] ) ) );
            }
            if ( isset( $_POST['twilio_main_col_manage_form_msg_service_assigned_numbers_whatsapp_id'] ) ) {
                Disciple_Tools_Twilio_API::set_option( Disciple_Tools_Twilio_API::$option_twilio_assigned_numbers_whatsapp_id, sanitize_text_field( wp_unslash( $_POST['twilio_main_col_manage_form_msg_service_assigned_numbers_whatsapp_id'] ) ) );
            }
        }
    }

    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <tr>
                <th>Twilio Management</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <?php $this->main_column_management(); ?>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->

        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <tr>
                <th>Assigned Telephone Numbers</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <?php $this->main_column_assigned_numbers(); ?>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table style="display: none;" id="twilio_right_docs_section" class="widefat striped">
            <thead>
            <tr>
                <th id="twilio_right_docs_title"></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td id="twilio_right_docs_content"></td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php

        // Include helper documentation
        include 'general-tab-docs.php';
    }

    private function main_column_management() {
        ?>
        <table class="widefat striped">
            <tr>
                <td style="vertical-align: middle;">Enabled</td>
                <td>
                    <?php $enabled = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_enabled ); ?>
                    <input type="checkbox"
                           id="twilio_main_col_manage_enabled" <?php echo esc_attr( ! empty( $enabled ) && boolval( $enabled ) ? 'checked' : '' ); ?> />
                </td>
            </tr>
            <tr>
                <td style="vertical-align: middle;">Account SID [<a href="#" class="twilio-docs"
                                                            data-title="twilio_right_docs_title_sid"
                                                            data-content="twilio_right_docs_content_sid">&#63;</a>]
                </td>
                <td>
                    <?php $sid = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_sid ); ?>
                    <input style="min-width: 100%;" type="password" id="twilio_main_col_manage_sid"
                           value="<?php echo esc_attr( ! empty( $sid ) ? $sid : '' ); ?>"/>
                    <br>
                    <input type="checkbox" id="twilio_main_col_manage_sid_show">Show SID
                </td>
            </tr>
            <tr>
                <td style="vertical-align: middle;">Auth Token [<a href="#" class="twilio-docs"
                                                              data-title="twilio_right_docs_title_token"
                                                              data-content="twilio_right_docs_content_token">&#63;</a>]
                </td>
                <td>
                    <?php $token = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_token ); ?>
                    <input style="min-width: 100%;" type="password" id="twilio_main_col_manage_token"
                           value="<?php echo esc_attr( ! empty( $token ) ? $token : '' ); ?>"/>
                    <br>
                    <input type="checkbox" id="twilio_main_col_manage_token_show">Show Token
                </td>
            </tr>
            <tr>
                <td style="vertical-align: middle;">Default Service Type [<a href="#" class="twilio-docs"
                                                                data-title="twilio_right_docs_title_service"
                                                                data-content="twilio_right_docs_content_service">&#63;</a>]
                </td>
                <td>
                    <select style="min-width: 100%;" id="twilio_main_col_manage_service">
                        <?php
                        $services        = Disciple_Tools_Twilio_API::list_services();
                        $current_service = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_service );
                        foreach ( $services ?? [] as $service ) {
                            $selected = ! empty( $current_service ) && $current_service === $service['id'] ? 'selected' : '';
                            echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $service['id'] ) . '">' . esc_attr( $service['name'] ) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: middle;">Messaging Service [<a href="#" class="twilio-docs"
                                                                          data-title="twilio_right_docs_title_msg_service"
                                                                          data-content="twilio_right_docs_content_msg_service">&#63;</a>]
                </td>
                <td>
                    <select style="min-width: 100%;" id="twilio_main_col_manage_msg_service">
                        <option disabled selected value>-- select twilio messaging service --</option>

                        <?php
                        $messaging_services = Disciple_Tools_Twilio_API::list_messaging_services();
                        $msg_service_id     = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_msg_service_id );
                        foreach ( $messaging_services ?? [] as $service ) {
                            $selected = ! empty( $msg_service_id ) && $msg_service_id === $service['id'] ? 'selected' : '';
                            echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $service['id'] ) . '">' . esc_attr( $service['name'] ) . '</option>';
                        }
                        ?>

                    </select>
                </td>
            </tr>
        </table>
        <br>
        <span style="float:right;">
            <button type="submit" id="twilio_main_col_manage_update"
                    class="button float-right twilio-main-col-update"><?php esc_html_e( 'Update', 'disciple_tools' ) ?></button>
        </span>

        <!-- [Submission Form] -->
        <form method="post" id="twilio_main_col_manage_form">
            <input type="hidden" id="twilio_main_col_manage_form_nonce" name="twilio_main_col_manage_form_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'twilio_main_col_manage_form_nonce' ) ) ?>"/>

            <input type="hidden" value="" id="twilio_main_col_manage_form_enabled"
                   name="twilio_main_col_manage_form_enabled"/>

            <input type="hidden" value="" id="twilio_main_col_manage_form_sid"
                   name="twilio_main_col_manage_form_sid"/>

            <input type="hidden" value="" id="twilio_main_col_manage_form_token"
                   name="twilio_main_col_manage_form_token"/>

            <input type="hidden" value="" id="twilio_main_col_manage_form_service"
                   name="twilio_main_col_manage_form_service"/>

            <input type="hidden" value="" id="twilio_main_col_manage_form_msg_service_id"
                   name="twilio_main_col_manage_form_msg_service_id"/>

            <input type="hidden" value="" id="twilio_main_col_manage_form_msg_service_assigned_numbers_sms_id"
                   name="twilio_main_col_manage_form_msg_service_assigned_numbers_sms_id"/>

            <input type="hidden" value="" id="twilio_main_col_manage_form_msg_service_assigned_numbers_whatsapp_id"
                   name="twilio_main_col_manage_form_msg_service_assigned_numbers_whatsapp_id"/>
        </form>
        <?php
    }

    private function main_column_assigned_numbers() {
        ?>
        <div class="wrap">
            <nav class="nav-tab-wrapper wp-clearfix">
                <a id="twilio_main_col_assigned_numbers_sms_tab" href="#"
                   class="numbers-nav-tab nav-tab nav-tab-active"
                   data-number_type="sms">SMS</a>
                <a id="twilio_main_col_assigned_numbers_whatsapp_tab" href="#"
                   class="numbers-nav-tab nav-tab"
                   data-number_type="whatsapp">WhatsApp</a>
            </nav>
            <br>
            <div id="twilio_main_col_assigned_numbers_tab_content" class="numbers-nav-tab-content" style="text-align: center;">
                <span id="twilio_main_col_assigned_numbers_tab_content_spinner" class="loading-spinner active" style="display: none;"></span>
                <span id="twilio_main_col_assigned_numbers_tab_content_msg" style="display: none;"></span>

                <div id="twilio_main_col_assigned_numbers_tab_content_sms" class="numbers-nav-tab-content" style="display: none; text-align: center;">
                    <table class="widefat striped">
                        <tr>
                            <td style="vertical-align: middle;">Phone Numbers</td>
                            <td>
                                <select id="twilio_main_col_assigned_numbers_sms_select" style="min-width: 100%;"></select>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="twilio_main_col_assigned_numbers_tab_content_whatsapp" class="numbers-nav-tab-content" style="display: none; text-align: center;">
                    <table class="widefat striped">
                        <tr>
                            <td style="vertical-align: middle;">Phone Numbers</td>
                            <td>
                                <select id="twilio_main_col_assigned_numbers_whatsapp_select" style="min-width: 100%;"></select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <br>
        <span style="float:right;">
            <button type="submit" id="twilio_main_col_assigned_numbers_update"
                    class="button float-right twilio-main-col-update"><?php esc_html_e( 'Update', 'disciple_tools' ) ?></button>
        </span>
        <?php
    }
}
