$('#printer_friendly').click(function(event) {
    event.preventDefault();
    $(this).parent().trigger('submit');
});
