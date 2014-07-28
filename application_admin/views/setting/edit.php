<?php
echo $section_title;
echo '<div id="page">';
echo validation_errors();
echo form_open('setting/process_edit/', array('id' => 'setting_edit_form'));
echo '<div>';
echo form_hidden('setting_id', $setting_id);
print_form_container_open();
print_input_element('Name', array('name' => 'name', 'size' => '50'), true);
print_input_element('Value', array('name' => 'value', 'size' => '50'), true);
print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button"');
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';
