$(document).ready(function() {
    $('.date_input').datepicker();
    $('select[name="status_text_selector"]').bind('change', function() {
        $('input[name="status_text"]').val($(this).val());
    });
});
