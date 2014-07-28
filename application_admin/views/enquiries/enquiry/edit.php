<?php
echo $section_title;
echo '<div id="page">';
// Prepping HTML for conditional statements
$notes_textarea = form_textarea(array('cols' => 40, 'rows' => 3, 'id' => 'append_note', 'name' => 'append_note')) . '<br />';
$notes_textarea .= form_button('appendnote', 'Append note', 'onclick="append_enquiry_note('.$enquiry_data['enquiry_id'].', '.$user_id.');"');

// @TODO Code the Enquiry Note Edit interface
// $edit_notes_button = form_button('editnotes', 'Edit notes', 'onclick="window.location=\'/enquiries/enquiry/edit_notes/'.$enquiry_data['enquiry_id'].'\';"');
echo validation_errors();
echo form_open_multipart('/enquiries/enquiry/process_edit/', array('id' => 'enquiry_edit_form'));
echo form_hidden('enquiry_id', $enquiry_data['enquiry_id']);
print_form_container_open();

print_form_section_heading('Enquiry Details');
print_static_form_element('Enquiry Number', $enquiry_data['enquiry_id']);
print_static_form_element('Enquiry Date', $enquiry_data['enquiry_creation_date']);

if (has_capability('enquiries:assignstafftoenquiries')) { // Admin user
    echo form_hidden('enquiry_user_id', $enquiry_data['enquiry_user_id']);
    echo form_hidden('enquiry_product_id', $enquiry_data['enquiry_product_id']);
    print_dropdown_element('enquiry_priority', 'Priority', $dropdowns['priorities']);
    print_static_form_element('Staff notes', '<span id="enquiry_notes">' . $enquiry_notes . '</span>' . $notes_textarea . '<br />' . @$edit_notes_button);
    print_dropdown_element('enquiry_status', 'Enquiry Status', $dropdowns['statuses']);
    print_multiselect_element('enquiry_staff[]', 'Assigned Staff', $staff_list, false, 'size="5"');
    print_input_element('Due Date', array('name' => 'enquiry_due_date', 'class' => 'date_input'));

    print_form_section_heading('Company Details');
    print_static_form_element('Company Name', $enquiry_data['company_name']);
    print_static_form_element('Company Type', get_lang_for_constant_value('COMPANY_TYPE', @$enquiry_data['company_company_type']));
    print_static_form_element('Website URL', $enquiry_data['company_url']);
    print_static_form_element('Address 1', $enquiry_data['address_address1']);
    print_static_form_element('Address 2', $enquiry_data['address_address2']);
    print_static_form_element('City', $enquiry_data['address_city']);
    print_static_form_element('State/Province/County', $enquiry_data['address_state']);
    print_static_form_element('Postcode', $enquiry_data['address_postcode']);
    print_static_form_element('Country', $enquiry_data['address_country_name']);

    print_form_section_heading('Enquirer Details');
    print_static_form_element('Title', $enquiry_data['user_salutation']);
    print_static_form_element('First Name', $enquiry_data['user_first_name']);
    print_static_form_element('Last Name', $enquiry_data['user_surname']);
    print_static_form_element('Telephone', $enquiry_data['user_phone']);
    print_static_form_element('Mobile Phone', $enquiry_data['user_mobile']);
    print_static_form_element('Fax', $enquiry_data['user_fax']);
    print_static_form_element('Email Address', anchor('email/index/'.$enquiry_data['user_id'].'/'.$enquiry_data['enquiry_id'], $enquiry_data['user_email']));

} else if (has_capability('enquiries:assignabletoenquiries')) { // Staff
    print_static_form_element('Priority', get_lang_for_constant_value('ENQUIRIES_ENQUIRY_PRIORITY', $enquiry_data['enquiry_priority']));
    $notes_content = '<span id="enquiry_notes">' . $enquiry_notes . '</span>' . $notes_textarea;
    if (has_capability('enquiries:editenquiries')) {
        $notes_content .= $notes_button;
    }
    print_static_form_element('Staff Notes', $notes_content);
    print_static_form_element('Enquiry Status', get_lang_for_constant_value('ENQUIRIES_ENQUIRY_STATUS', $enquiry_data['enquiry_status']));
    print_static_form_element('Due Date', $enquiry_data['enquiry_due_date']);
} else { // Customer
    print_static_form_element('Enquiry Status', get_lang_for_constant_value('ENQUIRIES_ENQUIRY_STATUS', $enquiry_data['enquiry_status']));
    print_static_form_element('Due Date', $enquiry_data['enquiry_due_date']);
}

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
print_dropdown_element('enquiry_source', 'Source', $dropdowns['sources'], true);

print_form_section_heading('Files');
$i = 1;
foreach ($files as $file) {
    $viewfile_link = anchor('/enquiries/enquiry/download_file/'.$file->id, $file->filename_original);
    $delete_icon = anchor('/enquiries/enquiry/delete_file/'.$file->id, img(array('src' => 'images/admin/icons/delete_16.gif', 'class' => 'icon nofloat', 'title' => 'Delete this file')));
    if (!has_capability('enquiries:viewfiles')) {
        $viewfile_link = $file->filename_original;
    }
    if (!has_capability('enquiries:deletefiles')) {
        $delete_icon = '';
    }
    print_static_form_element('File #'.$i++, $viewfile_link . ' ' . $delete_icon);
}
print_file_element('enquiry_file', 'Add a file');
print_submit_container_open();
if ($previous_id) {
    echo form_submit('previous', "Submit and show previous ($previous_id)");
}
echo form_submit('button', 'Submit', 'id="submit_button"');
if ($next_id) {
    echo form_submit('next', "Submit and show next ($next_id)");
}
print_submit_container_close();
print_form_container_close();
echo form_close();
echo '</div>';
?>

