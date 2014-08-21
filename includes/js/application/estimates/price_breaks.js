$(document).ready(function() {

	$('.price-breaks-form .add').on('click', function() {
		var $tr = $(this).closest('tr'),
			qty = $tr.find('.qty').val(),
			price = $tr.find('.price').val();

		if (! $.isNumeric(qty) || ! $.isNumeric(price)) return false;

		$clone = $tr.clone();
		$clone.find('button').replaceWith('<button type="button" class="remove button-small">Remove</button>');
		$tr.before($clone)
			.find('input').val('').end()
			.find('.qty').focus();
	});

	$('.price-breaks-form').on('click', '.remove', function() {
		$(this).closest('tr').remove();
	});

});