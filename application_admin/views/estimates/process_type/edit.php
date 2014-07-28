<h1><?= $title ?></h1>
<?php
echo validation_errors();
echo form_open('/estimates/process_type/process', array('id' => 'process_type_edit_form'));
print_form_container_open();

echo form_hidden('id', $process_type->id);
print_input_element('Type', array('name' => 'name', 'size' => 60, 'maxlength' => 60), true);

print_submit_container_open();
    echo form_submit('submit', 'Submit', 'id="submit_button"');
    echo form_button(array('name' => 'button', 'content' => 'Back to Process Types list', 'onclick' => "window.location='/estimates/process_type/browse';"));
    print_submit_container_close();

print_form_container_close();

echo form_close();
?>
<hr>
<h1>Add / Edit Sub-Types</h1>
<a class="action-icon add"></a>
<br><br>
<div class="hidden" style="width: 480px">
	<? print_form_container_open(); ?>

		<? print_input_element('Sub-Type', array('name' => 'grade_name', 'id' => 'name', 'size' => 60, 'maxlength' => 60), true); ?>

		<? print_submit_container_open(); ?>
	    	<?= form_submit('submit', 'Add', 'id="add_button" data-url="estimates/process_subtype/process_add/' . $process_type->id . '"'); ?>
	    <? print_submit_container_close(); ?>

	 <? print_form_container_close(); ?>
	<hr>
</div>
<table class="dataTable tbl">
	<thead>
		<tr>
			<th>Sub-Types</th>
			<th>Created By</th>
			<th>Last Edited By</th>
			<th></th>
		</tr>
	</thead>
	<tbody></tbody>
</table>

<script>
	var aoColumns = [
		null,
		null,
		null,
		null,
	]
	var dataTableUrl = 'estimates/process_subtype/datatable/<?= $process_type->id ?>';
</script>
