jQuery(function ($) {

  $(document).on('click', '#twilio_main_col_manage_sid_show', function () {
    show_secrets($('#twilio_main_col_manage_sid'), $('#twilio_main_col_manage_sid_show'));
  });

  $(document).on('click', '#twilio_main_col_manage_token_show', function () {
    show_secrets($('#twilio_main_col_manage_token'), $('#twilio_main_col_manage_token_show'));
  });

  $(document).on('click', '#twilio_main_col_manage_update', function () {
    update_settings();
  });

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
    let contact_field = $('#twilio_main_col_manage_fields').val();

    // Update submission form
    $('#twilio_main_col_manage_form_enabled').val(enabled);
    $('#twilio_main_col_manage_form_sid').val(sid);
    $('#twilio_main_col_manage_form_token').val(token);
    $('#twilio_main_col_manage_form_service').val(service);
    $('#twilio_main_col_manage_form_msg_service_id').val(msg_service_id);
    $('#twilio_main_col_manage_form_contact_field').val(contact_field);

    // Post submission form
    $('#twilio_main_col_manage_form').submit();
  }

});
