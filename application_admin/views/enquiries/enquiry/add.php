<?php
echo $section_title;
echo '<div id="page">';
echo validation_errors();
echo form_open_multipart('/enquiries/enquiry/process_add/', array('id' => 'enquiry_add_form'));
print_form_container_open();
echo form_hidden('company_id');

print_form_section_heading('Enquiry Details');
print_dropdown_element('company_name_prefill', 'Company', $dropdowns['enquirers'], false, 'onchange="window.location=\'/enquiries/enquiry/add/\'+$(this).val();"');
print_static_form_element('Enquiry Number', $next_id);
print_static_form_element('Enquiry Date', unix_to_human(time()));

if (has_capability('enquiries:assignstafftoenquiries')) {
    print_dropdown_element('enquiry_priority', 'Priority', $dropdowns['priorities'], true);
    print_textarea_element('Notes for staff', array('name' => 'enquiry_notes', 'cols' => 50, 'rows' => 5));
}

print_form_section_heading('Company Details');
print_input_element('Company Name', array('name' => 'company_name', 'size' => 40), true);
print_dropdown_element('company_company_type', 'Company Type', $dropdowns['company_types'], true);
print_input_element('Website URL', array('name' => 'company_url', 'size' => 30));
print_input_element('Address 1', array('name' => 'address_address1', 'size' => 30), true);
print_input_element('Address 2', array('name' => 'address_address2', 'size' => 30));
print_input_element('City', array('name' => 'address_city', 'size' => 26), true);
print_input_element('State/Province/County', array('name' => 'address_state', 'size' => 40), true);
print_input_element('Postcode', array('name' => 'address_postcode', 'size' => 12), true);
print_dropdown_element('address_country_id', 'Country', $dropdowns['countries'], true);

print_form_section_heading('Enquirer Details');
print_dropdown_element('user_salutation', 'Title', $dropdowns['salutations'], true);
print_input_element('First Name', array('name' => 'user_first_name', 'size' => 20), true);
print_input_element('Last Name', array('name' => 'user_surname', 'size' => 20), true);
print_input_element('Telephone', array('name' => 'user_phone', 'size' => 20), true);
print_input_element('Mobile Phone', array('name' => 'user_mobile', 'size' => 20));
print_input_element('Fax', array('name' => 'user_fax', 'size' => 20));
print_input_element('Email Address', array('name' => 'user_email', 'size' => 35), true);

print_form_section_heading('Product Details');
print_input_element('Product Title', array('name' => 'enquiry_product_title', 'size' => 80, 'maxlength' => 80), true);
print_textarea_element('Product Description', array('name' => 'enquiry_product_description', 'cols' => 80, 'rows' => 6), true);
print_textarea_element('Materials', array('name' => 'enquiry_product_materials', 'cols' => 30, 'rows' => 2));
print_textarea_element('Manufacturing Process', array('name' => 'enquiry_product_man_process', 'cols' => 30, 'rows' => 2));
print_input_element('Size', array('name' => 'enquiry_product_size', 'size' => 60, 'maxlength' => 60));
print_input_element('Weight', array('name' => 'enquiry_product_weight', 'size' => 60, 'maxlength' => 60));
print_input_element('Colour', array('name' => 'enquiry_product_colour', 'size' => 60, 'maxlength' => 60));
print_textarea_element('Packaging', array('name' => 'enquiry_product_packaging', 'cols' => 30, 'rows' => 4));

print_form_section_heading('Trading Details');
print_input_element('Minimum Annual Qty', array('name' => 'enquiry_min_annual_qty', 'size' => 15, 'maxlength' => 40), true);
print_input_element('Maximum Annual Qty', array('name' => 'enquiry_max_annual_qty', 'size' => 15, 'maxlength' => 40));
print_input_element('Minimum 1st Order Qty', array('name' => 'enquiry_min_order_qty', 'size' => 15, 'maxlength' => 40));
print_dropdown_element('enquiry_shipping', 'Shipping Method', $dropdowns['shipping_methods'], true);
print_dropdown_element('enquiry_delivery_terms', 'Delivery Terms', $dropdowns['delivery_terms']);
print_dropdown_element('enquiry_country_id', 'Delivery Country', $dropdowns['countries'], true);
print_input_element('Delivery Port', array('name' => 'enquiry_delivery_port', 'size' => 20, 'maxlength' => 40));
print_dropdown_element('enquiry_currency', 'Currency', $dropdowns['currencies'], true);
print_textarea_element('Useful Websites', array('name' => 'enquiry_websites', 'cols' => 30, 'rows' => 4));
// print_form_element('Source', form_dropdown('enquiry_source', $sources, set_value('enquiry_source')), true);

print_form_section_heading('Files');
print_file_element('enquiry_file_1', 'File 1');
print_file_element('enquiry_file_2', 'File 2');
print_file_element('enquiry_file_3', 'File 3');
print_file_element('enquiry_file_4', 'File 4');
print_file_element('enquiry_file_5', 'File 5');

print_submit_container_open();
echo form_submit('button', 'Submit Enquiry', 'id="submit_button"');
print_submit_container_close();

print_form_container_close();
echo form_close();
?>
</div>
