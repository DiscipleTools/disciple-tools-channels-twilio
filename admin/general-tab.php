<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Channels_Twilio_Tab_General
 */
class Disciple_Tools_Channels_Twilio_Tab_General {

    public function __construct() {

        // Load scripts and styles
        wp_enqueue_script( 'dt_channels_twilio_general_script', plugin_dir_url( __FILE__ ) . 'js/general-tab.js', [
            'jquery',
            'lodash'
        ], filemtime( dirname( __FILE__ ) . '/js/general-tab.js' ), true );
        wp_localize_script(
            "dt_channels_twilio_general_script", "dt_channels_twilio", array(
                't_b_c' => []
            )
        );

        // First, handle update submissions
        $this->process_updates();
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
            if ( isset( $_POST['twilio_main_col_manage_form_contact_field'] ) ) {
                Disciple_Tools_Twilio_API::set_option( Disciple_Tools_Twilio_API::$option_twilio_contact_field, sanitize_text_field( wp_unslash( $_POST['twilio_main_col_manage_form_contact_field'] ) ) );
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

                        <?php /* $this->right_column() */ ?>

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
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <tr>
                <th>Information</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    Content
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
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
                <td style="vertical-align: middle;">SID</td>
                <td>
                    <?php $sid = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_sid ); ?>
                    <input style="min-width: 100%;" type="password" id="twilio_main_col_manage_sid"
                           value="<?php echo esc_attr( ! empty( $sid ) ? $sid : '' ); ?>"/>
                    <br>
                    <input type="checkbox" id="twilio_main_col_manage_sid_show">Show SID
                </td>
            </tr>
            <tr>
                <td style="vertical-align: middle;">Token</td>
                <td>
                    <?php $token = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_token ); ?>
                    <input style="min-width: 100%;" type="password" id="twilio_main_col_manage_token"
                           value="<?php echo esc_attr( ! empty( $token ) ? $token : '' ); ?>"/>
                    <br>
                    <input type="checkbox" id="twilio_main_col_manage_token_show">Show Token
                </td>
            </tr>
            <tr>
                <td style="vertical-align: middle;">Service</td>
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
                <td style="vertical-align: middle;">Messaging Service</td>
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
            <tr>
                <td style="vertical-align: middle;">Contact Field</td>
                <td>
                    <select style="min-width: 100%;" id="twilio_main_col_manage_fields">
                        <option disabled selected value>-- select field containing contact information --</option>

                        <?php
                        $filtered_fields = $this->fetch_filtered_fields();
                        $contact_field   = Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_contact_field );
                        foreach ( $filtered_fields ?? [] as $filtered ) {
                            if ( isset( $filtered['fields'] ) && ! empty( $filtered['fields'] ) ) {
                                echo '<option disabled value>-- ' . esc_attr( $filtered['name'] ) . ' --</option>';

                                // Display filtered fields
                                foreach ( $filtered['fields'] as $field ) {
                                    $value    = esc_attr( $filtered['post_type'] ) . '+' . esc_attr( $field['id'] );
                                    $selected = ! empty( $contact_field ) && $contact_field === $value ? 'selected' : '';
                                    echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $value ) . '">' . esc_attr( $field['name'] ) . '</option>';
                                }
                            }
                        }
                        ?>

                    </select>
                </td>
            </tr>
        </table>
        <br>
        <span style="float:right;">
            <button type="submit" id="twilio_main_col_manage_update"
                    class="button float-right"><?php esc_html_e( "Update", 'disciple_tools' ) ?></button>
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

            <input type="hidden" value="" id="twilio_main_col_manage_form_contact_field"
                   name="twilio_main_col_manage_form_contact_field"/>

        </form>
        <?php
    }

    private function fetch_filtered_fields(): array {
        $filtered_fields = [];
        $supported_types = [
            'communication_channel'
        ];

        $post_types = DT_Posts::get_post_types();
        if ( ! empty( $post_types ) ) {
            foreach ( $post_types as $post_type ) {

                $fields             = [];
                $post_type_settings = DT_Posts::get_post_settings( $post_type );

                // Iterate over fields in search of comms fields
                foreach ( $post_type_settings['fields'] as $key => $field ) {
                    if ( isset( $field['type'] ) && in_array( $field['type'], $supported_types ) ) {

                        // Capture filtered field
                        $fields[] = [
                            'id'   => $key,
                            'name' => $field['name'],
                            'type' => $field['type']
                        ];
                    }
                }

                // If filtered fields detected, package and return...
                if ( ! empty( $fields ) ) {
                    $filtered_fields[ $post_type ] = [
                        'post_type' => $post_type,
                        'name'      => $post_type_settings['label_plural'],
                        'fields'    => $fields
                    ];
                }
            }
        }

        return $filtered_fields;
    }
}
