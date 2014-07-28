<?php
echo $section_title;
echo '<div id="page">';
echo validation_errors();
echo form_open('verification/process_edit/', array('id' => 'verification_edit_form'));
echo '<div>';
print_form_container_open();
print_input_element('Google verification code', array('name' => 'verification', 'size' => '50'), true);
print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button"');
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';
