<?php
echo $section_title;
echo validation_errors();
echo form_open('exchange/dailyvalues/process_add/', array('id' => 'exchange_dailyvalues_form'));
print_form_container_open();
print_dropdown_element('market_id', 'Market', $markets, true, 'onchange="window.location=\'/exchange/dailyvalues/add/\'+this.value;"');
print_input_element('Date', array('name' => 'timestamp', 'class' => 'date_input'));
foreach ($commodities as $commodity) {
    print_input_element($commodity->name, array('name' => "commodity_$commodity->id"));
}
print_checkbox_element('Override existing values', array('name' => 'override', 'value' => 1));
print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button"');
print_submit_container_close();
print_form_container_close();
echo form_close();
echo '</div>';
?>

