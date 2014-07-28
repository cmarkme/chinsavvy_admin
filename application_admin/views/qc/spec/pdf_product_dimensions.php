<table cellspacing="0" cellpadding="8" border="1">
    <tr style="background-color: #DDDDDD;"><th colSpan="8"><strong>1. <?=$lang_strings['dimensions'][$lang]?></strong></th></tr>
    <tr><th<?=printf($style, 263)?>><strong><?=$lang_strings['part'][$lang]?></strong></th>
        <th<?=printf($style, 260)?>><strong><?=$lang_strings['length'][$lang]?>(mm)</strong></th>
        <th<?=printf($style, 261)?>><strong><?=$lang_strings['width'][$lang]?>(mm)</strong></th>
        <th<?=printf($style, 261)?>><strong><?=$lang_strings['height'][$lang]?>(mm)</strong></th>
        <th<?=printf($style, 271)?>><strong><?=$lang_strings['diameter'][$lang]?>(mm)</strong></th>
        <th<?=printf($style, 271)?>><strong><?=$lang_strings['thickness'][$lang]?>(mm)</strong></th>
        <th<?=printf($style, 261)?>><strong><?=$lang_strings['weight'][$lang]?>(g)</strong></th>
        <th<?=printf($style, 260)?>><strong><?=$lang_strings['other'][$lang]?></strong></th>
    </tr>
    <?=$parts_output?>
</table>
