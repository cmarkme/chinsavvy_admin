<?=$main_title?>
<div id="page">
    <div id="details">
        <div id="detailsmessage"></div>
        <table id="detailstable" class="tbl">
            <tbody>
                <tr>
                    <th>CS Job No</th><td><?=$project_details['jobno']?></td>
                    <th>Product Name</th><td><a href="/qc/project/edit/<?=$project_id?>"><?=stripslashes($project_details['productname'])?></a></span></td>
                </tr>
                <tr>
                    <th>CS Product Code</th><td><?=$project_details['productcode']?></td>
                    <th>Customer Prod Code</th><td><?=stripslashes($project_details['customerproductcode'])?></span></td>
                </tr>
                <tr>
                    <th>Revision No</th><td><?=$project_details['revisionstring']?></td>
                    <th>Last revision date</th><td><?=$project_details['lastrevisiondate']?></td>
                </tr>
                <tr>
                    <th>Last udpated by</th><td><?=stripslashes($project_details['lastupdatedby'])?></td>
                    <th>Batch Size</th><td><?=stripslashes($project_details['batchsize'])?></td>
                </tr>
                <tr>
                    <th>Inspection level</th><td><?=get_lang_for_constant_value('QC_INSPECTION_LEVEL_', $project_details['inspectionlevel'])?></td>
                    <th>Sample Size</th><td><?=stripslashes($project_details['samplesize'])?></td>
                </tr>
                <tr>
                    <th>Related Products</th>
                    <td>
                    <?php if (!empty($project_details['related'])) : ?>
                            <ul id="relatedproducts">
                            <?php foreach ($project_details['related'] as $relatedid => $relatedname) : ?>
                                <li><a href="/qc/project/edit/<?=$relatedid?>"><?=$relatedname?></a></li>
                            <?php endforeach;
                        endif;
                    ?>
                    </td>
                    <th>Shipping Marks</th><td><?=stripslashes(nl2br($project_details['shippingmarks']))?></td>
                </tr>
                <tr>
                    <th>Permitted Defect</th><td>Cri[<?=$project_details['defectcriticallimit']?>%] Maj[<?=$project_details['defectmajorlimit']?>%] Min[<?=$project_details['defectminorlimit']?>%]</td>
                    <td colSpan="2">
                        <input <?=(empty($job_id)) ? 'disabled="disabled"' : '';?> id="restart" type="submit" value="Save revision and restart QC" onclick="window.location='/qc/process/restart/<?=$job_id?>';" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <?=$process_title?>
    <div id="report">
        <div id="reportmessage"></div>
        <form action="qc/process/qc_data/<?=$category_id?>/<?=$project_id?>" method="post">
        <table class="tbl" id="reportdetailstable">
        <?php if ($no_job) : ?>
            <tr><th style="width: 140px">Select a Supplier</th><td id="newsupplierselector"></td></tr>
        <?php else : ?>
            <tr><th style="width: 190px">Supplier</th><td id="supplierselector"></td></tr>
            <!--<tr><th>Add a Supplier</th><td id="newsupplierselector"></td></tr>-->
            <tr><th>Report date</th><td><input id="job_report_date" name="report_date" value="<?=$job->report_date?>" <?=$disabled?> /></td></tr>
            <tr><th>Inspection date</th><td><input id="job_inspection_date" name="inspection_date" value="<?=$job->inspection_date?>" <?=$disabled?> /></td></tr>
            <tr><th>QC Inspector Name</th><td><?=form_dropdown('user_id', $inspectors, $job->user_id, $disabled.' id="job_user_id" ')?></td></tr>
            <tr><th>Result</th>
                <td>
                    <span id="readonly-result">
                        <?php if ($disabled or $job->result == QC_RESULT_PASS or $job->result == QC_RESULT_HOLD) : ?>
                            <?=get_lang_for_constant_value('QC_RESULT_', $job->result)?>
                        <?php endif ?>
                    </span>
                    <?php if ( ! $disabled) : ?>
                        <select id="job_result" name="job_result"<?=($job->result == QC_RESULT_PASS or $job->result == QC_RESULT_HOLD) ? ' class="hidden"' : ''?>>
                            <? foreach(array(QC_RESULT_REJECT, QC_RESULT_CONCESSION_CUSTOMER, QC_RESULT_CONCESSION_CHINASAVVY) as $option) : ?>
                                <option value="<?= $option ?>"<?=$job->result == $option ? ' selected="selected"' : ''?>>
                                    <?=get_lang_for_constant_value('QC_RESULT', $option)?>
                                </option>
                            <? endforeach ?>
                        </select>
                    <?php endif ?>
                </td>
            </tr>

        <?php endif; ?>
        </table>
        </form>
    </div>

<?php if (!$no_job) : ?>
    <?=$files_title?>
    <div id="files">
    <div id="filesmessage"></div>
    <?php
    echo form_open_multipart("qc/process/process_file/$category_id/$project_id", array('id' => 'job_file_form'));
    ?>
    <table class="tbl" id="filestable">
        <tr>
            <th><?=form_hidden('id', $job_id);?>File name</th><th>Size</th><th>Description</th><th>Upload date</th><th class="actions">Actions</th>
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
    <?=$qc_checks_title?>
    <div id="checks">
    <div id="checksmessage"></div>
        <form action="qc/process/qc_data/<?=$category_id?>/<?=$project_id?>" method="post">
            <table id="qccheckstable" class="tbl">
                <thead>
                    <tr>
                        <th colSpan="2"><?=stripslashes($category_name)?></th>
                        <th class="importance">Importance</th>
                        <th class="photos">Inspection Photos</th>
                        <th class="checked">Checked</th>
                        <th class="defects">Permitted Defects</th>
                        <th class="defects">Actual Defects</th>
                        <th class="result">Result</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($process_specs as $spec_id => $spec) : ?>
                <?//dd($spec); ?>
                    <tr class="<?=$spec->rowclass?>">
                        <td class="specnumber"><?=$spec->subnumber.$spec->spec_number?></td>
                        <td class="description"><?=stripslashes($spec->data)?></td>
                        <td class="importance"><?=$spec->importance_label?></td>

                        <?php if (has_capability('qc:viewprocessphotos')) : ?>
                            <td class="photos">
                                <span class="photocount"><?=$spec->photos_count?></span>
                                <a href="/qc/job/photos/<?=$job->id?>/<?=$spec_id?>">
                                    <img src="images/admin/icons/camera_16.gif" class="icon" alt="Photos" title="Photos" />
                                </a>
                            </td>
                        <?php endif; ?>
                        <td class="checked">
                            <input id="checked_<?=$spec_id?>" type="checkbox" name="checked" <?=$spec->checked?><?= has_capability('qc:editqcprocesses') ? '': ' disabled="disabled"' ?>/>
                        </td>
                        <td class="permitted-defects">
                            <?php
                                switch($spec->importance) :
                                    case QC_SPEC_IMPORTANCE_CRITICAL:
                                        echo $permitted = floor($project_details['samplesize'] * ($project_details['defectcriticallimit'] / 100));
                                        break;
                                    case QC_SPEC_IMPORTANCE_MAJOR:
                                        echo $permitted = floor($project_details['samplesize'] * ($project_details['defectmajorlimit'] / 100));
                                        break;
                                    case QC_SPEC_IMPORTANCE_MINOR:
                                        echo $permitted = floor($project_details['samplesize'] * ($project_details['defectminorlimit'] / 100));
                                        break;
                                endswitch;
                            ?>
                        </td>
                        <td class="defects">
                            <?php if (has_capability('qc:editqcprocesses')) : ?>
                                <input id="defects_<?=$spec_id?>" type="text" name="defects" value="<?=@$spec->specs_result->defects?>" size="5" maxlength="7" /></td>
                            <?php else : ?>
                                <?=@$spec->specs_result->defects?>
                            <?php endif ?>
                        </td>
                        <td class="result">
                            <span id="result_<?=$spec_id?>" class="result-<?=(@ $spec->specs_result->checked and ($spec->specs_result->defects <= $permitted)) ? 'pass' : 'fail' ?>"></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </form>
    </div>
    <?=$additional_title?>
    <div id="specifications">
        <div id="specificationsmessage"></div>
            <table class="tbl" id="qcspecstable">
            <?php foreach ($job_specs as $spec_type => $spec_array) :
                $spec_count = 1;

                if ($spec_type == QC_SPEC_TYPE_ADDITIONAL) {
                    $title = 'Additional QA specifications';
                    $prefix = 'A';
                } else if ($spec_type == QC_SPEC_TYPE_OBSERVATION) {
                    $title = 'Additional observations by QC inspector';
                    $prefix = 'B';
                }
                ?>

                <tr>
                    <th colSpan="2"><?=$title?></th>
                    <th class="speccatactions">Actions</th>
                </tr>

                <?php foreach ($spec_array as $key => $specs) : ?>
                    <tr>
                        <td class="specnumber" rowspan="2"><?=$prefix.$spec_count?></td>
                        <td>
                            <form action="qc/process/edit_spec/<?=$category_id?>/<?=$project_id?>" method="post">
                                <div>
                                    <input type="hidden" name="spectype" value="<?=$spec_type?>" />
                                    <input type="hidden" name="spec_id" value="<?=$specs[QC_SPEC_LANGUAGE_EN]->id?>" />
                                    <input type="hidden" name="language" value="<?=QC_SPEC_LANGUAGE_EN?>" />
                                    <input type="hidden" name="job_id" value="<?=$job_id?>" />
                                    <input type="hidden" name="<?=$specs[QC_SPEC_LANGUAGE_EN]->hidden_field?>" value="<?=$specs[QC_SPEC_LANGUAGE_EN]->hidden_value?>" />
                                    <textarea name="data" cols="60" rows="2"><?=stripslashes($specs[QC_SPEC_LANGUAGE_EN]->data)?></textarea>
                                    <input type="submit" value="Save" />
                                </div>
                            </form>
                        </td>
                        <td class="specactions" rowspan="2">
                            <span class="photocount"><?=$specs[QC_SPEC_LANGUAGE_EN]->photos_count?></span>
                            <a href="/qc/job/photos/<?=$job_id?>/<?=$specs[QC_SPEC_LANGUAGE_EN]->id?>">
                                <img src="images/admin/icons/camera_16.gif" class="icon" alt="Photos" title="Photos" />
                            </a>
                            <form method="post" action="qc/process/delete_spec/<?=$category_id?>/<?=$project_id?>">
                                <input type="hidden" name="spec_id" value="<?=$specs[QC_SPEC_LANGUAGE_EN]->id?>" />
                                <input type="image" src="images/admin/icons/delete_16.gif" class="icon" onclick="return deletethis();" />
                            </form>
                        </td>
                    </tr>
                    <?php if (!empty($specs[QC_SPEC_LANGUAGE_CH]->data)) : ?>
                        <tr>
                            <td>
                                <form action="qc/process/<?=$specs[QC_SPEC_LANGUAGE_EN]->action?>/<?=$category_id?>/<?=$project_id?>" method="post">
                                    <div>
                                        <input type="hidden" name="spec_type" value="<?=$specs[QC_SPEC_LANGUAGE_EN]->type?>" />
                                        <input type="hidden" name="job_id" value="<?=$job_id?>" />
                                        <input type="hidden" name="language" value="<?=QC_SPEC_LANGUAGE_CH?>" />
                                        <input type="hidden" name="<?=$specs[QC_SPEC_LANGUAGE_EN]->hidden_field?>" value="<?=$specs[QC_SPEC_LANGUAGE_EN]->hidden_value?>" />
                                        <textarea <?=$specs[QC_SPEC_LANGUAGE_CH]->style.$specs[QC_SPEC_LANGUAGE_CH]->onclick?> name="data" cols="60" rows="2"><?=stripslashes($specs[QC_SPEC_LANGUAGE_CH]->data)?></textarea>
                                        <input type="submit" value="Save" />
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endif; ?>

                <?php $spec_count++;
                endforeach; ?>

                <tr>
                    <td class="specnumber" rowspan="2"><?=$prefix.$spec_count?></td>
                    <td>
                        <form action="qc/process/add_spec/<?=$category_id?>/<?=$project_id?>" method="post">
                            <div>
                                <input type="hidden" name="spec_type" value="<?=$spec_type?>" />
                                <input type="hidden" name="language" value="<?=QC_SPEC_LANGUAGE_EN?>" />
                                <input type="hidden" name="job_id" value="<?=$job_id?>" />
                                <textarea style="font-style: italic;" onclick="$(this).val('');$(this).css('font-style', '');$(this).attr('onclick', '');"
                                        name="data" cols="60" rows="2">Enter new English specification here...</textarea>
                                <input type="submit" value="Save" />
                            </div>
                        </form>
                    </td>
                    <td class="specactions" rowspan="2"></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
            <?php endforeach; ?>
            </table>
            <script type="text/javascript">
            /*<![CDATA[ */
            var job_id = <?=$job->id?>;
            //]]>
            </script>
        </div>
<?php endif; ?>
</div>
<script type="text/javascript">
/*<![CDATA[ */
var project_id = <?=$project_id?>;

<?php if (!empty($sample_size)) : ?>
    var sample_size = <?=$sample_size?>;
<?php endif; ?>

var category_id = <?=$category_id?>;

<?php if ($no_job) : ?>
    var job_id = null;
<?php endif; ?>
//]]>
</script>
