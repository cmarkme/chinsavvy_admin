<?php
echo $section_title;
echo '<div id="page">';
echo validation_errors();
echo form_open('company/process_edit/', array('id' => 'company_edit_form'));
echo '<div>';
echo form_hidden('company_id', $company_id);
print_hidden_element('address_billing_id');
print_hidden_element('address_shipping_id');
print_form_container_open();
print_form_section_heading('Company Details');
print_input_element('Name', array('name' => 'company_name', 'size' => '50'), true);
print_input_element('Name (Chinese)', array('name' => 'company_name_ch', 'size' => '50'));
print_input_element('Code', array('name' => 'company_code', 'size' => '3'));
print_dropdown_element('company_role', 'Role', $dropdowns['roles'], true);
print_dropdown_element('company_type', 'Type', $dropdowns['company_types'], true);
print_input_element('URL', array('name' => 'company_url', 'size' => '50'));
print_input_element('Phone', array('name' => 'company_phone', 'size' => '16'));
print_input_element('Fax', array('name' => 'company_fax', 'size' => '16'));
print_input_element('Email', array('name' => 'company_email', 'size' => '50'));
print_input_element('Secondary Email', array('name' => 'company_email2', 'size' => '50'));
print_textarea_element('Notes', array('name' => 'company_notes', 'cols' => '40', 'rows' => 5));

print_form_section_heading('Billing Address');
print_input_element('Address 1', array('name' => 'address_billing_address1', 'size' => 30), true);
print_input_element('Address 2', array('name' => 'address_billing_address2', 'size' => 30));
print_input_element('City', array('name' => 'address_billing_city', 'size' => 26), true);
print_input_element('State/Province/County', array('name' => 'address_billing_state', 'size' => 40), true);
print_input_element('Postcode', array('name' => 'address_billing_postcode', 'size' => 12), true);
print_dropdown_element('address_billing_country_id', 'Country', $dropdowns['countries'], true);

print_form_section_heading('Shipping Address');
print_input_element('Address 1', array('name' => 'address_shipping_address1', 'size' => 30));
print_input_element('Address 2', array('name' => 'address_shipping_address2', 'size' => 30));
print_input_element('City', array('name' => 'address_shipping_city', 'size' => 26));
print_input_element('State/Province/County', array('name' => 'address_shipping_state', 'size' => 40));
print_input_element('Postcode', array('name' => 'address_shipping_postcode', 'size' => 12));
print_dropdown_element('address_shipping_country_id', 'Country', $dropdowns['countries']);

print_form_section_heading('Users');
print_static_form_element('Currently assigned', ul($assigned_users));
print_dropdown_element('new_user', 'Add a user', $unassigned_users, false, 'onchange="window.location=\'/company/add_user/'.$company_id.'/\'+this.value;"');

print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button"');
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';
