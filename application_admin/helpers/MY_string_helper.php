<?php
function stripslashes_deep($value) {
    if (is_object($value)) {
        return $value;
    }
    $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    return $value;
}

function cleanHTML($html) {
	return $html;

	// Chris Reid 13/12/2013
	// This class is no longer available and I don't know what it's meant to do!
    $tidy = new tidy();
    return $tidy->repairString($html, array('show-body-only' => true, 'output-html' => true, 'indent' => true), 'UTF8');
}

?>
