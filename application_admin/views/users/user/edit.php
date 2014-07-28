<?php echo $top_title ?>
<div id="page">
    <?=form_open('users/user/process_edit', array('id' => 'userform'))?>
    <div>
    <?php print_hidden_element('user_id')?>
    <?php print_hidden_element('action')?>
    <?php echo $details_title ?>
        <div id="details">
            <div id="detailsmessage"></div>
            <?php print_form_container_open();
            if (!empty($user_id)) print_static_form_element('ID', $user_id);
            if (!empty($company_id)) print_static_form_element('Company', anchor("company/edit/$company_id", $company_name, 'title="Edit this company"'));
            print_dropdown_element('salutation', 'Title', $salutations, true);
            print_input_element('First Name', array('name' => 'first_name'), true);
            print_input_element('First Name (Chinese)', array('name' => 'first_name_chinese'));
            print_input_element('Last Name', array('name' => 'surname'), true);
            print_input_element('Last Name (Chinese)', array('name' => 'surname_chinese'));
            print_checkbox_element('Disabled', array('name' => 'disabled', 'value' => 1));
            print_input_element('Username', array('name' => 'username'));

            // Only show password if admin has donanything for users
            if (has_capability('users:doanything')) {
                print_password_element('Password', array('name' => 'password'), false, true);
            }

            print_textarea_element('Signature', array('name' => 'signature', 'cols' => 80, 'rows' => 10));
            print_submit_container_open();
            echo form_submit('submit', 'Submit', 'id="submit_button"');
            echo form_button(array('name' => 'button', 'content' => 'Back to Users list', 'onclick' => "window.location='/users/user';"));
            print_submit_container_close();

            print_form_container_close();
        echo '</div>';
    echo form_close();
    echo '</div>';

    echo $contacts_title; ?>
        <div id="contacts">
        <div id="contactsmessage"></div>
            <table class="tbl">
                <tr><th>Emails</th><td id="email"></td></tr>
                <tr><th>Work Phones</th><td id="phone"></td></tr>
                <tr><th>Mobiles</th><td id="mobile"></td></tr>
                <tr><th>Faxes</th><td id="fax"></td></tr>
            </table>
        </div>

<?php $this->ckeditor->replace('signature') ?>
<script type="text/javascript"> /*<![CDATA[ */
var user_id = <?php echo (!empty($user_id)) ? $user_id: '0'; ?>;
//]]></script>
</div>
