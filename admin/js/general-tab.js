jQuery(function ($) {

  $(document).ready(function () {
    assigned_numbers_load(function () {
      $('#twilio_main_col_assigned_numbers_sms_select').val( window.dt_admin_shared.escape( window.dt_channels_twilio.assigned_numbers_sms_id ) );
      $('#twilio_main_col_assigned_numbers_whatsapp_select').val( window.dt_admin_shared.escape( window.dt_channels_twilio.assigned_numbers_whatsapp_id ) );
    });
  });

  $(document).on('click', '.twilio-docs', function (evt) {
    handle_docs_request($(evt.currentTarget).data('title'), $(evt.currentTarget).data('content'));
  });

  $(document).on('click', '#twilio_main_col_manage_sid_show', function () {
    show_secrets($('#twilio_main_col_manage_sid'), $('#twilio_main_col_manage_sid_show'));
  });

  $(document).on('click', '#twilio_main_col_manage_token_show', function () {
    show_secrets($('#twilio_main_col_manage_token'), $('#twilio_main_col_manage_token_show'));
  });

  $(document).on('change', '#twilio_main_col_manage_msg_service', function () {
    assigned_numbers_load(function () {
      $('#twilio_main_col_assigned_numbers_sms_select').val( window.dt_admin_shared.escape( window.dt_channels_twilio.assigned_numbers_sms_id ) );
      $('#twilio_main_col_assigned_numbers_whatsapp_select').val( window.dt_admin_shared.escape( window.dt_channels_twilio.assigned_numbers_whatsapp_id ) );
    });
  });

  $(document).on('click', '.twilio-main-col-update', function () {
    update_settings();
  });

  $(document).on('click', '.numbers-nav-tab', function (e) {
    assigned_numbers_tab_switch( $(e.target).data('number_type') );
  });

  $(document).on('click', '#twilio_main_col_assigned_numbers_whatsapp_templates_upload_all', function (e) {
    handle_whatsapp_templates_auto_actions( 'upload', $(e.currentTarget) );
  });

  $(document).on('click', '#twilio_main_col_assigned_numbers_whatsapp_templates_submit_all', function (e) {
    handle_whatsapp_templates_auto_actions( 'submit', $(e.currentTarget) );
  });

  function handle_docs_request(title_div, content_div) {
    $('#twilio_right_docs_section').fadeOut('fast', function () {
      $('#twilio_right_docs_title').html($('#' + title_div).html());
      $('#twilio_right_docs_content').html($('#' + content_div).html());

      $('#twilio_right_docs_section').fadeIn('fast');
    });
  }

  function show_secrets(input_ele, show_ele) {
    if (show_ele.is(':checked')) {
      input_ele.attr('type', 'text');
    } else {
      input_ele.attr('type', 'password');
    }
  }

  function assigned_numbers_load( callback = null ) {
    const tab_content = $('#twilio_main_col_assigned_numbers_tab_content');
    const tab_content_spinner = $('#twilio_main_col_assigned_numbers_tab_content_spinner');

    $(tab_content).children().hide();
    $(tab_content_spinner).fadeIn('fast');

    // Proceed with requesting list of assigned numbers, by selected messaging service.
    const list_type = 'messaging';
    $.ajax({
      url: window.dt_channels_twilio.dt_endpoint_list_phone_numbers,
      method: 'POST',
      data: {
        'type': list_type,
        'msg_service_id': $('#twilio_main_col_manage_msg_service').val()
      },
      beforeSend: (xhr) => {
        xhr.setRequestHeader("X-WP-Nonce", window.dt_admin_scripts.nonce);
      },
      success: function (data) {

        // Process sms numbers and reset accordingly.
        const tab_content_sms = $('#twilio_main_col_assigned_numbers_tab_content_sms');
        const tab_content_sms_select = $('#twilio_main_col_assigned_numbers_sms_select');
        $(tab_content_sms_select).empty().append($('<option>', {
          'value': '',
          'text': '-- select default sms phone number --',
          'selected': true,
          'disabled': true
        }));

        if (data[list_type] && data[list_type]['sms']) {
          data[list_type]['sms'].forEach(function (number) {
            if (number['id'] && number['name']) {
              $(tab_content_sms_select).append($('<option>', {
                'value': window.dt_admin_shared.escape( number['id'] ),
                'text': window.dt_admin_shared.escape( number['name'] )
              }));
            }
          });
        }

        // Process whatsapp numbers and reset accordingly.
        const tab_content_whatsapp = $('#twilio_main_col_assigned_numbers_tab_content_whatsapp');
        const tab_content_whatsapp_select = $('#twilio_main_col_assigned_numbers_whatsapp_select');
        $(tab_content_whatsapp_select).empty().append($('<option>', {
          'value': '',
          'text': '-- select default whatsapp phone number --',
          'selected': true,
          'disabled': true
        }));

        if (data[list_type] && data[list_type]['whatsapp']) {
          data[list_type]['whatsapp'].forEach(function (number) {
            if (number['id'] && number['name']) {
              $(tab_content_whatsapp_select).append($('<option>', {
                'value': window.dt_admin_shared.escape( number['id'] ),
                'text': window.dt_admin_shared.escape( number['name'] )
              }));
            }
          });
        }

        // Display updated numbers, accordingly by currently active tab.
        $(tab_content_spinner).fadeOut('slow', function() {
          const active_tab = $('.numbers-nav-tab.nav-tab-active');

          // Trigger callback just before re-display.
          if ( callback ) { callback(); }

          // Display relevant tab content.
          switch ( $(active_tab).data('number_type') ) {
            case 'whatsapp':
              $(tab_content_whatsapp).fadeIn('fast');
              break;
            case 'sms':
              $(tab_content_sms).fadeIn('fast');
              break;
          }
        });
      },
      error: function (data) {
        console.log(data);
        $(tab_content_spinner).fadeOut('slow', function () {
          $(tab_content).children().hide();
          $('#twilio_main_col_assigned_numbers_tab_content_msg').text('-- unable to locate any phone numbers --').fadeIn('fast');
        });
      }
    });
  }

  function assigned_numbers_tab_switch( number_type ) {
    const tab_content = $('#twilio_main_col_assigned_numbers_tab_content');
    const tab_content_sms = $('#twilio_main_col_assigned_numbers_tab_content_sms');
    const tab_content_whatsapp = $('#twilio_main_col_assigned_numbers_tab_content_whatsapp');

    // Select specified tab.
    $('.numbers-nav-tab').removeClass('nav-tab-active');
    $('#twilio_main_col_assigned_numbers_'+ number_type +'_tab').addClass('nav-tab-active');

    // Select corresponding tab content.
    $(tab_content).children().hide();

    switch ( number_type ) {
      case 'whatsapp':
        $(tab_content_whatsapp).fadeIn('fast');
        break;
      case 'sms':
        $(tab_content_sms).fadeIn('fast');
        break;
    }
  }

  function handle_whatsapp_templates_auto_actions( action, button = null ) {
    if ( confirm(`Are you sure you wish to ${action} all templates?`) ) {
      const message_span = $('#twilio_main_col_assigned_numbers_whatsapp_templates_message');
      $(message_span).addClass('loading-spinner active').html('').fadeIn('fast');

      if (button) {
        $(button).prop('disabled', true);
      }

      $.ajax({
        url: window.dt_channels_twilio.dt_endpoint_template_actions,
        method: 'POST',
        data: {
          'action': action
        },
        beforeSend: (xhr) => {
          xhr.setRequestHeader("X-WP-Nonce", window.dt_admin_scripts.nonce);
        },
        success: function (data) {
          let message = 'No results returned. Please check Templates Tab and try again!';
          if ((data['action']!==undefined) && (data['processed_count']!==undefined)) {
            const action_prefix_text = (data['action']==='upload') ? 'Uploaded':'Submitted';
            message = `${action_prefix_text} ${data['processed_count']} Templates.`;
          }
          $(message_span).fadeOut('fast', function () {
            $(message_span).removeClass('loading-spinner active');
            $(message_span).html(message);
            $(message_span).fadeIn('fast');
          });

          if (button) {
            $(button).prop('disabled', false);
          }
        },
        error: function (data) {
          console.log(data);
          $(message_span).fadeOut('fast').removeClass('loading-spinner active').html('');

          if (button) {
            $(button).prop('disabled', false);
          }
        }
      });
    }
  }

  function update_settings() {

    // Fetch values
    let enabled = $('#twilio_main_col_manage_enabled').prop('checked') ? 1 : 0;
    let sid = $('#twilio_main_col_manage_sid').val();
    let token = $('#twilio_main_col_manage_token').val();
    let service = $('#twilio_main_col_manage_service').val();
    let msg_service_id = $('#twilio_main_col_manage_msg_service').val();
    let msg_service_assigned_numbers_sms_id = $('#twilio_main_col_assigned_numbers_sms_select').val();
    let msg_service_assigned_numbers_whatsapp_id = $('#twilio_main_col_assigned_numbers_whatsapp_select').val();
    let service_sms_enabled = $('#twilio_main_col_assigned_numbers_sms_notify_enabled').prop('checked') ? 1 : 0;
    let service_whatsapp_enabled = $('#twilio_main_col_assigned_numbers_whatsapp_notify_enabled').prop('checked') ? 1 : 0;

    // Update submission form
    $('#twilio_main_col_manage_form_enabled').val(enabled);
    $('#twilio_main_col_manage_form_sid').val(sid);
    $('#twilio_main_col_manage_form_token').val(token);
    $('#twilio_main_col_manage_form_service').val(service);
    $('#twilio_main_col_manage_form_msg_service_id').val(msg_service_id);
    $('#twilio_main_col_manage_form_msg_service_assigned_numbers_sms_id').val(msg_service_assigned_numbers_sms_id);
    $('#twilio_main_col_manage_form_msg_service_assigned_numbers_whatsapp_id').val(msg_service_assigned_numbers_whatsapp_id);
    $('#twilio_main_col_manage_form_service_sms_enabled').val(service_sms_enabled);
    $('#twilio_main_col_manage_form_service_whatsapp_enabled').val(service_whatsapp_enabled);

    // Post submission form
    $('#twilio_main_col_manage_form').submit();
  }

});
