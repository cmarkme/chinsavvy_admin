<?php echo $main_title ?>
<div id="page">
    <?=$options_title?>
    <div id="options">
        <table class="tbl">
            <tr>
                <td>
                    <label for="language">Language:</label>
                    <select name="language" id="language">
                        <option value="<?=QC_SPEC_LANGUAGE_EN?>">English</option>
                        <option value="<?=QC_SPEC_LANGUAGE_CH?>">Chinese</option>
                        <option value="<?=QC_SPEC_LANGUAGE_COMBINED?>">Combined</option>
                    </select>
                </td>
            </tr>
        </table>
    </div>

    <?php echo $this->filter->get_filter_table('documents'); ?>
    <?=$projects_title?>
    <div id="documenttable">
        <table id="ajaxtable" class="tbl">
            <thead>
                <tr>
        <?php foreach ($table_headings as $field => $label): ?>
                    <th class="<?=$field?>"><?=$label?></th>

        <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript" src="/includes/js/application/qc/documents.js">
/*<![CDATA[ */
//]]>
</script>

