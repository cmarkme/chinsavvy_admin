<?php
echo $section_title;
echo '<div id="page">';
echo validation_errors();
echo form_open('users/role/process_edit/', array('id' => 'role_edit_form'));
echo form_hidden('role_id', $role_id);
print_form_container_open();
print_input_element('Name', array('name' => 'name', 'size' => '50'), true);
print_textarea_element('Description', array('name' => 'description', 'cols' => 80, 'rows' => 6), true);
print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button"');
print_submit_container_close();
print_form_container_close();
echo form_close();
echo '</div>';
