jQuery(function ($) {

  $(document).ready(function () {
    refresh_templates(
      window.dt_channels_twilio.messaging_templates,
      window.dt_channels_twilio.messaging_templates_settings,
      window.dt_channels_twilio.messaging_templates_statuses
    );
  });

  $(document).on('click', '.template-action-button', function (evt) {
    const action_but = $(evt.currentTarget);
    const id = $(action_but).data('template_id');
    const action = $(action_but).data('template_action');
    handle_template_action_request(id, action, action_but);
  });

  /*
   * Helper Functions
   */

  function refresh_templates(messaging_templates, messaging_templates_settings, messaging_templates_statuses) {
    $('#twilio_main_col_templates_manage_table').find('tbody tr').each((idx, tr) => {
      const template_id = $(tr).find('#twilio_main_col_template_id').val();
      const template_status = $(tr).find('#twilio_main_col_template_status');
      const action_view = $(tr).find('#twilio_main_col_template_action_view');
      const action_upload = $(tr).find('#twilio_main_col_template_action_upload');
      const action_approve = $(tr).find('#twilio_main_col_template_action_approve');
      const action_reset = $(tr).find('#twilio_main_col_template_action_reset');

      // Determine template actions to be activated and/or disabled.
      if ( !messaging_templates_settings[ template_id ] || !messaging_templates_settings[ template_id ]['content_id'] ) {

        // Template is at the start of the Twilio content approval process.
        $(action_view).prop('disabled', false);
        $(action_upload).prop('disabled', false);
        $(action_approve).prop('disabled', true);
        $(action_reset).prop('disabled', true);
        $(template_status).html('---');

        $(template_status).html(`
          <span>Not Uploaded</span><br>
          <span style="color: #808080; font-size: 10px;">The template has not been uploaded to Twilio servers.</span>
        `);

      } else if ( messaging_templates_settings[ template_id ]['content_id'] && messaging_templates_statuses[ messaging_templates_settings[ template_id ]['content_id'] ] ) {
        const content = messaging_templates_statuses[ messaging_templates_settings[ template_id ]['content_id'] ];

        // Determine content's current status and subsequent actions to be made available.
        const content_status = content['approval_status']['status'];
        switch( content_status ) {
          case 'unsubmitted':
            $(action_view).prop('disabled', false);
            $(action_upload).prop('disabled', true);
            $(action_approve).prop('disabled', false);
            $(action_reset).prop('disabled', false);

            $(template_status).html(`
              <span>Unsubmitted</span><br>
              <span style="color: #808080; font-size: 10px;">The template has not been submitted to Twilio or WhatsApp for any sort of approval.</span>
            `);
            break;
          case 'received':
          case 'pending':
          case 'approved':
          case 'rejected':
          case 'paused':
          case 'disabled':
          default:
            $(action_view).prop('disabled', false);
            $(action_upload).prop('disabled', true);
            $(action_approve).prop('disabled', true);
            $(action_reset).prop('disabled', false);

            // Display status text accordingly, based on current state.
            let status_title = null;
            let status_text = null;
            if ( content_status === 'received' ) {
              status_title = 'Received';
              status_text = 'Template approval request has been received by Twilio. It is not yet in review by WhatsApp.';

            } else if ( content_status === 'pending' ) {
              status_title = 'Pending';
              status_text = 'The template is now under review by WhatsApp. Review can take up to 24 hours.';

            } else if ( content_status === 'approved' ) {
              status_title = 'Approved';
              status_text = 'The template has been approved by WhatsApp and can now be used to notify customers.';

            } else if ( content_status === 'rejected' ) {
              status_title = 'Rejected';
              status_text = 'The template has been rejected by WhatsApp during the review process. ';

              if ( content['approval_status']['rejection_reason'] ) {
                status_text += content['approval_status']['rejection_reason'];
              }

            } else if ( content_status === 'paused' ) {
              status_title = 'Paused';
              status_text = 'The template has been paused by WhatsApp due to recurring negative feedback from end users, typically resulting from "block" and "report spam" actions associated with the template. Message templates with this status cannot be sent to end users.';

            } else if ( content_status === 'disabled' ) {
              status_title = 'Disabled';
              status_text = ' The template has been disabled by WhatsApp due to recurring negative feedback from end users or for violating one or more of WhatsApp\'s policies. Message templates with this status cannot be sent to end users.';

            } else {
              $(template_status).html('---');
            }

            if ( status_title && status_text) {
              $(template_status).html(`
                <span>${ status_title }</span><br>
                <span style="color: #808080; font-size: 10px;">${ status_text }</span>
              `);
            }
            break;
        }
      } else {
        $(action_view).prop('disabled', false);
        $(action_upload).prop('disabled', true);
        $(action_approve).prop('disabled', true);
        $(action_reset).prop('disabled', true);
        $(template_status).html('---');
      }
    });
  }

  function handle_template_action_request(template_id, template_action, action_but = null) {
    const messaging_templates = window.dt_channels_twilio.messaging_templates;
    if (template_id && template_action && messaging_templates[ template_id ]) {
      switch ( template_action ) {
        case 'view': {
          const content = ( ( messaging_templates[ template_id ]?.content_template !== undefined ) && ( Object.keys( messaging_templates[ template_id ]['content_template'] ).length > 0 ) ) ? JSON.stringify( messaging_templates[ template_id ]['content_template'], null, 8 ) : 'Content Template Structure Unavailable!';
          alert( content );
          break;
        }
        case 'upload': {
          if ( confirm( `Are you sure you wish to upload ${ messaging_templates[ template_id ]['name'] }, to Twilio servers?` ) ) {

            // If there is a handle onto action button; then trigger spinner!
            if (action_but) {
              $(action_but).find('span')
              .removeClass('mdi mdi-cloud-upload-outline')
              .css('width', '13px')
              .css('height', '13px')
              .addClass('loading-spinner active');

              $(action_but).prop('disabled', true);
            }

            // Proceed with template upload.
            $.ajax({
              url: window.dt_channels_twilio.dt_endpoint_upload_messaging_template,
              method: 'POST',
              data: {
                'template_id': template_id
              },
              beforeSend: (xhr) => {
                xhr.setRequestHeader("X-WP-Nonce", window.dt_admin_scripts.nonce);
              },
              success: function (data) {
                if ( data['content_id'] && action_but ) {
                  const content_id = data['content_id'];

                  // Obtain handle to corresponding status td.
                  const template_status = $(action_but).parent().parent().parent().find('#twilio_main_col_template_status');
                  if ( template_status ) {
                    $(template_status).html(`
                      <span>Successfully Uploaded</span><br>
                      <span style="color: #808080; font-size: 10px;">${ content_id }</span>
                    `);
                  }
                }

                // Remove spinner, but keep button disabled!
                if (action_but) {
                  $(action_but).find('span')
                  .removeClass('loading-spinner active')
                  .css('width', '')
                  .css('height', '')
                  .addClass('mdi mdi-cloud-upload-outline');
                }
              },
              error: function (data) {
                console.log(data);

                if (action_but) {
                  $(action_but).find('span')
                  .removeClass('loading-spinner active')
                  .css('width', '')
                  .css('height', '')
                  .addClass('mdi mdi-cloud-upload-outline');
                }
              }
            });
          }
          break;
        }
        case 'approve': {
          if ( confirm( `Are you sure you wish to submit and start approval process for ${ messaging_templates[ template_id ]['name'] }?` ) ) {

            // If there is a handle onto action button; then trigger spinner!
            if (action_but) {
              $(action_but).find('span')
              .removeClass('mdi mdi-cogs')
              .css('width', '13px')
              .css('height', '13px')
              .addClass('loading-spinner active');

              $(action_but).prop('disabled', true);
            }

            // Proceed with template approval.
            $.ajax({
              url: window.dt_channels_twilio.dt_endpoint_submit_messaging_template,
              method: 'POST',
              data: {
                'template_id': template_id
              },
              beforeSend: (xhr) => {
                xhr.setRequestHeader("X-WP-Nonce", window.dt_admin_scripts.nonce);
              },
              success: function (data) {
                if ( data['submitted'] && action_but ) {

                  // Obtain handle to corresponding status td.
                  const template_status = $(action_but).parent().parent().parent().find('#twilio_main_col_template_status');
                  if ( template_status ) {
                    $(template_status).html(`
                      <span>Successfully Submitted</span><br>
                      <span style="color: #808080; font-size: 10px;">The template has been submitted to Twilio and/or WhatsApp for approval.</span>
                    `);
                  }
                }

                // Remove spinner, but keep button disabled!
                if (action_but) {
                  $(action_but).find('span')
                  .removeClass('loading-spinner active')
                  .css('width', '')
                  .css('height', '')
                  .addClass('mdi mdi-cogs');
                }
              },
              error: function (data) {
                console.log(data);

                if (action_but) {
                  $(action_but).find('span')
                  .removeClass('loading-spinner active')
                  .css('width', '')
                  .css('height', '')
                  .addClass('mdi mdi-cogs');
                }
              }
            });
          }
          break;
        }
        case 'reset': {
          if ( confirm( `Are you sure you wish to reset ${ messaging_templates[ template_id ]['name'] }? \n\nThis operation is final and any previously uploaded templates will be deleted from Twilio servers, along with any approval statuses.` ) ) {

            // If there is a handle onto action button; then trigger spinner!
            if (action_but) {
              $(action_but).find('span')
              .removeClass('mdi mdi-backup-restore')
              .css('width', '13px')
              .css('height', '13px')
              .addClass('loading-spinner active');

              $(action_but).prop('disabled', true);
            }

            // Proceed with template reset.
            $.ajax({
              url: window.dt_channels_twilio.dt_endpoint_reset_messaging_template,
              method: 'POST',
              data: {
                'template_id': template_id
              },
              beforeSend: (xhr) => {
                xhr.setRequestHeader("X-WP-Nonce", window.dt_admin_scripts.nonce);
              },
              success: function (data) {
                if ( action_but ) {

                  // Obtain handle to corresponding status td.
                  const template_status = $(action_but).parent().parent().parent().find('#twilio_main_col_template_status');
                  if ( template_status ) {
                    $(template_status).html(`
                      <span>Reset Request Submitted</span><br>
                      <span style="color: #808080; font-size: 10px;">Reload page to update template's current status.</span>
                    `);
                  }
                }

                // Remove spinner, but keep button disabled!
                if (action_but) {
                  $(action_but).find('span')
                  .removeClass('loading-spinner active')
                  .css('width', '')
                  .css('height', '')
                  .addClass('mdi mdi-backup-restore');
                }
              },
              error: function (data) {
                console.log(data);

                if (action_but) {
                  $(action_but).find('span')
                  .removeClass('loading-spinner active')
                  .css('width', '')
                  .css('height', '')
                  .addClass('mdi mdi-backup-restore');
                }
              }
            });
          }
          break;
        }
      }
    }
  }

});
