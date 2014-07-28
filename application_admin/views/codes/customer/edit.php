<?php
echo $section_title;
echo '<div id="page">
<style type="text/css">
.formlabel {
    width: 250px;
}
</style>';
echo validation_errors();
echo form_open('codes/customer/process_edit/', array('id' => 'customer_edit_form'));
echo '<div>';
echo form_hidden('company_id', $customer_id);
echo form_hidden('address_address_id');
echo form_hidden('existing_comp_id');
echo form_hidden('corporate_contact_user_email', @form_element::$default_data['corporate_contact_user_email']);
echo form_hidden('technical_contact_user_email', @form_element::$default_data['technical_contact_user_email']);
if (!empty($corporate_contact_details)) {
    echo form_hidden('corporate_contact_id', $corporate_contact_details['id']);
}
if (!empty($technical_contact_details)) {
    echo form_hidden('technical_contact_id', $technical_contact_details['id']);
}
print_form_container_open();

$disabled_param = (empty(form_element::$default_data['corporate_contact_user_email'])) ? array('disabled' => 'disabled', 'class' => 'disabled') : array();

print_static_form_element('Corporate contact Email', '<span id="corporate_email">'.@form_element::$default_data['corporate_contact_user_email'].'</span>');
print_input_element('Corporate contact First Name', array('name' => 'corporate_contact_user_first_name', 'size' => 50) + $disabled_param, true);
print_input_element('Corporate contact Last Name', array('name' => 'corporate_contact_user_surname', 'size' => 50) + $disabled_param, true);
print_dropdown_element('corporate_contact_user_salutation', 'Corporate contact Title', $dropdowns['titles'], false, $disabled_param);
print_input_element('Corporate contact Phone', array('name' => 'corporate_contact_user_phone', 'size' => 18) + $disabled_param);
print_input_element('Corporate contact Mobile', array('name' => 'corporate_contact_user_mobile', 'size' => 18) + $disabled_param);
print_input_element('Corporate contact FTP username', array('name' => 'corporate_contact_user_ftpuserid', 'size' => 32) + $disabled_param);
print_input_element('Corporate contact FTP password', array('name' => 'corporate_contact_user_password', 'size' => 16) + $disabled_param);

$disabled_param = (empty(form_element::$default_data['technical_contact_user_email'])) ? array('disabled' => 'disabled', 'class' => 'disabled') : array();

print_static_form_element('Technical contact Email', '<span id="technical_email">'.@form_element::$default_data['technical_contact_user_email'].'</span>');
print_input_element('Technical contact First Name', array('name' => 'technical_contact_user_first_name', 'size' => 50) + $disabled_param);
print_input_element('Technical contact Last Name', array('name' => 'technical_contact_user_surname', 'size' => 50) + $disabled_param);
print_dropdown_element('technical_contact_user_salutation', 'Technical contact Title', $dropdowns['titles'], false, $disabled_param);
print_input_element('Technical contact Phone', array('name' => 'technical_contact_user_phone', 'size' => 18) + $disabled_param);
print_input_element('Technical contact Mobile', array('name' => 'technical_contact_user_mobile', 'size' => 18) + $disabled_param);
print_input_element('Technical contact FTP username', array('name' => 'technical_contact_user_ftpuserid', 'size' => 32) + $disabled_param);
print_input_element('Technical contact FTP password', array('name' => 'technical_contact_user_password', 'size' => 16) + $disabled_param);

print_input_element('Company Code', array('name' => 'company_code', 'size' => 2), true);
print_input_element('Company Name', array('name' => 'company_name', 'size' => 60), true);
if (!empty($customer_id)) {
    print_static_form_element('Date updated', $updated_date);
}

// Billing address
print_input_element('Address line 1', array('name' => 'billing_address_address1', 'size' => 50));
print_input_element('Address line 2', array('name' => 'billing_address_address2', 'size' => 50));
print_input_element('City', array('name' => 'billing_address_city', 'size' => 40), true);
print_input_element('Province', array('name' => 'billing_address_province', 'size' => 30));
print_input_element('State', array('name' => 'billing_address_state', 'size' => 30));
print_input_element('Zip/Post code', array('name' => 'billing_address_postcode', 'size' => 10));
print_dropdown_element('billing_address_country_id', 'Country', $dropdowns['countries'], true);
print_input_element('Phone', array('name' => 'billing_company_phone', 'size' => 15));
print_input_element('Fax', array('name' => 'billing_company_fax', 'size' => 15));

// Shipping address
print_input_element('Shipping Address line 1', array('name' => 'shipping_address_address1', 'size' => 50));
print_input_element('Shipping Address line 2', array('name' => 'shipping_address_address2', 'size' => 50));
print_input_element('Shipping City', array('name' => 'shipping_address_city', 'size' => 40));
print_input_element('Shipping Province', array('name' => 'shipping_address_province', 'size' => 30));
print_input_element('Shipping State', array('name' => 'shipping_address_state', 'size' => 30));
print_input_element('Shipping Zip/Post code', array('name' => 'shipping_address_postcode', 'size' => 10));
print_dropdown_element('shipping_address_country_id', 'Shipping Country', $dropdowns['countries']);

print_textarea_element('Notes', array('name' => 'company_notes', 'cols' => 40, 'rows' => 5));

print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button"');
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';
