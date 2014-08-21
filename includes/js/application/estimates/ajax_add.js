$(document).ready(function() {

	$('.add').on('click', function() {
		$('.hidden').slideToggle(250);
	});

	$('#add_button').on('click', function() {
		var name = $('#name').val();
		if (name.length < 1) {
			alert('Name can not be blank.');
			return false;
		}
		$.ajax({
			url: $(this).data('url'),
			type: 'POST',
			dataType: 'json',
			data: {name: name},
		})
		.done(function() {
			dataTable.fnDraw();
			$('#name').val('');
		})
		.fail(function() {
			console.log("error");
		});
	});

	$('.tbl').on('click', '.delete', function(e) {
		e.preventDefault();
		if ( ! confirm('Are you sure you want to delete this item?')) {
			return false;
		}
		$.ajax({
			url: $(this).attr('href'),
			type: 'POST',
			dataType: 'json',
		})
		.done(function() {
			dataTable.fnDraw();
		})
		.fail(function() {
			console.log("error");
		});

		return false;
	});
});