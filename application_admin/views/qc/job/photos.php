<?php echo $main_title ?>
<div id="spec">
<?php echo $details_title ?>
<div id="details">
    <table class="tbl">
        <tr>
            <th style="width: 12em">Product</th><td><a href="/qc/project/edit/<?=$project_id?>"><?=$this->project_model->get_name($part_id)?></a></td>
        </tr>
        <tr>
            <th>Category</th><td><?=$category_name?></td>
        </tr>
        <tr>
            <th>Specification</th><td colSpan="3"><?=$spec->data?></td>
        </tr>
    </table>
    <input type="button" onclick="window.location='/qc/process/qc_data/<?=$job->category_id?>/<?=$job->project_id?>';" value="Return to QC process" />

</div>

<?php echo $photos_title ?>
<div id="photos">
<?php if (has_capability("qc:writeprocessphotos")) : ?>
    <form method="post" action="qc/job/upload_photos" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="job_id" value="<?=$job->id?>" />
        <input type="hidden" name="spec_id" value="<?=$spec->id?>" />
        <label for="fileuploads">Upload new photos</label>
        <input id="fileuploads" type="file" name="photos[]" accept="gif|jpg|jpeg|png|bmp|tif|tiff" />
        <input type="submit" value="Upload photos" />
    </div>
    </form>
<?php endif;
$photonumber = 1;
?>
<div id="photolisting">

<?php foreach ($photos as $photo) :
    $imgurl = "files/qc/$job->project_id/process/$job->id/small/$photo->hash";
?>
    <div class="specphoto">
            <h4>
                Photo #<?=$photonumber?>
            </h4>
            <div class="photo">
                <img src="<?=$imgurl?>" alt="<?=$photo->file?>" />
            </div>
            <p class="info">
                Uploaded on <?=gmdate('M d, Y', $photo->creation_date);?> |
                <a class="deletephoto" onclick="return deletethis();" href="/qc/job/delete_photo/<?=$photo->id?>">delete</a>
            </p>
            <fieldset><legend>Description</legend>
            <p class="description" id="editdescription_<?=$photo->id?>">
                <?=$photo->description?>
            </p>
            </fieldset>
        </div>

    <?php $photonumber++;

endforeach;?>
    </div>
</div>
</div>
<script type="text/javascript">
/*<![CDATA[ */
$(function() {
    $('#fileuploads').MultiFile({
        STRING: {
            remove: '<img class="icon" style="float: none" title="Delete this file" src="images/admin/icons/delete_16.gif" alt="x" />'
        }
    });

    $('.description').addClass('edit');
    $('.description').each(function (i) {
        var matches = $(this).attr('id').match(/editdescription_([0-9]*)/);
        $(this).editable('qc/job/edit_photo_description/'+matches[1], {
            id: 'action',
            type: 'textarea',
            submit: 'Save',
            cancel: 'Cancel',
            tooltip: 'Click to edit...',
            indicator: 'Saving...',
            onblur: 'submit'
        });
    });
});
//]]>
</script>
