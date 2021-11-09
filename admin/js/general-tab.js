jQuery(function ($) {

  $(document).on('click', '.twilio-docs', function (evt) {
    handle_docs_request($(evt.currentTarget).data('title'), $(evt.currentTarget).data('content'));
  });

  $(document).on('click', '#twilio_main_col_manage_sid_show', function () {
    show_secrets($('#twilio_main_col_manage_sid'), $('#twilio_main_col_manage_sid_show'));
  });

  $(document).on('click', '#twilio_main_col_manage_token_show', function () {
    show_secrets($('#twilio_main_col_manage_token'), $('#twilio_main_col_manage_token_show'));
  });

  $(document).on('click', '#twilio_main_col_manage_update', function () {
    update_settings();
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

  function update_settings() {

    // Fetch values
    let enabled = $('#twilio_main_col_manage_enabled').prop('checked') ? 1 : 0;
    let sid = $('#twilio_main_col_manage_sid').val();
    let token = $('#twilio_main_col_manage_token').val();
    let service = $('#twilio_main_col_manage_service').val();
    let msg_service_id = $('#twilio_main_col_manage_msg_service').val();

    // Update submission form
    $('#twilio_main_col_manage_form_enabled').val(enabled);
    $('#twilio_main_col_manage_form_sid').val(sid);
    $('#twilio_main_col_manage_form_token').val(token);
    $('#twilio_main_col_manage_form_service').val(service);
    $('#twilio_main_col_manage_form_msg_service_id').val(msg_service_id);

    // Post submission form
    $('#twilio_main_col_manage_form').submit();
  }

});
