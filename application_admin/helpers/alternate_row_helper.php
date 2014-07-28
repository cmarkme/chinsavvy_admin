<?php
function alternate_row($reset = false) {
    static $current_row = 'odd';
    $current_row = ($reset || $current_row == 'even') ? 'odd' : 'even';
    return $current_row;
}
