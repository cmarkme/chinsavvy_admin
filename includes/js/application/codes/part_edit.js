$(document).ready(function() {
    $('.date_input').datepicker();
    $('select[name="status_text_selector"]').bind('change', function() {
        $('input[name="status_text"]').val($(this).val());
    });
    $('select[name="project_id"]').bind('change', function() {
        // TODO Instead of using window.location, use an AJAX request to prefill fields. Don't forget that this only applies for existing parts, and actually duplicates the existing part when a new project is selected
    });
});
