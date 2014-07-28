<?php $small_width = 180; ?>
<tr style="background-color: #EEEEEE;">
    <th width="920" colspan="3"><strong><?=$process_number . '. ' . $category->name?></strong></th>
    <th width="290"><?=$lang_strings['result'][$lang]?>: <?=get_lang_for_constant_value("QC_RESULT_", $job->result)?></th>
    <!--<th width="188.5"><?=$lang_strings['supplier'][$lang]?>: <?=$suppliers[$cat_id]['name']?></th>-->
    <!--<th width="100">Inspector: <?=get_lang_for_constant_value("QC_RESULT_", $job->result)?></th>-->
    <td width="<?=$small_width?>"><?=$lang_strings['importance'][$lang]?></td>
    <td width="<?=$small_width?>"><?=$lang_strings['checked'][$lang]?></td>
    <td width="<?=$small_width?>"><?=$lang_strings['critical'][$lang]?></td>
    <td width="<?=$small_width?>"><?=$lang_strings['major'][$lang]?></td>
    <td width="<?=$small_width?>"><?=$lang_strings['minor'][$lang]?></td>
</tr>
<?=$specs_output?>
