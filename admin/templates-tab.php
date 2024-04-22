<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Channels_Twilio_Tab_Templates
 */
class Disciple_Tools_Channels_Twilio_Tab_Templates {

    public function __construct() {

        // First, handle update submissions
        $this->process_updates();

        // Load scripts and styles
        $this->process_scripts();
    }

    private function process_updates() {

    }

    public function process_scripts(): void {
        dt_theme_enqueue_style( 'material-font-icons-local', 'dt-core/dependencies/mdi/css/materialdesignicons.min.css', array() );
        wp_enqueue_style( 'material-font-icons', 'https://cdn.jsdelivr.net/npm/@mdi/font@6.6.96/css/materialdesignicons.min.css', array(), '6.6.96' );

        wp_enqueue_script( 'dt_channels_twilio_templates_script', plugin_dir_url( __FILE__ ) . 'js/templates-tab.js', [
            'jquery',
            'lodash'
        ], filemtime( dirname( __FILE__ ) . '/js/templates-tab.js' ), true );
        wp_localize_script(
            'dt_channels_twilio_templates_script', 'dt_channels_twilio', array(
                'messaging_templates' => Disciple_Tools_Twilio_API::list_messaging_templates() ?? [],
                'messaging_templates_statuses' => Disciple_Tools_Twilio_API::list_messaging_templates_statuses( [ 'avoid_duplicates' => true ] ) ?? [],
                'messaging_templates_settings' => Disciple_Tools_Twilio_API::get_option( Disciple_Tools_Twilio_API::$option_twilio_messaging_templates, [] ),
                'dt_endpoint_upload_messaging_template' => Disciple_Tools_Twilio_API::fetch_endpoint_upload_messaging_template_url(),
                'dt_endpoint_submit_messaging_template' => Disciple_Tools_Twilio_API::fetch_endpoint_submit_messaging_template_url(),
                'dt_endpoint_reset_messaging_template' => Disciple_Tools_Twilio_API::fetch_endpoint_reset_messaging_template_url()
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
                <th>Messaging Template Management</th>
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
        <table id="twilio_right_docs_section" class="widefat striped">
            <thead>
            <tr>
                <th id="twilio_right_docs_title">Messaging Template Workflow</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td id="twilio_right_docs_content">
                    All available Messaging Templates will be listed within management table; accompanied by the following actions:<br>
                    <ul>
                        <li>- View Content Templates</li>
                        <li>- Upload Content Templates</li>
                        <li>- Start Twilio Approval Process</li>
                        <li>- Reset Content Templates</li>
                    </ul>
                    <br>

                    <span style="font-weight: bold;">View Content Templates</span><br><br>
                    A json representation of the Messaging Content Template to be uploaded to Twilio servers, is shown; which can be copied and used elsewhere.

                    <br><br>
                    <span style="font-weight: bold;">Upload Content Templates</span><br><br>
                    This action will enable you to upload and create content template within Twilio; which will subsequently generate the required id, to be used during approval submission process.

                    <br><br>
                    <span style="font-weight: bold;">Start Twilio Approval Process</span><br><br>
                    Formally submit previously uploaded content template to start WhatsApp approval process. The status column will be updated accordingly, with one of the following:
                    <ul>
                        <li>- Unsubmitted</li>
                        <li>- Received</li>
                        <li>- Pending</li>
                        <li>- Approved</li>
                        <li>- Rejected</li>
                        <li>- Paused</li>
                        <li>- Disabled</li>
                    </ul>
                    <br>
                    A detailed description of each status, can be found <a href="https://www.twilio.com/docs/content/content-types-overview#whatsapp-template-approval-statuses" target="_blank">here</a>.

                    <br><br>
                    <span style="font-weight: bold;">Reset Content Templates</span><br><br>
                    This action will reset any previously uploaded content templates.<br><br>
                    Please note, this operation is final and both templates and their approval statuses will be deleted; requiring a fresh upload and re-submission to obtain a new approval status.
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php

        // Include helper documentation
        include 'templates-tab-docs.php';
    }

    private function main_column_management() {
        ?>
        <table id="twilio_main_col_templates_manage_table" class="widefat striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th><span style="float: right;">Actions</span></th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ( Disciple_Tools_Twilio_API::list_messaging_templates() ?? [] as $template_key => $template ) {
                if ( isset( $template['enabled'] ) && $template['enabled'] ) {
                    ?>
                    <tr>
                        <td style="vertical-align: middle;">
                            <?php echo esc_attr( $template['name'] ); ?>

                            <input id="twilio_main_col_template_id" type="hidden" value="<?php echo esc_attr( $template['id'] ); ?>" />
                        </td>
                        <td style="vertical-align: middle;"><?php echo esc_attr( $template['type'] ); ?></td>
                        <td style="vertical-align: middle;" id="twilio_main_col_template_status">---</td>
                        <td style="vertical-align: middle;">
                            <span style="float: right;">
                                <button id="twilio_main_col_template_action_view" class="button template-action-button" type="button" data-template_id="<?php echo esc_attr( $template['id'] ); ?>" data-template_action="view" title="View Content Template Structure">
                                    <span class="mdi mdi-glasses"></span>
                                </button>
                                <button id="twilio_main_col_template_action_upload" disabled class="button template-action-button" type="button" data-template_id="<?php echo esc_attr( $template['id'] ); ?>" data-template_action="upload" title="Upload & Create Template On Twilio Servers">
                                    <span class="mdi mdi-cloud-upload-outline"></span>
                                </button>
                                <button id="twilio_main_col_template_action_approve" disabled class="button template-action-button" type="button" data-template_id="<?php echo esc_attr( $template['id'] ); ?>" data-template_action="approve" title="Start Twilio Approval Process">
                                    <span class="mdi mdi-cogs"></span>
                                </button>
                                <button id="twilio_main_col_template_action_reset" disabled class="button template-action-button" type="button" data-template_id="<?php echo esc_attr( $template['id'] ); ?>" data-template_action="reset" title="Reset Content Template">
                                    <span class="mdi mdi-backup-restore"></span>
                                </button>
                            </span>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
        <br>

        <?php
    }
}
