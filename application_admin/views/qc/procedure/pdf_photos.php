<?php
if (!empty($photos)) {
    // Table of 2 x 3 per page
    // TODO refactor this so the HTML is in the view. A bit tricky right now
    $pdf->addPage();
    echo '<h2>Photos</h2>';
    $count = 1;
    $image_row = '<tr>';
    $description_row = '<tr>';
    $photos_html = '<table cellpadding="3" style="text-align: center;" border="1" width="600">';

    foreach ($photos as $photo) {
        $image_dir = ROOTPATH."/files/qc/procedures/{$procedure_data['id']}/photos/small/$photo->hash";

        if (!file_exists($image_dir)) {
            continue;
        }

        $image_row .= '<td colSpan="2" border="1" width="970"><img height="770" src="'.$image_dir.'" /></td>';
        $description_row .= '<td width="70">'.$count.'</td><td width="900">'.$photo->description.'</td>';

        if ($count % 6 == 0 && count($photos) != 6) {
            // Close photos html and print to pdf every 6 images
            $photos_html .= $image_row.'</tr>'.$description_row.'</tr></table>';
            $pdf->writeHTML($photos_html, false, false, false, false, '');
            $pdf->addPage();
            $photos_html = '<table cellpadding="3" style="text-align: center;" border="1" width="900">';
            $image_row = '<tr>';
            $description_row = '<tr>';
        } else if ($count % 2 == 0 && count($photos) != 2) {
            $image_row .= '</tr>';
            $description_row .= '</tr>';
            $photos_html .= $image_row . $description_row;
            $image_row = '<tr>';
            $description_row = '<tr>';
        }

        $count++;
    }

    $photos_html .= $image_row.'</tr>'.$description_row.'</tr></table>';
    echo $photos_html;
}
