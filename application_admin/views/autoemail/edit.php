<?php
echo $section_title;
echo '<div id="page">';
echo validation_errors();
echo form_open('autoemails/process_edit/', array('id' => 'autoemail_edit_form'));
echo '<div>';
print_form_container_open();
echo form_hidden('id', $autoemail_id);

print_static_form_element('Name', $autoemail->name);
print_static_form_element('Description', $autoemail->description);
print_static_form_element('Emails in queue', $emails_in_queue);
print_input_element('Subject', array('name' => 'subject', 'size' => '150'), true);
// print_input_element('Additional Recipients', array('name' => 'recipients', 'size' => 150));
print_textarea_element('Message', array('name' => 'message', 'id' => 'email_message', 'cols' => 50, 'rows' => 5), true);
print_static_form_element('Dynamic fields', '[enquiry_id], [product_title], [enquiry_date], [enquirer], [first_name], [surname]');
print_checkbox_element('Active', 'status');

print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button"');
// TODO Cancel button
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';
$this->ckeditor->replace('email_message');
