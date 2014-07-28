<h1><?= $title ?></h1>
<?php
echo validation_errors();
echo form_open('/estimates/process_cost/process', array('id' => 'process_type_add_form'));
print_form_container_open();

echo form_hidden('id', $process_cost->id);

print_static_form_element('ID', $process_cost->id);
print_dropdown_element('process_type_id', 'Process Type', Eloquent\ProcessType::getDropdown('name'), true,
	array('data-ajax-url' => 'estimates/process_subtype/dropdown/', 'data-receiver' => 'process_subtype_id'));
print_dropdown_element('process_subtype_id', 'Process Sub-Type', Eloquent\ProcessSubtype::getDropdown($process_cost->process_type_id), true,
	array('id' => 'process_subtype_id'));
print_input_element('Machine Size', array('name' => 'machine_size', 'size' => 60, 'maxlength' => 60), true);
print_input_element('Process Action', array('name' => 'action', 'size' => 60, 'maxlength' => 60), true);
print_input_element('Tooling Cost', array('name' => 'tooling_cost', 'size' => 20, 'maxlength' => 20), true);
print_input_element('Source', array('name' => 'source', 'size' => 60, 'maxlength' => 60), true);

$this->load->view('estimates/_price_breaks_form', array('price_breaks' => $process_cost->price_breaks));

print_submit_container_open();
    echo form_submit('submit', 'Submit', 'id="submit_button"');
    //echo form_button(array('name' => 'button', 'content' => 'Back to Material Types list', 'onclick' => "window.location='/estimates/process_type/browse';"));
    print_submit_container_close();

print_form_container_close();

echo form_close();
?>
