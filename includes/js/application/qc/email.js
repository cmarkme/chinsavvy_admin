function update_form(event) {
    $('#email_settings').trigger('submit');
}

$(document).ready(function() {
    $('#project_id').bind('change', update_form);
    $('#report_type').bind('change', update_form);
    $('#revision_id').bind('change', update_form);
    $('#category_id').bind('change', update_form);
    $('#supplier_email').bind('change', update_form);
    $('#lang').bind('change', update_form);

    $('#reset_body').bind('click', function() {
        $('textarea[name=email_body]').val(bodies[report_type]);
    });
});
