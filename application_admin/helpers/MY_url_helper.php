<?php
/**
 * Returns a text link that sends data by POST (using a form) instead of an <a> tag
 * @param string $url
 * @param string $text
 * @param array $params POST parameters
 * @return string
 */
function anchor_post($url, $text, $params) {
    $id = substr(md5(time()), 5);
    $html = '<form method="post" id="'.$id.'" action="'.$url.'"><div onclick="$(\'#'.$id.'\').submit();" class="anchorpost">'."\n";
    foreach ($params as $key => $val) {
        $html .= '<input type="hidden" name="'.$key.'" value="'.$val.'" />'."\n";
    }
    $html .= $text.'</div></form>';
    return $html;
}
