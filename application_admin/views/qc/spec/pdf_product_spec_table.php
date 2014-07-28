<?php if ($lang == QC_SPEC_LANGUAGE_EN || $lang == QC_SPEC_LANGUAGE_COMBINED) : ?>
    <tr>
        <td width="80" rowspan="<?=$row_span?>"><?=$spec_cat_number?>.<?=$spec_number?></td>
        <td width="<?=$spec_width?>"><?=stripslashes($spec[QC_SPEC_LANGUAGE_EN]['data'])?></td>
    </tr>
<?php elseif ($lang == QC_SPEC_LANGUAGE_CH) : ?>
    <tr>
        <td width="80" rowspan="<?=$row_span?>"><?=$spec_cat_number?>.<?=$spec_number?></td>
        <td width="<?=$spec_width?>"><font face="chinese"><?=stripslashes($spec[QC_SPEC_LANGUAGE_CH]['data'])?></font></td>
    </tr>
<?php endif;

if ($lang == QC_SPEC_LANGUAGE_COMBINED) : ?>
    <tr>
        <td width="<?=$spec_width?>">
            <font face="chinese"><?=stripslashes($spec[QC_SPEC_LANGUAGE_CH]['data'])?></font>
        </td>
    </tr>
<?php endif; ?>
