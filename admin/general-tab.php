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
                't_b_d' => []
            )
        );
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
                    <input type="checkbox" id="twilio_main_col_manage_enabled"/>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: middle;">SID</td>
                <td>
                    <input style="min-width: 100%;" type="password" id="twilio_main_col_manage_sid"/>
                    <br>
                    <input type="checkbox" id="twilio_main_col_manage_sid_show">Show SID
                </td>
            </tr>
            <tr>
                <td style="vertical-align: middle;">Token</td>
                <td>
                    <input style="min-width: 100%;" type="password" id="twilio_main_col_manage_token"/>
                    <br>
                    <input type="checkbox" id="twilio_main_col_manage_token_show">Show Token
                </td>
            </tr>
            <tr>
                <td style="vertical-align: middle;">Sender Number</td>
                <td>
                    <select style="min-width: 100%;" id="twilio_main_col_manage_numbers">
                        <option disabled selected value>-- select twilio sender number --</option>

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
                        if ( ! empty( $filtered_fields ) ) {
                            foreach ( $filtered_fields as $filtered ) {
                                if ( isset( $filtered['fields'] ) && ! empty( $filtered['fields'] ) ) {
                                    echo '<option disabled value>-- ' . esc_attr( $filtered['name'] ) . ' --</option>';

                                    // Display filtered fields
                                    foreach ( $filtered['fields'] as $field ) {
                                        $value = esc_attr( $filtered['post_type'] ) . '+' . esc_attr( $field['id'] );
                                        echo '<option value="' . esc_attr( $value ) . '">' . esc_attr( $field['name'] ) . '</option>';
                                    }
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
