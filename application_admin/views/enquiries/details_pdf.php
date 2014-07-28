<?php $this->pdf->setX(160); ?>
<table cellpadding="3" border="1" style="font-size: 7pt;">
    <?php foreach ($data as $label => $value) : ?>
    <tr>
        <th width="270" style="text-align: right;"><strong><?=$label?></strong></th>
        <td width="400"><?=$value?></td>
    </tr>
    <?php endforeach; ?>
</table>
