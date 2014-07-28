<?php
echo $section_title;
echo '<div id="page">';
echo validation_errors();
echo form_open('codes/project/process_edit/', array('id' => 'project_edit_form'));
echo '<div>';
print_form_container_open();
if (empty($project_id)) {
    print_input_element('Project number', array('name' => 'number'), true);
} else {
    echo form_hidden('id', $project_id);
    print_static_form_element('Project number', $project_number);
}
print_dropdown_element('company_id', 'Company Name', $dropdowns['companies'], true);
print_dropdown_element('division_id', 'Division', $dropdowns['divisions'], true);
print_date_element('Project start date', array('name' => 'creation_date'));
print_input_element('Name', array('name' => 'name', 'size' => '50'), true);
print_input_element('Customer Project Number', array('name' => 'customer_project_number'));
print_input_element('Customer PO Number', array('name' => 'customer_po_number'));
print_date_element('Customer PO Date', array('name' => 'customer_po_date'));
print_textarea_element('Description', array('name' => 'description', 'cols' => 50, 'rows' => 5), true);
print_date_element('Due Completion Date', array('name' => 'due_date'));
print_dropdown_element('status_text_selector', 'Status Preset Selector', $dropdowns['status_codes']);
print_input_element('Status', array('name' => 'status_text'));
print_textarea_element('Status Description', array('name' => 'status_description', 'cols' => 50, 'rows' => 5));
print_checkbox_element('Completed', 'completed');

if (!empty($project_id)) {
    print_static_form_element('Status update date', $status_update_date);
    print_static_form_element('Date updated', $revision_date);
    print_static_form_element('Updated by', $revision_user);
    $button_label = 'Update project';
} else {
    $button_label = 'Add new project';
}

print_submit_container_open();
echo form_submit('button', $button_label, 'id="submit_button"');
// TODO Cancel button
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';
