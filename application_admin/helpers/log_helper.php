<?php
function log_user_action($message) {
    $ci = get_instance();
    log_message('info', 'User ' . $ci->user_model->get_name($ci->session->userdata('user_id')) . " $message");
}
