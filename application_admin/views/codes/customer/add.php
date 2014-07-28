<?php
echo $section_title;
echo '<div id="page">
<style type="text/css">
.formlabel {
    width: 150px;
}
</style>';
echo form_open('codes/customer/process_add/', array('id' => 'customer_add_form'));
echo '<div>';
print_form_container_open();
print_checkbox_element('User already exists in system?', array('name' => 'user_exists', 'value' => 1));
print_checkbox_element('Company already exists in system?', array('name' => 'company_exists', 'value' => 1));
print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button"');
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';
