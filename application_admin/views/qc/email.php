<?=$main_title?>
<form id="email_settings" method="post" action="qc/email/index/">
    <div>
        <input id="form_data" type="hidden" name="form_data" value="0" />
        <input type="hidden" name="from" value="<?=$from?>" />
        <input type="hidden" name="from_name" value="<?=$from_name?>" />
        <input type="hidden" name="previous_report_type" value="<?=$report_type?>" />

    <?php if ($disabled_en) : ?>
        <input type="hidden" name="lang_en" value="1" />
    <?php endif; ?>
    <?php if ($disabled_ch) : ?>
        <input type="hidden" name="lang_ch" value="1" />
    <?php endif; ?>

    </div>
    <table class="tbl">
        <tr>
            <th style="width: 140px">Report type <span class="required">*</span></th>
            <td><?=form_dropdown('report_type', $report_types, $report_type, 'id="report_type"').get_error_message($errors, 'report_type')?></td>
        </tr>
        <tr>
            <th>Project <span class="required">*</span></th>
            <td><?=form_dropdown('project_id', $projects_array, $project_id, 'id="project_id"').get_error_message($errors, 'project_id')?></td>
        </tr>

    <?php if ($report_type != QC_EMAIL_REPORT_TYPE_QC_RESULTS) : ?>
        <tr>
            <th>Revision <span class="required">*</span></th>
            <td><?=form_dropdown('revision_id', $revisions_array, $revision_id, 'id="revision_id"').get_error_message($errors, 'revision_id')?></td>
        </tr>
    <?php endif; ?>

    <tr>
        <th>Languages <span class="required">*</span></th>
        <td>
            <select name="lang" id="lang">
                <option value="<?=QC_SPEC_LANGUAGE_EN?>" <?=$en_selected?>>English</option>
                <option value="<?=QC_SPEC_LANGUAGE_CH?>" <?=$ch_selected?>>Chinese</option>
                <option value="<?=QC_SPEC_LANGUAGE_COMBINED?>" <?=$all_selected?>>Combined</option>
            </select>
            <?=get_error_message($errors, 'languages')?>
        </td>
    </tr>

    <?php if ($report_type == QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER) : ?>
        <tr>
            <th>Process</th>
            <td><?=form_dropdown('category_id', $categories_array, $category_id, 'id="category_id"')?></td>
        </tr>
        <tr>
            <th>Supplier recipient <span class="required">*</span></th>

        <?php if (empty($supplier_recipients)) : ?>
            <td>No suppliers assigned to this process</td>
            <?php $ready_for_sending = false; ?>
        <?php else : ?>
            <td><?=form_dropdown('supplier_id', $supplier_recipients, $supplier_id, 'id="supplier_email"').get_error_message($errors, 'supplier_emails')?></td>
        <?php endif; ?>

        </tr>
    <?php endif; ?>

    <?php if ($report_type != QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER && !empty($project_id) && !empty($report_type)) :
        if (empty($customer_recipients)) : ?>
            <td>No customer emails for this project!</td>
            <?php $ready_for_sending = false; ?>
        <?php else : ?>
                <tr>
                    <th>Customer recipients <span class="required">*</span></th>
                    <td><?=form_multiselect('customer_emails[]', $customer_recipients, $customer_emails, 'id="customer_emails"').get_error_message($errors, 'customer_emails')?></td>
                </tr>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($ready_for_sending)) : ?>
            <tr>
                <th>Subject <span class="required">*</span></th>
                <td><input style="width: 500px" type="text" name="subject" value="<?=$subject?>" /><?=get_error_message($errors, 'subject')?></td>
            </tr>
            <tr>
                <th>Message <span class="required">*</span></th>
                <td><textarea cols="80" rows="15" id="email_body" name="email_body"><?=$email_body?></textarea>
                    <button type="button" id="reset_body">Reset</button><?=get_error_message($errors, 'message')?></td>
            </tr>
            <tr>
                <th>Dynamic fields</th>
                <td>
                    <ul id="dynamic_fields">
                        <?php foreach ($dynamic_fields as $field => $label) : ?>
                            <li>[<?=$field?>] : <?=$label?></li>
                        <?php endforeach; ?>
                    </ul>
                </td>
            </tr>

        <?php if (!empty($report_type) && !empty($project_id)) : ?>
            <tr>
                <td colSpan="2"><input type="submit" onclick="$('#form_data').val(1);" value="Send" /></td>
            </tr>
        <?php endif; ?>
    <?php endif; ?>

    </table>
</form>

<!-- If we are sending to several suppliers, each document may be different (show different specs). We disable the preview in that case -->
<table class="tbl">
    <tr>
        <th style="width: 140px">PDF preview</th>

    <?php if ($supplier_id == -1) : ?>
        <td>Several suppliers are selected. Each PDF document may be different, so PDF preview is disabled.</td>
    <?php else :?>
        <td>
        <?php
        echo form_open($pdf_urls[$report_type]);
        echo form_hidden($pdf_url_params[$report_type]);
        echo form_input(array('type' => 'image', 'src' => "images/admin/icons/pdf_16.gif", 'class' => 'icon'));
        echo form_close();
        ?>
        </td>
    <?php endif; ?>

    </tr>
</table>
<?php $this->ckeditor->replace('email_body') ?>
<script type="text/javascript">
/*<![CDATA[ */
var project_id = <?php echo ($project_id) ? $project_id: '0'; ?>;
var bodies = <?php echo json_encode($bodies); ?>;
var report_type = <?php echo (!empty($report_type)) ? $report_type : '0'; ?>;
$('#email_settings').bind('submit', function() {
    CKEDITOR.instances.email_body.updateElement();
    return true;
});
//]]>
</script>
