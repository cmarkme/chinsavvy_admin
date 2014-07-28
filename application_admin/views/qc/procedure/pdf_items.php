<?php
$items_html = '<h2>Procedure steps</h2>';
$pdf->call_method('moveY', array(-2));
if (!empty($items)) {
    $items_html .= '
    <table cellpadding="8" border="1">
        <tr>
            <th width="250">Number</th><th width="900">English</th><th width="900">Chinese</th>
        </tr>';

    foreach ($items as $item) {
        $items_html .= '<tr>
            <td width="250">'.$item->number.'</th>
            <td width="900">'.$item->item.'</td>
            <td width="900"><font face="chinese">'.$item->item_ch.'</font></td>
        </tr>';
    }

    $items_html .= '</table>';
} else {
    $items_html .= '<p>No procedure steps yet.</p>';
}

echo $items_html;
