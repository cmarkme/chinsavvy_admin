<?php
echo $section_title;
echo '<div id="page">';
echo validation_errors();
echo form_open_multipart('enquiries/outbound_send/show/'.$quotation_id, array('id' => 'enquiry_edit_form'));
echo '<div>';
echo form_hidden('quotation_id', $quotation_id);
echo form_hidden('staff_id', $staff_id);
?>

<table class="tbl">
    <tr><th>Document</th><th>Preview</th><th>Include</th></tr>
    <tr>
        <td>Quotation</td>
        <td>
            <a href="/enquiries/outbound_send/serve_pdf/<?=$quotation_id?>">
                <?=img(array('src' => 'images/admin/icons/pdf_16.gif', 'class' => 'icon nofloat'))?>
            </a>
        </td>
        <td><?=form_checkbox(array('name' => 'quotation', 'readonly' => true, 'checked' => true, 'value' => 1))?></td>
    </tr>
<?php foreach ($public_files as $public_file) : ?>
    <tr>
        <td><?=$public_file->filename_original?></td>
        <td>
            <a href="/enquiries/outbound_send/serve_file/<?=$public_file->id?>/<?=$quotation_id?>">
                <?=img(array('src' => 'images/admin/icons/pdf_16.gif', 'class' => 'icon nofloat'))?>
            </a>
        </td>
        <td><?=form_checkbox(array('name' => 'public_files['.$public_file->id.']', 'checked' => true, 'value' => 1))?></td>
    </tr>
<?php endforeach; ?>

<?php foreach ($files as $file) : ?>
    <tr>
        <td><?=$file->filename_original?></td>
        <td>
            <a href="/enquiries/outbound_send/serve_file/<?=$file->id?>/<?=$quotation_id?>">
                <?=img(array('src' => 'images/admin/icons/documents_16.gif', 'class' => 'icon nofloat'))?>
            </a>
        </td>
        <td><?=form_button('remove_'.$file->id, 'Remove file', 'onclick="window.location=\'/enquiries/outbound_send/delete_file/'.$quotation_id.'/'.$file->id.'\'"')?></td>
    </tr>
<?php endforeach; ?>

    <tr>
        <td colspan="2">
            <?=form_submit('attach', 'Add a File')?>
            <?=form_upload(array('name' => 'extra_file', 'size' => 'chars'))?>
        </td>
        <td>
            <?=form_submit('send', 'Email Enquirer')?>
        </td>
    </tr>

    <tr><th colspan="3">Additional email recipients</th></tr>

<?php for ($i = 1; $i < 6; $i++) :
    $error_class = (form_error("email$i")) ? ' error ' : '';
    ?>
    <tr class="<?=$error_class?>">
        <td>Email <?=$i?></td>
        <td colspan="2"><?=form_input(array('name' => "email$i", 'value' => set_value("email$i"), 'size' => 70))?></td>
    </tr>
<?php endfor; ?>

</table>
</div>
</form>
</div>
