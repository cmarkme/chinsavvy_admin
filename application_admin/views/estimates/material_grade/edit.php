<h1><?= $title ?></h1>
<?php
echo validation_errors();
echo form_open('/estimates/material_grade/process/' . $material_grade->id, array('id' => 'material_grade_edit_form'));
print_form_container_open();

echo form_hidden('material_type_id', $material_grade->material_type_id);
print_input_element('Name', array('name' => 'name', 'size' => 60, 'maxlength' => 60), true);

print_submit_container_open();
    echo form_submit('submit', 'Submit', 'id="submit_button"');
    echo form_button(array('name' => 'button', 'content' => 'Back to Material Grades list', 'onclick' => "window.location='/estimates/material_type/edit/{$material_grade->type->id}';"));
    print_submit_container_close();

print_form_container_close();

echo form_close();
?>
<script>
	var aoColumns = [
		null,
		null,
		null,
		null,
	]
	var dataTableUrl = 'estimates/material_grade/datatable/<?= $id ?>';
</script>
