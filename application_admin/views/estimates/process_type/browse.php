<h1><?= $title ?></h1>

<a class="action-icon add"></a>
<br><br>
<div <?= ! $add ? 'class="hidden"' : '' ?> style="width: 480px">
	<? echo validation_errors() ?>
	<? echo form_open('/estimates/process_type/process_add', array('id' => 'process_type_add_form')) ?>

		<? print_form_container_open(); ?>

			<? print_input_element('Type', array('name' => 'name', 'size' => 60, 'maxlength' => 60), true); ?>

			<? print_submit_container_open(); ?>
		    	<?= form_submit('submit', 'Add', 'data-url="estimates/process_type/process_add"'); ?>
		    <? print_submit_container_close(); ?>

		 <? print_form_container_close(); ?>

	<? echo form_close() ?>
	<hr>
</div>
<table class="dataTable tbl">
	<thead>
		<tr>
			<th style="width: 120px">Type</th>
			<th style="width: 120px">SubType</th>
			<th style="width: 120px">Created By</th>
			<th style="width: 120px">Last Edited By</th>
			<th style="width: 120px"></th>
		</tr>
	</thead>
	<tbody></tbody>
</table>
<script>
	var aoColumns = [
		null,
		{ bSortable: false },
		null,
		null,
		null,
	];
	var dataTableUrl = 'estimates/process_type/datatable';
</script>