<?php
echo form_open('/estimates/estimate/insert/' . $enquiry_data['enquiry_id'], array('id' => 'enquiry_add_form'));
print_form_container_open();
echo form_hidden('enquiry_id', $enquiry_data['enquiry_id']);
if ( is_null($enquiry_data) )
{
	print_dropdown_element(
	'enquiry_prefill',
	'Enquiry Ref No.',
	$dropdowns['enquiries'],
	false,
	'onchange="window.location=\'/estimates/estimate/add/\'+$(this).val();"'
	);
}
else
{
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

	print_form_section_heading('Enquiry Details');
	print_static_form_element('Product Title', $enquiry_data['enquiry_product_title']);
	print_textarea_element('Product Description', array('name' => 'enquiry_product_description', 'cols' => 80, 'rows' => 6), false,
		array('disabled' => 'disabled', 'value' => $enquiry_data['enquiry_product_description']));

	print_form_section_heading('Estimate Details');

	print_static_form_element('Estimate Date', unix_to_human(time()));
	print_static_form_element('Version', Eloquent\Estimate::getNextVersionFromEnquiryId($enquiry_id));

	print_input_element('Version Name', array('name' => 'name', 'size' => 60, 'maxlength' => 60), true, array('value' => $enquiry_data['enquiry_product_title']));
	print_textarea_element('Version Description', array('name' => 'description', 'cols' => 60, 'rows' => 8), false, array('value' => $enquiry_data['enquiry_product_description']));
	print_input_element('Price Breaks (seperated by commas)',
		array('name' => 'price_breaks', 'size' => 60, 'maxlength' => 255), true);


	print_submit_container_open();
	    echo form_submit('submit', 'Submit', 'id="submit_button"');
	    //echo form_button(array('name' => 'button', 'content' => 'Back to Material Types list', 'onclick' => "window.location='/estimates/material_type/browse';"));
	print_submit_container_close();

}

print_form_container_close();
echo form_close();
