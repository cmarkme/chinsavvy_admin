function check_email(email_input) {
    var params = {};

    if (undefined == this.value) {
        params.email = $(email_input).val();
    } else {
        params.email = this.value;
    }

    $.post('/users/user/check_email/', params, function(data) {
        if (data.user_email != undefined) {
            var div = document.createElement('div');
            $(div).addClass('form-required');
            $(div).addClass('warning');
            $(div).attr('style', 'font-weight: normal;');
            $(div).html('This email address ('+data.user_email+') already exists in our records. The form has been prefilled accordingly.  If you know the password for this account, you may login now using the box on the left.<br /><br />Otherwise, please fill out any remaining required fields and submit your enquiry.');
            $(div).insertAfter($('input[name="user_email"]').parent());

            $('input[name="login_email"]').attr('value', data.user_email);
            $('select[name="user_salutation"]').val(data.user_salutation);
            $('input[name="user_first_name"]').val(data.user_first_name);
            $('input[name="user_surname"]').val(data.user_surname);
            $('input[name="user_phone"]').val(data.user_phone);
            $('input[name="user_mobile"]').val(data.user_mobile);
            $('input[name="user_fax"]').val(data.user_fax);
            $('input[name="company_name"]').val(data.company_name);
            $('select[name="company_type"]').val(data.company_company_type);
            $('input[name="company_url"]').val(data.company_url);
            $('input[name="address_address1"]').val(data.address_address1);
            $('input[name="address_address2"]').val(data.address_address2);
            $('input[name="address_city"]').val(data.address_city);
            $('input[name="address_state"]').val(data.address_state);
            $('input[name="address_postcode"]').val(data.address_postcode);
            $('select[name="address_country_id"]').val(data.address_country_id);

            // Disable fields have been prefilled
            for (name in data) {
                if ($('input[name="'+name+'"]').val() != '') {
                    $('input[name="'+name+'"]').parent().hide();
                    $('input[name="'+name+'"]').parent().removeClass('form-optional');
                }
                if (data[name] != '') {
                    $('select[name="'+name+'"]').parent().hide();
                    $('select[name="'+name+'"]').parent().removeClass('form-optional');
                }
                if (data.company_company_type != '' && typeof data.company_company_type != 'undefined') {
                    console.debug(data);
                    $('select[name="company_type"]').parent().hide();
                    $('select[name="company_type"]').parent().removeClass('form-optional');
                }
            }

            // Disable registration fields if user already has a username/password
            if (data.registered) {
                $('input[name="register"]').parent().hide();
                $('input[name="password"]').parent().hide();
                $('input[name="password_confirm"]').parent().hide();
                $('select[name="enquiry_source"]').parent().hide();
            }

            // Remove focus event from email address field
            $('input[name="user_email"]').unbind();
        }
    }, 'json');
}

$(document).ready(function() {
    // When email address has been entered, check if it identifies an existing user. If yes, offer to pre-fill form
    var email_input = $('input[name="user_email"]');
    if ($(email_input).val() == '') {
        $(email_input).bind('blur', check_email);
    } else {
        check_email(email_input);
    }

    $('#register_checkbox').bind('change', toggle_password);
});

var register = true;
var password = '';
var password_confirm = '';

function toggle_password() {
    var checkboxstate = $('#register_checkbox').attr('checked');
    var pw = $('#password_field');
    var pwrow = $(pw).parent();
    var pwc = $('#password_confirm_field');
    var pwcrow = $(pwc).parent();

    if (checkboxstate) {
        $(pwcrow).show();
        $(pwrow).show();
        $('input[name="password"]').val(password);
        $('input[name="password_confirm"]').val(password_confirm);
        register = true;
    } else {
        $(pwcrow).hide();
        $(pwrow).hide();
        password = $('input[name="password"]').val();
        password_confirm = $('input[name="password_confirm"]').val();
        $('input[name="password"]').val('');
        $('input[name="password_confirm"]').val('');
        register = false;
    }
}
