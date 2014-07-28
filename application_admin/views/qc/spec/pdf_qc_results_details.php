<?php
$header_width = 290;
$cell_width = 350;
?>
<p>Works</p>
<table border="1" cellpadding="8">
    <tr><th width="<?=$header_width?>" style="background-color: #EEEEEE;"><strong><?=$lang_strings['supplier'][$lang]?></strong></th><td width="<?=$cell_width?>"><?=$supplier->name?></td></tr>
    <tr><th width="<?=$header_width?>" style="background-color: #EEEEEE;"><strong><?=$lang_strings['reportdate'][$lang]?></strong></th><td width="<?=$cell_width?>"><?=$report_date?></td></tr>
    <tr><th width="<?=$header_width?>" style="background-color: #EEEEEE;"><strong><?=$lang_strings['inspectiondate'][$lang]?></strong></th><td width="<?=$cell_width?>"><?=$inspection_date?></td></tr>
    <tr><th width="<?=$header_width?>" style="background-color: #EEEEEE;"><strong><?=$lang_strings['qcinspectorname'][$lang]?></strong></th><td width="<?=$cell_width?>"><?=$inspector?></td></tr>
    <tr><th width="<?=$header_width?>" style="background-color: #EEEEEE;"><strong><?=$lang_strings['result'][$lang]?></strong></th><td width="<?=$cell_width?>"><?=get_lang_for_constant_value('QC_RESULT_', $job->result)?></td></tr>
</table>
