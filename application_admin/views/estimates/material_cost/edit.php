<h1><?= $title ?></h1>
<?php
echo validation_errors();
echo form_open('/estimates/material_cost/process', array('id' => 'process_type_add_form'));
print_form_container_open();

echo form_hidden('id', $material_cost->id);

print_static_form_element('ID', $material_cost->id);
print_dropdown_element('material_type_id', 'Material Type', Eloquent\MaterialType::getDropdown('name'), true,
	array('data-ajax-url' => 'estimates/material_grade/dropdown/', 'data-receiver' => 'material_grade_id'));
print_dropdown_element('material_grade_id', 'Material Grade', Eloquent\MaterialGrade::getDropdown($material_cost->material_type_id)
	, true, array('id' => 'material_grade_id'));
print_input_element('Form', array('name' => 'form', 'size' => 60, 'maxlength' => 60), true);
print_dropdown_element('measurement_unit_id', 'Measurement Unit', Eloquent\MeasurementUnit::getDropdown('name'), true);
print_input_element('Source', array('name' => 'source', 'size' => 60, 'maxlength' => 60), true);

$this->load->view('estimates/_price_breaks_form', array('price_breaks' => $material_cost->price_breaks));

print_submit_container_open();
    echo form_submit('submit', 'Submit', 'id="submit_button"');
    //echo form_button(array('name' => 'button', 'content' => 'Back to Material Types list', 'onclick' => "window.location='/estimates/process_type/browse';"));
    print_submit_container_close();

print_form_container_close();

echo form_close();
?>
