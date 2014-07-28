<?php
if (!empty($files)) {

    $files_html = '<h2>Files</h2>';
    $pdf->call_method('moveY', array(-2));

    $files_html .= '<table cellpadding="8" border="1">
        <tr>
            <th>Name</th><th>Size</th><th>Description</th><th>Upload date</th>
        </tr>';
    foreach ($files as $file) {

        $unit = 'MB';
        if ($file->file_size < 1000) {
            $unit = 'KB';
        } else {
            $file->file_size = round($file->file_size / 1000, 2);
        }
        $files_html .= '
            <tr>
                <td>'.$file->file.'</th>
                <td>'.$file->file_size.' '.$unit.'</td>
                <td>'.$file->description.'</td>
                <td>'.unix_to_human($file->creation_date).'</td>
            </tr>';
    }
    $files_html .= '</table>';
    echo $files_html;
}

