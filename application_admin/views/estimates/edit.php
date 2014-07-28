<?php
$this->load->view('estimates/_tabs');
echo validation_errors();
echo form_open('/estimates/estimate/update/' . $estimate->id, array('id' => 'estimate_edit_form'));
print_form_container_open();

echo form_hidden('id', $estimate->id);

print_static_form_element(
	'Enquiry Ref',
	'<a href="/estimates/estimate/versions/' . $estimate->enquiry_id . '">' . $estimate->enquiry_id . '</a>'
	);

if (has_capability('estimates:canseecompanydetails')) //TODO add this capability
{
	print_form_section_heading('Company Details');
	print_static_form_element('Enquirer Name', $enquiry_data['company_name']);
	print_static_form_element('Enquirer Address 1', $enquiry_data['address_address1']);
	print_static_form_element('Enquirer Address 2', $enquiry_data['address_address2']);
	print_static_form_element('City', $enquiry_data['address_city']);
	print_static_form_element('Province/State/County', $enquiry_data['address_state']);
	print_static_form_element('Post Code', $enquiry_data['address_postcode']);
	print_static_form_element('Country', $enquiry_data['address_country_name']);
	print_static_form_element('Contact Title', $enquiry_data['user_salutation']);
	print_static_form_element('Contact First Name', $enquiry_data['user_first_name']);
	print_static_form_element('Contact Last Name', $enquiry_data['user_surname']);
}

print_form_section_heading('Estimate Details');
print_static_form_element('Estimate Date', $estimate->creation_date);
print_static_form_element('Version', $estimate->version);

print_input_element('Name', array('name' => 'name', 'size' => 60, 'maxlength' => 60), true);
print_textarea_element('Description', array('name' => 'description', 'cols' => 60, 'rows' => 8));
print_input_element('Price Breaks (seperated by commas)',
	array('name' => 'price_breaks', 'size' => 60, 'maxlength' => 255), true);


// print_dropdown_element('material_type_id', 'Material Type', Eloquent\MaterialType::lists('name', 'id'), true);
// print_dropdown_element('material_grade_id', 'Material Grade', Eloquent\MaterialGrade::getDropdown($material_cost->material_type_id), true);
// print_input_element('Form', array('name' => 'form', 'size' => 60, 'maxlength' => 60), true);
// print_dropdown_element('measurement_unit_id', 'Measurement Unit', Eloquent\MeasurementUnit::lists('name', 'id'), true);

print_submit_container_open();
    echo form_submit('submit', 'Update', 'id="submit_button"');
    //echo form_button(array('name' => 'button', 'content' => 'Back to Material Types list', 'onclick' => "window.location='/estimates/material_type/browse';"));
    print_submit_container_close();

print_form_container_close();

echo form_close();
