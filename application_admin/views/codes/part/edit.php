<?php
echo $section_title;
echo '<div id="page">';
echo validation_errors();
echo form_open('codes/part/process_edit/', array('id' => 'part_edit_form'));
echo '<div>';
print_form_container_open();
if (empty($part_id)) {
    print_dropdown_element('project_id', 'Project ID', $dropdowns['projects'], false, 'onchange="window.location=\'/codes/part/edit/0/\'+this.value;"');
} else {
    echo form_hidden('id', $part_id);
    print_static_form_element('Project ID', $dropdowns['projects'][$project_id]);
}
print_static_form_element('Product number', $product_number);
print_input_element('Name', array('name' => 'name', 'size' => '50'));
print_input_element('Name (CH)', array('name' => 'name_ch', 'size' => '50'));
print_input_element('2D Data', array('name' => '_2d_data', 'size' => '50'));
print_input_element('Latest Rev 2D Data', array('name' => '_2d_data_rev', 'size' => '3'));
print_input_element('3D Data', array('name' => '_3d_data', 'size' => '50'));
print_input_element('Latest Rev 3D Data', array('name' => '_3d_data_rev', 'size' => '3'));
print_input_element('Other Data', array('name' => 'other_data', 'size' => '50'));
print_date_element('Date Receipt other Data', array('name' => 'other_data_date'));
print_input_element('Customer PO Number', array('name' => 'customer_po_number'));
print_date_element('Customer PO Date', array('name' => 'customer_po_date'));
print_textarea_element('Description', array('name' => 'description', 'cols' => 50, 'rows' => 5));
print_date_element('Due Completion Date', array('name' => 'due_date'));
print_dropdown_element('status_text_selector', 'Status Preset Selector', $dropdowns['status_codes']);
print_input_element('Status', array('name' => 'status_text'));
print_textarea_element('Status Description', array('name' => 'status_description', 'cols' => 50, 'rows' => 5));
print_checkbox_element('Completed', 'completed');

if (empty($part_id)) {
    $button_label = 'Add new product';
} else {
    print_static_form_element('Status update date', $status_update_date);
    print_static_form_element('Date updated', $revision_date);
    print_static_form_element('Updated by', $revision_user);
    $button_label = 'Update product';
}

print_submit_container_open();
echo form_submit('button', $button_label, 'id="submit_button"');
// TODO Cancel button
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';
