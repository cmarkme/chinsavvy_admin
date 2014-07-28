<form id="fileinfo_form" action="/vault/filemanager/process_documents" method="post">
<input type="hidden" name="folder" value="<?=$temp_files[0]->folder?>" />
<table class="tbl">
<thead>
    <tr>
        <th class="new_filename">New File Name</th>
        <th class="type">Type</th>
        <th class="identity">Identity</th>
        <th class="enquiry_id">Enquiry ID</th>
        <th class="customer_id">Customer</th>
        <th class="customer_part_code">Customer part code</th>
        <th class="chinasavvy_product_code">Chinasavvy product code</th>
        <th class="chinasavvy_part_code">Chinasavvy part code</th>
        <th class="version_date">Version date</th>
        <th class="version">Version number</th>
        <th class="is_new">New file?</th>
    </tr>
</thead>
<tbody>
<?php foreach ($temp_files as $key => $file) : ?>
    <tr>
        <td class="new_filename"><?=$file->new_filename?></td>
        <td class="type"><?=form_dropdown('type['.$key.']', $types, null)?></td>
        <td class="identity"><?=form_dropdown('identity['.$key.']', $identities, 0)?></td>
        <td class="enquiry_id"><?=form_input(array('name' => 'enquiry_id['.$key.']', 'disabled' => "disabled"))?></td>
        <td class="customer_id"><?=form_input(array('name' => 'customer_id['.$key.']', 'disabled' => "disabled"))?></td>
        <td class="customer_part_code"><?=form_input(array('name' => 'customer_part_code['.$key.']', 'type' => 'text'))?></td>
        <td class="chinasavvy_product_code"><?=form_input(array('name' => 'chinasavvy_product_code['.$key.']', 'type' => 'text', 'disabled' => "disabled"))?></td>
        <td class="chinasavvy_part_code"><?=form_input(array('name' => 'chinasavvy_part_code['.$key.']', 'type' => 'text', 'disabled' => "disabled"))?></td>
        <td class="version_date"><?=form_input(array('version_date' => 'version_date['.$key.']', 'class' => 'date_input', 'type' => 'text'),
            mdate('%d %m %Y %h:%i %a', $file->creation_date))?></td>
        <td class="version">1</td>
        <td class="is_new"><?=form_checkbox(array('name' => 'is_new['.$key.']'))?></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<div><input type="submit" value="Submit" /></div>
</form>
<script type="text/javascript">
/*<![CDATA[ */
$(document).ready(function() {
    // Hide columns
    $('.enquiry_id,.customer_id,.chinasavvy_part_code,.chinasavvy_product_code').hide();

    $('.type select').bind('change', function(event) {
        if ($(this).val() == constants.VAULT_FILE_TYPE_ENQUIRY) {
            $('.enquiry_id').show();
            $(this).parents('td.type').siblings('td.enquiry_id').children('input').removeAttr('disabled');
            $(this).parents('td.type').siblings('td.chinasavvy_part_code,td.chinasavvy_product_code,td.customer_id').children('input').attr('disabled', 'disabled');
        } else if ($(this).val() == constants.VAULT_FILE_TYPE_ORDER) {
            $('.chinasavvy_product_code,.chinasavvy_part_code,.customer_id').show();
            $(this).parents('td.type').siblings('td.chinasavvy_part_code,td.chinasavvy_product_code,td.customer_id').children('input').removeAttr('disabled');
            $(this).parents('td.type').siblings('td.enquiry_id').children('input').attr('disabled', 'disabled');
        }
        toggleColumns();
    });

    function toggleColumns() {
        var enquiries = false;
        var orders = false;
        $('.type select').each(function() {
            if ($(this).val() == constants.VAULT_FILE_TYPE_ENQUIRY) {
                enquiries = true;
            } else if ($(this).val() == constants.VAULT_FILE_TYPE_ORDER) {
                orders = true;
            }
        });
        if (!enquiries) {
            $('.enquiry_id').hide();
        }
        if (!orders) {
            $('.chinasavvy_product_code,.chinasavvy_part_code,td.customer_id').hide();
        }
    }

    $('#fileinfo_form').bind('submit', function() {
        // Validation

    });

    $('.date_input').each(function() {
        $(this).datetimepicker({
            dateFormat: 'dd mm yy',
            showSecond: false,
            timeFormat: 'hh:mm tt',
            addSliderAccess: true,
            ampm: true,
            sliderAccessArgs: { touchonly: false },
            debug: true

        });
    });

    $('select[name^="enquiry_id"]').autocomplete({

    });
});
//]]>
</script>
