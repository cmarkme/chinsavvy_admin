<h1><?= $title ?></h1>
<?php
echo validation_errors();
echo form_open('/estimates/material_type/process', array('id' => 'material_type_edit_form'));
print_form_container_open();

echo form_hidden('id', $material_type->id);
print_input_element('Type', array('name' => 'name', 'size' => 60, 'maxlength' => 60), true);

print_submit_container_open();
    echo form_submit('submit', 'Submit', 'id="submit_button"');
    echo form_button(array('name' => 'button', 'content' => 'Back to Material Types list', 'onclick' => "window.location='/estimates/material_type/browse';"));
    print_submit_container_close();

print_form_container_close();

echo form_close();
?>
<hr>
<h1>Add / Edit Grades</h1>
<a class="action-icon add"></a>
<br><br>
<div class="hidden" style="width: 480px">
	<? print_form_container_open(); ?>

		<? print_input_element('Grade', array('name' => 'grade_name', 'id' => 'name', 'size' => 60, 'maxlength' => 60), true); ?>

		<? print_submit_container_open(); ?>
	    	<?= form_submit('submit', 'Add', 'id="add_button" data-url="estimates/material_grade/process_add/' . $material_type->id . '"'); ?>
	    <? print_submit_container_close(); ?>

	 <? print_form_container_close(); ?>
	<hr>
</div>
<table class="dataTable tbl">
	<thead>
		<tr>
			<th>Grade</th>
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
	var dataTableUrl = 'estimates/material_grade/datatable/<?= $material_type->id ?>';
</script>
