<?php
$pdf->setFont('tahoma', '', 7);
$pdf->setX(120);
$thwidth = 240;
$tdwidth = 690;
$output = '
<table cellpadding="8" border="1" style="font-size: 7pt;">
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Title</strong></th>
        <td width="'.$tdwidth.'">'.$procedure_data["title"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Summary</strong></th>
        <td width="'.$tdwidth.'">'.$procedure_data["summary"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Version</strong></th>
        <td width="'.$tdwidth.'">'.$procedure_data["version"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Test jigs, tools and equipment</strong></th>
        <td width="'.$tdwidth.'">'. $procedure_data["equipment"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Test jigs, tools and equipment (Chinese)</strong></th>
        <td width="'.$tdwidth.'">'.$procedure_data["equipment_ch"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Date Created</strong></th>
        <td width="'.$tdwidth.'">'.$procedure_data["creation_date"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Date Updated</strong></th>
        <td width="'.$tdwidth.'">'.$procedure_data["revision_date"].'</td>
    </tr>
    <tr>
        <th width="'.$thwidth.'" style="text-align: right;"><strong>Updated by</strong></th>
        <td width="'.$tdwidth.'">'.$procedure_data["updated_by"].'</td>
    </tr>
</table>';
echo $output;
