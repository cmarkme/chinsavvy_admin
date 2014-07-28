<?php
echo $section_title;
function get_email_row($recipienttype = 'to', $allstaff_options, $allenquirers_options, $defaults, $admin_emails, $enquirer_emails) {
    $ci = get_instance();
    $countries = $ci->country_model->get_dropdown();
    $dd = form_dropdown($recipienttype.'_country', $countries, null, 'onchange="update_enquirers(\''.$recipienttype.'\', this);"');

    return '
            <tr>
                <th rowspan="2">'.ucfirst($recipienttype).'</th>
                <td rowspan="2"><textarea id="'.$recipienttype.'-area" name="'.$recipienttype.'" cols="60" rows="5">'. @$defaults[$recipienttype]. '</textarea></td>
                <th>
                    Admins
                </th>
                <td>
                    <select id="'.$recipienttype.'_admin" onchange="appendAddress($(\'#'.$recipienttype.'-area\')[0], this.value);">
                        '. $allstaff_options. '
                    </select>
                </td>
                <td>
                    <input id="addalladmin_'.$recipienttype.'" type="button" onclick="addAllAdmins(\''.$recipienttype.'-area\');" value="Add all ('.count($admin_emails).')" />
                </td>
            </tr>
            <tr>
                <th>
                    Enquirers
                </th>
                <td>
                    <select id="'.$recipienttype.'_enquirers" onchange="appendAddress($(\'#'.$recipienttype.'-area\')[0], this.value);">
                        '. $allenquirers_options. '
                    </select>
                    <br />
                    '.$dd.'
                </td>
                <td>
                    <input id="addallenquirers_'.$recipienttype.'" type="button" onclick="addAllEnquirers(\''.$recipienttype.'\', \''.$recipienttype.'-area\');"
                            value="Add all ('.count($enquirer_emails).')" />
                    <input id="enquirerscsv_'.$recipienttype.'" type="button" onclick="download_csv(\''.$recipienttype.'\');" value="Download CSV" />
                </td>
            </tr>';
}
?>
<form enctype="multipart/form-data" id="emailform" method="post" action="email/send" onsubmit="return validateForm(this);">
    <div id="entry_form" style="display: block;">
        <input type="hidden" name="formdata" value="1" />
        <input type="hidden" name="user_id" value="<?=$user_id?>" />
        <input type="hidden" name="from" value="<?=$staff->email?>" />
        <input type="hidden" name="fromname" value="<?=$staff->name?>" />
        <table class="tbl">
            <?php echo get_email_row('to', $allstaff_options, $allenquirers_options, $defaults, $admin_emails, $enquirer_emails); ?>
            <?php echo get_email_row('cc', $allstaff_options, $allenquirers_options, $defaults, $admin_emails, $enquirer_emails); ?>
            <?php echo get_email_row('bcc', $allstaff_options, $allenquirers_options, $defaults, $admin_emails, $enquirer_emails); ?>
            <tr>
                <th>Subject</th>
                <td colSpan="5">
                    <input type="text" name="subject" size="50" value="<?php echo @$defaults['subject']; ?>" />
                    <?=form_dropdown('enquiry_id', $enquiry_options, $enquiry_id)?>
                </td>
            </tr>
            <tr>
                <th>Attachments <input type="hidden" value="50000000" name="MAX_FILE_SIZE"/></th>
                <td colSpan="5" id="attachment-td">
                    <span id="addattachment-span" onclick="addAttachmentInput();return false;">Attach a file</span>
                </td>
            </tr>
            <tr>
                <th>Message</th>
                <td colSpan="5"><textarea id="body" name="body" cols="70" rows="20"><?php echo @$defaults['message']; ?></textarea></td>
            </tr>
            <tr>
                <td colSpan="6"><input type="submit" value="Send Email" /></td>
            </tr>
        </table>
    </div>
</form>
<?php $this->ckeditor->replace('body') ?>
<script type="text/javascript">
//<![CDATA[
var enquirers = [<?=$enquirer_emails_js?>];
var enquirers_to = enquirers;
var enquirers_cc = enquirers;
var enquirers_bcc = enquirers;
var admins = [<?php foreach($admin_emails as $user_id => $email) echo "{email: '$email', user_id: $user_id},"; ?>];
var addattachment_span = null;
var addanother = $('<span id="addattachment-span" onclick="addAttachmentInput()" class="addanotherattachment"></span>');

//]]>
</script>
<script type="text/javascript" src="/includes/js/application/site/email.js"> /*<![CDATA[ */ //]]></script>
