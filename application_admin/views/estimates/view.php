<?php
$this->load->view('estimates/_tabs');
echo validation_errors();
echo form_open('/estimates/material_cost/process', array('id' => 'material_type_add_form'));
print_form_container_open();

echo form_hidden('id', $estimate->id);

print_static_form_element('Enquiry Ref', $estimate->enquiry_id);

if (has_capability('estimates:canseecompanydetails')) //TODO add this capability
{
	print_form_section_heading('Company Details');
	print_static_form_element('Enquirer Name', set_value('company_name'));
	print_static_form_element('Enquirer Address 1', set_value('address_address1'));
	print_static_form_element('Enquirer Address 2', set_value('address_address2'));
	print_static_form_element('City', set_value('address_city'));
	print_static_form_element('Province/State/County', set_value('address_state'));
	print_static_form_element('Post Code', set_value('address_postcode'));
	print_static_form_element('Country', set_value('address_country_id'));
	print_static_form_element('Contact Title', set_value('user_salutation'));
	print_static_form_element('Contact First Name', set_value('user_first_name'));
	print_static_form_element('Contact Last Name', set_value('user_surname'));
}

print_form_section_heading('Estimate Details');
print_static_form_element('Estimate Date', $estimate->creation_date);
print_static_form_element('Version', $estimate->version);

print_input_element('Name', array('name' => 'name', 'size' => 60, 'maxlength' => 60), true);
print_textarea_element('Description', array('name' => 'description', 'cols' => 60, 'rows' => 8));
print_input_element('Price Breaks (seperated by commas)',
	array('name' => 'price_breaks', 'size' => 60, 'maxlength' => 255), true);

print_submit_container_open();
    echo form_submit('submit', 'Submit', 'id="submit_button"');
    //echo form_button(array('name' => 'button', 'content' => 'Back to Material Types list', 'onclick' => "window.location='/estimates/material_type/browse';"));
    print_submit_container_close();

print_form_container_close();

echo form_close();
