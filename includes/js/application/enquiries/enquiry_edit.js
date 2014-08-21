$(document).ready(function() {
    $('.date_input').datepicker();
/*
    $("#submit_button").click(function(e) {
        e.preventDefault();
        var values = {};
        $('#enquiry_edit_form .required > input,select,textarea').each(function() {
            values[$(this).attr('name')] = $(this).val();
        });
        $('#enquiry_edit_form input[type=hidden]').each(function() {
            values[$(this).attr('name')] = $(this).val();
        });

        $.post($('#enquiry_edit_form').attr('action'), values, function(result) {
            json = $.parseJSON(result);
            if (json.result == 'error') {
                alert(json.message);
            }
            display_message(json.result, json.message);
        });
    });
    */
});
