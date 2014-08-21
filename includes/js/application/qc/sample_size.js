$(document).ready(function() {
	$('table').on('change', 'input[name^=qty]', function() {
		var $row = $(this).closest('tr'),
			$nextRow = $row.next(),
			val = parseInt($(this).val(), 10);
		if ( ! val) {
			$(this).val('');
			$row.nextAll('tr').remove();
		}
		if( ! $nextRow.length) {
			$nextRow = $row.clone();
			$nextRow.find('input[name^=qty]').val('');
			$row.after($nextRow);
		}
		$nextRow.find('td').first().text(val + 1);
	});
});