$(document).ready(function() {

	$('select[data-ajax-url]').on('change', function() {
		var url = $(this).data('ajax-url'),
			$receiver = $('#' + $(this).data('receiver'));

		$receiver.attr('disabled', true).html('<option>-- LOADING --</option>');

		$.ajax({
			url: url + '/' + $(this).val(),
			type: 'GET',
			dataType: 'json',
		})
		.done(function(data) {
			var html = '<option value="">-- Please select --</option>';
			$.each(data, function(index, value) {
				html += '<option value="' + index + '">' + value + '</option>';
			});
			$receiver.html(html).attr('disabled', false).focus();
		});
	});

});