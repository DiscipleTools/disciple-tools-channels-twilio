jQuery(function ($) {

  $(document).on('click', '#twilio_main_col_manage_sid_show', function () {
    show_secrets($('#twilio_main_col_manage_sid'), $('#twilio_main_col_manage_sid_show'));
  });

  $(document).on('click', '#twilio_main_col_manage_token_show', function () {
    show_secrets($('#twilio_main_col_manage_token'), $('#twilio_main_col_manage_token_show'));
  });


  function show_secrets(input_ele, show_ele) {
    if (show_ele.is(':checked')) {
      input_ele.attr('type', 'text');
    } else {
      input_ele.attr('type', 'password');
    }
  }

});
