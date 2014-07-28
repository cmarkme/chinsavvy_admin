<?php if ($lang == QC_SPEC_LANGUAGE_EN || $lang == QC_SPEC_LANGUAGE_COMBINED) : ?>
    <tr style="background-color: <?=$row_color?>;">
        <td width="80" rowspan="<?=$row_span?>"><?=$spec_cat_number?>.<?=$sub_number.$spec_number?></td>
        <td width="<?=$spec_width?>"><?=stripslashes($spec[QC_SPEC_LANGUAGE_EN]['data'])?></td>
    </tr>
<?php elseif ($lang == QC_SPEC_LANGUAGE_CH && $category_name != 'Files') : ?>
    <tr style="background-color: <?=$row_color?>;">
        <td width="80" rowspan="<?=$row_span?>"><?=$spec_cat_number?>.<?=$sub_number.$spec_number?></td>
        <td width="<?=$spec_width?>"><font face="chinese"><?=stripslashes($spec[QC_SPEC_LANGUAGE_CH]['data'])?></font></td>
    </tr>
<?php endif;

if ($lang == QC_SPEC_LANGUAGE_COMBINED && $category_name != 'Files') : ?>
    <tr style="background-color: <?=$row_color?>;">
        <td width="<?=$spec_width?>">
            <font face="chinese"><?=stripslashes($spec[QC_SPEC_LANGUAGE_CH]['data'])?></font>
        </td>
    </tr>
<?php endif; ?>
