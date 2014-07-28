<?php
echo $section_title;
echo '<div id="page">';
echo validation_errors();
echo form_open('codes/division/process_edit/', array('id' => 'division_edit_form'));
echo '<div>';
echo form_hidden('division_id', $division_id);
print_form_container_open();
print_input_element('Name', array('name' => 'name', 'size' => '50'), true);
print_input_element('Code', array('name' => 'code', 'size' => '50'), true);
if (!empty($revision_user)) {
    print_static_form_element('Date updated', $revision_date);
    print_static_form_element('Updated by', $revision_user);
}
print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button"');
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';
