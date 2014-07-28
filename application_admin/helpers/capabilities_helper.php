<?php
/**
 * @package helpers
 */
/**
 * Returns true if the currently logged in user or the given user
 * has the requested capability
 * @param string $capname
 * @param int $userid
 * @param boolean $allowdoanything Whether or not to allow the doanything cap to override everything
 * @return boolean
 */
function has_capability($capname, $user_id=null, $allowdoanything=true) {
    $ci = get_instance();
    $usercaps = $ci->session->userdata('user_caps');
    if (empty($usercaps)) {
        return false;
    }

    $caps = $ci->session->userdata('user_caps');

    if (!empty($user_id)) {
        $caps = $ci->user_model->get_capabilities($user_id);
    }
    return in_array($capname, $caps) || ($allowdoanything && in_array('site:doanything', $caps));
}
/**
 * Checks if the currently logged in user has the requested
 * capability, and throws a fatal error if not.
 * @param string $capname
 * @param boolean $allowdoanything Whether or not to allow the doanything cap to override everything
 * @return void
 */
function require_capability($capname, $allowdoanything=true) {
    $ci = get_instance();
    if (!has_capability($capname, null, $allowdoanything)) {
        redirect('access/unauthorised/'.$capname);
    }
}
?>
