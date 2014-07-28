<h1><?= $title ?></h1>
<?php
echo validation_errors();
echo form_open('/estimates/process_subtype/process/' . $process_subtype->id, array('id' => 'process_subtype_edit_form'));
print_form_container_open();

echo form_hidden('process_type_id', $process_subtype->process_type_id);
print_input_element('Name', array('name' => 'name', 'size' => 60, 'maxlength' => 60), true);

print_submit_container_open();
    echo form_submit('submit', 'Submit', 'id="submit_button"');
    echo form_button(array('name' => 'button', 'content' => 'Back to Process Sub-types list', 'onclick' => "window.location='/estimates/process_type/edit/{$process_subtype->type->id}';"));
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
	var dataTableUrl = 'estimates/process_subtype/datatable/<?= $id ?>';
</script>
