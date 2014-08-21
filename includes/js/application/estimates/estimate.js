$(document).ready(function() {

	$('.tbl').on('click', '.duplicate', function(event) {
		event.preventDefault();
		if ( ! confirm('Are you sure you want to duplicate this estimate?'))
		{
			return false;
		}

		$.ajax({
			url: $(this).attr('href'),
			type: 'POST',
		})
		.done(function() {
			dataTable.fnDraw();
		})
		.fail(function() {
			alert('Could not duplicate this estimate');
		});

	});

});