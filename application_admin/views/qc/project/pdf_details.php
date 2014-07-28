
<table cellspacing="0" cellpadding="8" border="1">
    <tr>
        <th style="background-color: #DDDDDD;" colSpan="4"><strong><?=$lang_strings['projectdetails'][$lang]?></strong></th>
    </tr>
    <tr>
        <th<?=$style?>><strong><?=$lang_strings['jobno'][$lang]?></strong></th><td><?=$details['jobno']?></td>
        <th<?=$style?>><strong><?=$lang_strings['productname'][$lang]?></strong></th><td><?=$details['productnamestr'][$lang]?></td>
    </tr>

<?php if ($hide_customer_fields) : ?>
    <tr>
        <th<?=$style?>><strong><?=$lang_strings['productcode'][$lang]?></strong></th><td colspan="3"><?=$details['productcode']?></td>
    </tr>

<?php else : ?>
    <tr>
        <th<?=$style?>><strong><?=$lang_strings['productcode'][$lang]?></strong></th><td><?=$details['productcode']?></td>
        <th<?=$style?>><strong><?=$lang_strings['customerproductcode'][$lang]?></strong></th><td><?=$details['customerproductcode']?></td>
    </tr>

<?php endif; ?>

    <tr>
        <th<?=$style?>><strong><?=$lang_strings['revisionno'][$lang]?></strong></th><td><?=$spec_revision_no?></td>
        <th<?=$style?>><strong><?=$lang_strings['lastrevisiondate'][$lang]?></strong></th><td><?=$details['lastrevisiondate']?></td>
    </tr>
    <tr>
        <th<?=$style?>><strong><?=$lang_strings['lastupdatedby'][$lang]?></strong></th><td><?=$details['lastupdatedby']?></td>
        <th<?=$style?>><strong><?=$lang_strings['batchsize'][$lang]?></strong></th><td><?=$details['batchsize']?></td>
    </tr>
    <tr>
        <th<?=$style?>><strong><?=$lang_strings['inspectionlevel'][$lang]?></strong></th><td><?=get_lang_for_constant_value('QC_INSPECTION_LEVEL', $details['inspectionlevel'])?></td>
        <th<?=$style?>><strong><?=$lang_strings['samplesize'][$lang]?></strong></th><td><?=$details['samplesize']?></td>
    </tr>
    <tr>
        <th<?=$style?>><strong><?=$lang_strings['permitteddefect'][$lang]?></strong></th>
        <td>
            <?=$lang_strings['critical'][$lang]?>[<?=$details['defectcriticallimit']?>%]
            <?=$lang_strings['major'][$lang]?>[<?=$details['defectmajorlimit']?>%]
            <?=$lang_strings['minor'][$lang]?>[<?=$details['defectminorlimit']?>%]
        </td>
        <th<?=$style?>><strong><?=$lang_strings['result'][$lang]?></strong></th><td><?=get_lang_for_constant_value('QC_RESULT', $details['result'])?></td>
    </tr>

<?php if ($type == QC_SPEC_CATEGORY_TYPE_PRODUCT) : ?>
    <?=$related_products_row?>
<?php endif; ?>

<?php if ($type == QC_SPEC_CATEGORY_TYPE_PRODUCT || $type == QC_SPEC_CATEGORY_TYPE_QC) : ?>
    <?=$shipping_marks_row?>
<?php endif; ?>

</table>
