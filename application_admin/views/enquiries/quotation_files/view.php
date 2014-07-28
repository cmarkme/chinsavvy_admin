<?php
echo $section_title;
echo '<div id="page">';
echo validation_errors();
echo form_open_multipart('enquiries/quotation_files/show', array('id' => 'quotation_file_edit_form'));
echo '<div>';
?>

<table class="tbl">
    <tr><th>Document</th><th>Preview</th><th>Include</th></tr>
<?php foreach ($files as $file) : ?>
    <tr>
        <td><?=$file->filename_original?></td>
        <td>
            <a href="/enquiries/quotation_files/serve_file/<?=$file->id?>">
                <?=img(array('src' => 'images/admin/icons/documents_16.gif', 'class' => 'icon nofloat'))?>
            </a>
        </td>
        <td><?=form_button('remove_'.$file->id, 'Remove file', 'onclick="window.location=\'/enquiries/quotation_files/delete_file/'.$file->id.'\'"')?></td>
    </tr>
<?php endforeach; ?>
    <tr>
        <td colspan="3">
            <?=form_submit('attach', 'Add a File')?>
            <?=form_upload(array('name' => 'new_file', 'size' => 'chars'))?>
        </td>
    </tr>
</table>
</div>
</form>
</div>
