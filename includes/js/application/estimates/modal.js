$(document).ready(function() {

	$('a.modal').on('click', function(event) {
		event.preventDefault();
		var modal = $(this).attr('href');

		$('div.modal').hide();
		$(modal).css({
			'top': ($(window).height() - $(modal).height()) / 2 - 100,
			'left': '200px' //($(window).width() - $(modal).width()) / 2 - 100
		})
		.show();

		$('#mask').fadeIn(500);
	});

	$('.close').on('click', function(event) {
		event.preventDefault();
		$('#mask').fadeOut(500);
		$('div.modal').hide();
	});

	/*
		|--------------------------------------------------------------------------
		| Setup dropdowns
		|--------------------------------------------------------------------------
	*/

	var html;
	html = '<option value>--New Sub-Assembly --</option>';
	$.each(subassemblies, function() {
		html += '<option value="' + this.id + '">' + this.name + '</option>';
	});
	$('#subassembly_id').html(html);

	html = '<option value>-- New Part --</option>';
	$.each(parts, function() {
		console.log(this);
		html += '<option value="' + this.id + '">' + this.cost.code + ' | ' + this.name + '</option>';
	});
	$('#part_id').html(html);

	html = '<option value>-- New Material --</option>';
	$.each(materials, function() {
		console.log(this);
		html += '<option value="' + this.id + '">' + this.cost.id + ' | ' + this.name + '</option>';
	});
	$('#material_id').html(html);

	html = '<option value>-- New Process --</option>';
	$.each(processes, function() {
		console.log(this);
		html += '<option value="' + this.id + '">' + this.cost.id + ' | ' + this.name + '</option>';
	});
	$('#process_id').html(html);

	/*
		|--------------------------------------------------------------------------
		| AJAX code for Materials, Processes, Parts, and SubAssembly Modals
		|--------------------------------------------------------------------------
	*/

	$('#subassembly_id').on('change', function() {
		var id = $(this).val();
		console.log(id);
		if (id) {
			$.each(subassemblies, function() {
				if (this.id !== id) return true;

				$('#add_subassembly [name="name"]').val(this.name);
				$('#add_subassembly [name="description"]').val(this.description);

				$('#add_subassembly .lockable')
					.attr('disabled', true);
			});
		} else {
			$('#add_subassembly .lockable')
				.val('')
				.attr('disabled', false);
		}
	});

	$('#part_id').on('change', function() {
		var id = $(this).val();
		if (id) {
			$.each(parts, function() {
				if (this.id !== id) return true;

				$('#add_part [name="code"]').val(this.cost.code);
				$('#add_part [name="name"]').val(this.name);
				$('#add_part [name="description"]').val(this.description);
				$('#add_part [name="source"]').val(this.cost.source);

				$('#add_part .hideable').hide();
				$('#add_part .lockable').attr('disabled', true);
			});
		} else {
			$('#add_part .hideable').show();
			$('#add_part .lockable').val('').attr('disabled', false);
		}
	});

	$('#material_id').on('change', function() {
		var id = $(this).val();
		if (id) {
			$.each(materials, function() {
				if (this.id !== id) return true;

				$('#add_material [name="code"]').val(this.cost.code);
				$('#add_material [name="name"]').val(this.name);
				$('#add_material [name="description"]').val(this.description);

				$('#add_material .hideable-parent').attr('disabled', true).closest('tr').hide();

				$('#add_material .lockable').attr('disabled', true);
			});
		} else {
			$('#add_material .hideable-parent').attr('disabled', false).closest('tr').show();
			$('#add_material .lockable').val('').attr('disabled', false);
		}
	});

	$('#process_id').on('change', function() {
		var id = $(this).val();
		if (id) {
			$.each(processes, function() {
				if (this.id !== id) return true;

				$('#add_process [name="code"]').val(this.cost.code);
				$('#add_process [name="name"]').val(this.name);
				$('#add_process [name="description"]').val(this.description);

				$('#add_process .hideable-parent').attr('disabled', true).closest('tr').hide();
				$('#add_process .lockable').attr('disabled', true);
			});
		} else {
			$('#add_process .hideable-parent').attr('disabled', false).closest('tr').show();
			$('#add_process .lockable').val('').attr('disabled', false);
		}
	});

	$('.ajax-form').on('submit', function(event) {
		event.preventDefault();
		var data = $(this).serialize(),
			url = $(this).attr('action');

		console.log(url, data);
		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: data,
		})
		.done(function() {
			console.log("success");
			$('#mask, .modal').fadeOut(500);
			window.location.reload();
		})
		.fail(function() {
			console.log("error");
		})
		.always(function() {
			console.log("complete");
		});
	});

	$('.delete').on('click', function(event) {
		event.preventDefault();
		if ( ! confirm('Are you sure you want to remove this item from the assembly?')) {
			return false;
		}
		var $row = $(this).closest('tr');

		$.ajax({
			url: $(this).attr('href'),
			type: 'POST',
			dataType: 'json',
		})
		.done(function() {
			console.log("success");
			$row.remove();
		})
		.fail(function() {
			console.log("error");
		})
		.always(function() {
			console.log("complete");
		});

	});

	$('.change-qty').on('click', function(event) {
		event.preventDefault();
		var qty = $(this).text(),
			$input = $('<input type="number">').addClass('input-mini').val(qty);
			$link = $(this);

		$link.hide().after($input);

		$input.focus().select().on('blur keyup', function(event) {
			console.log(event);
			if (event.type === 'keyup' && event.keyCode !== 13) {
				return false;
			}
			var newQty = $(this).val();

			if (newQty === qty) {
				$link.show();
				$input.remove();
				return  false;
			}

			$.ajax({
				url: $link.attr('href'),
				type: 'POST',
				dataType: 'json',
				data: {qty: newQty}
			})
			.done(function() {
				console.log("success");
				$link.text(newQty).show();
				$input.remove();
			})
			.fail(function() {
				console.log("error");
			})
			.always(function() {
				console.log("complete");
			});

		});
	});


});