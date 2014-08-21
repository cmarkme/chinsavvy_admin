$(document).ready(function() {
    // Specify width of th
    $("#entry_form th").css('width', '240px');

    form_errors = {};
    email_status = {corporate: null, technical: null};
    regex_patterns = {company_code: /^[A-Z][A-Z]$/, email: /^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/};

    // Are we adding or editing?
    var mode = ($('[name="company_id"]').attr('value') > 0) ? 'edit' : 'add';

    var corporate_email_input = make_autocomplete_input('corporate', 'corporate_contact_user_email', '', 'Corporate contact email', '/codes/customer/email_suggest', autocomplete_callback);
    var technical_email_input = make_autocomplete_input('technical', 'technical_contact_user_email', '', 'Technical contact email', '/codes/customer/email_suggest', autocomplete_callback);

    $(corporate_email_input.input).val($("#corporate").html());
    $(technical_email_input.input).val($("#technical").html());

    // Only replace existing email input if the user's id isn't set in the form
    if ($("#corporate_email").html().length == 0) {
        $("#corporate_email").replaceWith(corporate_email_input.input);
        $("#js_corporate").attr('id', 'corporate');
    }

    if ($("#technical_email").html().length == 0) {
        $("#technical_email").replaceWith(technical_email_input.input);
        $("#js_technical").attr('id', 'technical');
    }

    function autocomplete_callback(event, ui) { // Callback called when a value is entered
        var email = ui.item.value;
        fetch_customer_data(email);
    };

    /**
     * In addition to setting the value to disabled or null, it adds or removes a .disabled class
     */
    function enable_input(input, disabled) {
        if (disabled) {
            $(input).attr('disabled', 'disabled');
            $(input).addClass('disabled');
        } else {
            $(input).attr('disabled', null);
            $(input).removeClass('disabled');
        }
    }

    function update_email_status(type, status, callingcode) {
        othertype = (type == 'corporate') ? 'technical' : 'corporate';

        // Remove any warning on this contact field
        $(".error").remove();

        if (status == 'match' && email_status[othertype] == 'match') {
            status = 'match2';
            // We print a warning here: a second match will change the user's existing company association
            show_error(type + '_contact_user_email', 'This user is already associated with a different company!', 'input');
            // $("#"+type).parent().append('<span class="'+type+' warning">This user is already associated with a different company!</span>');
            form_errors[type+'_contact_user_email'] = "The " + type + " email address is already associated with a different company, please enter a different one.";
            // TODO prevent company fields from being filled

        } else if (status == null && email_status[othertype] == 'match2') {
            email_status[othertype] = 'match';
            // Also update customer fields now that we are using a different match
            fetch_customer_data($("#"+othertype).attr('value'));
        } else if (status == 'match') {
            // TODO For any match, place a link next to the email address for editing the user
        }

        email_status[type] = status;
    }

    /**
     * Main callback for updating contact fields
     *
     * @param string type (corporate|technical)
     * @param bool   disabled Whether the fields should be disabled or not
     * @param array  customer_values Optional array of values fetched by Ajax callback
     */
    function update_customer_fields(type, disabled, customer_values) {
        inputs = $(":regex(name, " + type + "_.*|company_.*|address_.*|shipping_address_.*)");
        othertype = (type == 'corporate') ? 'technical' : 'corporate';

        var email = $("#"+type).attr('value');
        var otheremail = $("#"+othertype).attr('value');

        // We can't allow the same email in both fields
        if (email.length > 0 && email == otheremail) {
            alert("You've entered the same email address for both contacts. This is not permitted.");
            $("#"+type).attr('value', '');
            $("#"+type).focus();
            return false;
        }

        // User field pattern
        patterns = [new RegExp(type + '_contact_(.*)')];

        // If a match is already found for the other user, do not change company fields
        if (email_status[othertype] != 'match') {
            patterns.push(new RegExp('company_(.*)'));
            patterns.push(new RegExp('address_(.*)'));
            patterns.push(new RegExp('shipping_address_(.*)'));
        }

        inputs.each(function (j) {

            // For each regex pattern, look up corresponding values in customer_values and apply them to form fields
            for (var i = 0; i < patterns.length; i++) {

                if (matches = this.name.match(patterns[i])) {

                    // For the user regex, we ignore the technical/corporate prefix
                    mymatch = (i == 0) ? matches[1] : matches[0];
                    disabled_here = disabled && disabled; // Avoiding reference assignment

                    if (mymatch == 'user_email') {
                        continue;
                    }

                    if (customer_values == null) {
                        if ($(this).attr('disabled') && $(this).val().length == 0) {
                            $(this).val('');
                        }
                    } else {
                        $(this).val(customer_values[mymatch]);
                        // If the company doesn't yet have a company code, leave the field enabled
                        if (mymatch == 'company_code' && customer_values.company_code == null) {
                            disabled_here = false;
                            $("input[name='existing_comp_id']").val(customer_values.company_id);
                        // If the company has a code, we must output an error message
                        } else if (mymatch == 'company_code' && customer_values.company_code.length > 0) {
                            console.debug(customer_values.company_code);
                            form_errors[type+'_contact_user_email_already'] =
                                    "The " + type + " email address is already associated with a different company, please enter a different one.";
                            show_error(type + '_contact_user_email', 'This company already has a Customer code!', 'input');
                            $("#existing_comp_id").val('');
                        }
                    }

                    // Only toggle disabled status of user fields. Leave company fields editable
                    if (i == 0) {
                        enable_input(this, disabled_here);
                    }
                }
            }
        });
    }

    // Based on which Suggested Email was selected, fetch the corresponding user fields from DB and populate form fields
    function fetch_customer_data(email) {

        // AJAX request to get user info
        $.ajax({
            type: "POST",
            url: '/codes/customer/get_user_details',
            data: 'email='+ email,
            success: function(data, responseStatus) {
                if (data.length > 0) {
                    user = $.evalJSON(data);

                    // Update email status variable
                    type = ($('#corporate').val() == email) ? 'corporate' : 'technical';

                    if (type !== null && type !== undefined) {
                        update_email_status(type, 'match', 'fetch_customer_data('+email+')');
                        $("."+type+".error").remove();
                        delete form_errors[type+'_contact_user_email'];
                        update_customer_fields(type, true, user);
                    }
                }
            }
        });
    }

    function validate_email(email) {
        return (email.length == 0) ? true : email.match(regex_patterns.email);
    }

    // When text is entered in the email fields, other contact fields are disabled and emptied
    function email_keypress_handler(e) {
        type = $(this).attr('id');
        email = $(this).attr('value');

        // Ignore Tab key
        if (e.which == 0) {
            return true;
        }

        update_email_status(type, 'editing', 'email_keypress_handler('+e.which+')');

        if (validate_email(email)) {
            if (email_status[type] == 'match') {
                // reset email status variable
                update_customer_fields(type, true, null);
            }
            $("." + type + ".error").remove();
            delete form_errors[type+'_contact_user_email'];
        }
    }

    $("#corporate").keypress(email_keypress_handler);
    $("#technical").keypress(email_keypress_handler);

    // When text is entered in the company code field, check for duplication
    function code_blur_handler(e) {
        $(".company_code.error").remove();
        delete form_errors.company_code;

        var code = $(this).attr('value');
        var input = this;

        if (code.length > 0 && !code.match(regex_patterns.company_code)) {
            show_error($(this).attr('name'), 'This company code is invalid. Must be two uppercase alphabetic characters.', 'input');
            form_errors.company_code = "This company code is invalid. Must be two uppercase alphabetic characters.";
            return false;
        }

        $.post("/codes/customer/company_code_check/", { code: code},
                function (data) {
                    // We have a match!
                    if (data !== null) {
                        show_error($(input).attr('name'), 'This company code is already taken by '+data+'!', 'input');
                        form_errors.company_code = 'This company code is already taken by '+data+'!'
                    }
                }, 'json');
    }

    $("input[name=company_code]").blur(code_blur_handler);

    // If focus on the email field is lost and no match has been selected, enable all fields
    function email_blur_handler(e) {
        var type = $(this).attr('id');
        var email = $(this).attr('value');
        var othertype = (type == 'corporate') ? 'technical' : 'corporate';

        // Email field is not empty and has a valid address
        if (email.length > 0 && validate_email(email)) {
            fetch_customer_data(email);

            // Enable and empty all fields for this contact
            if (email_status[type] != 'match') {
                update_customer_fields(type, false, null);
                update_email_status(type, 'new', 'email_blur_handler()');
            }
            $("." + type + ".error").remove();

        // Invalid and not empty
        } else if (email.length > 0) {
            if (email_status[type] != 'editing') {
                update_email_status(type, null, 'email_blur_handler()');
            }
            show_error(type + '_contact_user_email', 'Invalid email address!', 'input');

            update_customer_fields(type, true, null);

        // Empty email field
        } else {
            $(".error").remove();
            delete form_errors[type+'_contact_user_email'];

            // In all cases, first empty the contact details and update the email status of this contact to NULL
            update_email_status(type, null, 'email_blur_handler()');
            update_customer_fields(type, true, null); // This empties the current contact fields

            // Other field is empty or invalid: do nothing
            if (email_status[othertype] === null) {
                return false;
            }

            // Other field is a match, load the customer fields with it
            if (email_status[othertype] == 'match') {
                fetch_customer_data($("#"+othertype).attr('value'));

            // Other field is a valid but new address: enable and empty customer fields
            } else if (email_status[othertype] == 'new') {
                update_customer_fields(othertype, false, null);

            }
        }
    }

    $("#corporate").blur(email_blur_handler);
    $("#technical").blur(email_blur_handler);

    // Validation: required fields depend on the state of email fields
    $("#customer_edit_form").ajaxForm( {
            beforeSubmit: validateCustomerForm,
            success: customerFormSuccess
            } );

    /**
     * Here are the rules:
     * 1. Corporate contact email is ALWAYS required
     * 2. The following fields are required if Corporate email AND Technical email are NOT A MATCH and at least one of them is filled and valid.
     *   a. Company Code
     *   b. Company Name
     *   c. City
     *   d. Country
     * 3. The following fields are required if Corporate email is NOT a MATCH but is valid:
     *   a. Corporate First name
     *   b. Corporate Last Name
     * 4. The following fields are required if Technical email is NOT a MATCH but is valid:
     *   a. Technical First name
     *   b. Technical Last Name
     * 5. The company code is required if Corporate OR technical is a Match but the fetched company does not yet have a code
     *
     * Note, it seems easier to rely on the disabled state of fields to know if a field is required, rather than complex logic as described above
     */
    function validateCustomerForm(formData, jqForm, options) {
        params = {};
        $('#submit_button').attr('disabled', 'disabled');
        for (param in formData) {
            if (formData[param].value.length == 0) {
                params[formData[param].name] = ' ';
            } else if (formData[param].name != 'button') {
                params[formData[param].name] = formData[param].value;
            }
        }

        delete form_errors.corporate_contact_user_email;
        delete form_errors.technical_contact_user_email;

        if (params.corporate_contact_user_email == ' ') {
            form_errors.corporate_contact_user_email = "You must enter an email for the Corporate Contact";

        } else if (!params.corporate_contact_user_email.match(regex_patterns.email)) {
            form_errors.corporate_contact_user_email = "Please enter a Valid email address for the Corporate Contact";
        }

        delete form_errors.company_code;
        if (!$("input[name=company_code]").attr('disabled')) {
            if (params.company_code == ' ') {
                form_errors.company_code = "You must enter a Company Code";

            } else if (!params.company_code.match(regex_patterns.company_code)) {
                form_errors.company_code = "This company code is invalid. It must be two uppercase alphabetic characters.";

            } else { // We'll let the PHP backend give the error of duplicate company code. The inline message should have been warning enough

            }
        }

        delete form_errors.company_name;

        if (!$("input[name=company_name]").attr('disabled') && params.company_name == ' ') {
            form_errors.company_name = "You must enter a Company Name";
        }

        delete form_errors.address_city;
        if (!$("input[name=address_city]").attr('disabled') && params.address_city == ' ') {
            form_errors.address_city = "You must enter a City for this Company";
        }

        delete form_errors.address_country_id;
        if (!$("select[name=address_country_id]").attr('disabled') && params.address_country_id == ' ') {
            form_errors.address_country_id = "You must select a Country for this Company";
        }

        delete form_errors.corporate_contact_first_name;
        if (!$("input[name=corporate_contact_first_name]").attr('disabled') && params.corporate_contact_first_name == ' ') {
            form_errors.corporate_contact_first_name = "You must enter the First Name of the Corporate Contact";
        }

        delete form_errors.corporate_contact_surname;
        if (!$("input[name=corporate_contact_surname]").attr('disabled') && params.corporate_contact_surname == ' ') {
            form_errors.corporate_contact_surname = "You must enter the Last Name of the Corporate Contact";
        }

        delete form_errors.technical_contact_first_name;
        if (!$("input[name=technical_contact_first_name]").attr('disabled') && params.technical_contact_first_name == ' ') {
            form_errors.technical_contact_first_name = "You must enter the First Name of the Technical Contact";
        }

        delete form_errors.technical_contact_surname;
        if (!$("input[name=technical_contact_surname]").attr('disabled') && params.technical_contact_surname == ' ') {
            form_errors.technical_contact_surname = "You must enter the Last Name of the Technical Contact";
        }

        found_errors = false;

        for (var field in form_errors) {
            found_errors = true;
            show_error(field, form_errors[field], 'input');
        }

        if (found_errors) {
            console.debug(form_errors);
            return false;
        } else {
            return true;
        }
    }

    function customerFormSuccess(responseText, statusText, xhr, formElement) {
        window.location = '/codes/customer';
    }

    /**
     * Appends a span with error class and a message next to the element with the given name
     */
    function show_error(input_name, msg, element_type) {
        $("." + input_name + ".error").remove();
        $(element_type + "[name=" + input_name + "]").parent().append('<span class="error ' + input_name + '">' + msg + '</span>');
    }

    // TODO write remove_error function
});

