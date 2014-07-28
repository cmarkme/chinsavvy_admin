<?php
echo $main_title;
echo '<div id="procedure">';
echo $details_title;
echo '<div id="details">';
echo validation_errors();
echo form_open('qc/procedure/process_edit/', array('id' => 'procedure_edit_form'));
echo '<div>';
print_form_container_open();

print_static_form_element('Number', $procedure_number);
if (!empty($procedure_id)) {
    echo form_hidden('id', $procedure_id);
    print_static_form_element('Version', $procedure_version);
} else {
    print_static_form_element('Version', 1);
}

print_input_element('Title', array('name' => 'title', 'size' => '50'), true);
print_textarea_element('Summary', array('name' => 'summary', 'cols' => 50, 'rows' => 5), true);
print_textarea_element('Test jigs, tools and equipment required', array('name' => 'equipment', 'cols' => 50, 'rows' => 5));
print_textarea_element('Test jigs, tools and equipment required (Chinese)', array('name' => 'equipment_ch', 'cols' => 50, 'rows' => 5));

if (empty($procedure_id)) {
    $button_label = 'Add new procedure';
} else {
    print_static_form_element('Date updated', $revision_date);
    print_static_form_element('Updated by', $revision_user);
    print_checkbox_element('Notify related QC Projects about this update', array('name' => 'notify_projects', 'value' => 1));
    $button_label = 'Update procedure';
}

print_submit_container_open();
echo form_submit('button', $button_label, 'id="submit_button"');
// TODO Cancel button
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';

// Procedure items
if (!empty($procedure_id)) {
echo $items_title;
?>

<div id="items">
<div id="itemsmessage"></div>
<table class="tbl" id="itemstable">
    <tr>
        <th>Number</th><th>English</th><th>Chinese</th><th class="actions">Actions</th>
    </tr>

<?php
foreach ($items as $item) {
    echo '<tr id="edit_item_row_'.$item->id.'">
        <td class="tiny" id="edit_item_number_'.$item->id.'">'.$item->number.'</td>
        <td class="textarea" id="edit_item_item_'.$item->id.'">'.$item->item.'</td>
        <td class="textarea" id="edit_item_itemch_'.$item->id.'">'.$item->item_ch.'</td>
        <td class="actions">
            <div id="edit_item_'.$item->id.'" class="edit icon"></div>
            <div id="delete_item_'.$item->id.'" class="delete icon"></div>
        </td>
    </tr>';
}
?>

</table></div>

<?php
// Files
echo $files_title;
?>

<div id="files">
<div id="filesmessage"></div>
<?php
echo form_open_multipart('qc/procedure/process_file/', array('id' => 'procedure_file_form'));
?>
<table class="tbl" id="filestable">
    <tr>
        <th><?=form_hidden('id', $procedure_id);?>File name</th><th>Size</th><th>Description</th><th>Upload date</th><th class="actions">Actions</th>
    </tr>

<?php
foreach ($files as $file) {
    $unit = 'MB';
    if ($file->file_size < 1000) {
        $unit = 'KB';
    } else {
        $file->file_size = round($file->file_size / 1000, 2);
    }

    echo "<tr id=\"edit_file_row_$file->id\">
        <td>$file->file</td>
        <td>$file->file_size $unit</td>
        <td class=\"textarea\" id=\"edit_file_description\">$file->description</td>
        <td>".mdate('%d/%m/%Y', $file->creation_date)."</td>
        <td class=\"actions\">
            <div id=\"edit_file_".$file->id."\" class=\"edit icon\"></div>
            <div id=\"download_file_".$file->id."\" class=\"pdf icon\"></div>
            <div id=\"delete_file_".$file->id."\" class=\"delete icon\"></div>
        </td>
    </tr>";
}
?>

</table></form></div>
</div>

<?php
// Photos
echo $photos_title;
?>
<div id="photos">
<div id="photosmessage"></div>
<?php
echo form_open_multipart('qc/procedure/process_photo/', array('id' => 'procedure_photo_form'));
?>
<table class="tbl" id="photostable">
    <tr>
        <th><?=form_hidden('id', $procedure_id);?>Photo name</th><th>Preview</th><th>Description</th><th>Dimensions</th><th>Upload date</th><th class="actions">Actions</th>
    </tr>

<?php
foreach ($photos as $photo) {
    echo "<tr id=\"edit_photo_row_$photo->id\">
        <td>$photo->file</td>
        <td><a title=\"$photo->description\" href=\"files/qc/procedures/$procedure_id/photos/medium/$photo->hash\" rel=\"lightbox\">
            <img src=\"files/qc/procedures/$procedure_id/photos/thumb/$photo->hash\" /></a></td>
        <td class=\"textarea\" id=\"edit_photo_description\">$photo->description</td>
        <td>$photo->image_width x $photo->image_height</td>
        <td>".mdate('%d/%m/%Y', $photo->creation_date)."</td>
        <td class=\"actions\">
            <div id=\"edit_photo_".$photo->id."\" class=\"edit icon\"></div>
            <div id=\"delete_photo_".$photo->id."\" class=\"delete icon\"></div>
        </td>
    </tr>";
}
?>

</table></form></div>

<?=$projects_title?>
<div id="projects">
    <table class="tbl">
        <tr>
            <th width="40">Project ID</th>
            <th>Product Code</th>
            <th>Product Name</th>
        </tr>
        <?php foreach ($projects as $project_id => $project) : ?>
            <tr title="View this QC Project" id="qcproject_<?=$project_id?>">
                <td><?=$project_id?></td>
                <td><?=$project['productcode']?></td>
                <td><?=$project['productname']?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<script type="text/javascript">
/*<![CDATA[ */
var procedureid = <?php echo ($procedure_id) ? $procedure_id: '0'; ?>;
//]]>
</script>

<?php } ?>
