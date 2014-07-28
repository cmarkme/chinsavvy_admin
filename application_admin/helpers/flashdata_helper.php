<?php

/**
 * Use this function to append several messages to the 'message' flashdata or userdata variable
 * @param string $message
 * @param bool $delayed If true, will put variable in flashdata, otherwise in userdata for immediate use. Don't forget to clear the userdata once the message has been shown!
 */
function add_message($message, $type='success', $delayed=false) {
    $ci = get_instance();
    $current_message = ($delayed) ? $ci->session->userdata('flash:new:message') : $ci->session->userdata('message');
    if (!empty($current_message)) {
        if ($delayed) {
            $ci->session->set_flashdata('message', $current_message . "<br />$message");
            $ci->session->set_flashdata('message_type', $type);
        } else {
            $ci->session->set_userdata('message', $current_message . "<br />$message");
            $ci->session->set_userdata('message_type', $type);
        }
    } else {
        if ($delayed) {
            $ci->session->set_flashdata('message', $message);
            $ci->session->set_flashdata('message_type', $type);
        } else {
            $ci->session->set_userdata('message', $message);
            $ci->session->set_userdata('message_type', $type);
        }
    }
}

function clear_messages() {
    $ci = get_instance();
    $ci->session->set_userdata('flash:new:message', null);
    $ci->session->set_userdata('flash:new:message_type', null);
    $ci->session->set_userdata('message', null);
    $ci->session->set_userdata('message_type', null);
}
