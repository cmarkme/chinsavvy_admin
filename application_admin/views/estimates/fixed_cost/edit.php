<h1><?= $title ?></h1>
<?php
echo validation_errors();

echo form_open('/estimates/fixed_cost/update/' . $fixed_cost_id, array('id' => 'fixed_cost_edit_form'));
print_form_container_open();

	print_dropdown_element('type', 'Type', $types_dropdown, true);
	print_textarea_element('Description', array('name' => 'description', 'cols' => 60, 'rows' => 10), true);
	print_input_element('Cost', array('name' => 'cost', 'size' => 20, 'maxlength' => 20), true);

	print_submit_container_open();
	    echo form_submit('submit', 'Submit', 'id="submit_button"');
	    //echo form_button(array('name' => 'button', 'content' => 'Back to Material Types list', 'onclick' => "window.location='/estimates/material_type/browse';"));
	print_submit_container_close();


print_form_container_close();
echo form_close();
