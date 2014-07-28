<?php
function get_error_message($errors = array(), $field='') {
    if (!empty($errors[$field])) {
        return '<span class="error">'.$errors[$field].'</span>';
    } else {
        return null;
    }
}
