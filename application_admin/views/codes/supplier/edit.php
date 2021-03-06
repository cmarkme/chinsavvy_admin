<?php
echo $section_title;
echo '<div id="page">';
echo validation_errors();
echo form_open('codes/supplier/process_edit/', array('id' => 'supplier_edit_form'));
echo '<div>';
echo form_hidden('supplier_id', $supplier_id);
print_form_container_open();
print_input_element('Supplier Code', array('name' => 'company_code', 'size' => '3'));
print_input_element('Name', array('name' => 'company_name', 'size' => '50'), true);
print_input_element('URL', array('name' => 'company_url', 'size' => '50'));
print_input_element('Phone', array('name' => 'company_phone', 'size' => '16'));
print_input_element('Fax', array('name' => 'company_fax', 'size' => '16'));
print_input_element('Email', array('name' => 'company_email', 'size' => '50'));
print_dropdown_element('address_billing_country_id', 'Country', $dropdowns['countries'], true);
print_input_element('Address line 1', array('name' => 'address_billing_address1', 'size' => '50'));
print_input_element('Address line 2', array('name' => 'address_billing_address2', 'size' => '50'));
print_input_element('City', array('name' => 'address_billing_city', 'size' => '50'), true);
print_input_element('Province', array('name' => 'address_billing_province', 'size' => '50'));
print_input_element('State', array('name' => 'address_billing_state', 'size' => '50'));
print_input_element('Zip/Post code', array('name' => 'address_billing_postcode', 'size' => '8'));
print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button"');
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';
