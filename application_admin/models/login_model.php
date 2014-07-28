<?php
/**
 * Contains the Login Model class
 * @package models
 */

/**
 * Login Model class
 * @package models
 */

class Login_Model extends CI_Model {
    /**
     * Authenticates a user by username/password credentials. Uses the 'users' DB table.
     * @uses User_Model
     * @access public
     * @param string $username
     * @param string $password
     * @return bool
     * TODO Log login attempts into DB
     * TODO use flash data to replace show_error() calls and display a nice message instead
     */
    function auth_user($username, $password) {
        $this->load->library('encrypt');

        if ($user = $this->user_model->get(array('username' => $username, 'status' => 'Active'), true)) {
            if ($this->encrypt->decode($user->password) == $password) {
                $this->benchmark->mark('get_capabilities_start');
                $user_caps = $this->user_model->get_capabilities($user->id);
                $this->benchmark->mark('get_capabilities_end');

                if (empty($user_caps)) {
                    $this->session->set_flashdata('message', 'You do not have any permissions on this site, '
                            . 'please contact the administrator to obtain site permissions.');
                    return false;
                }
                $simple_user_caps = array();
                foreach ($user_caps as $cap) {
                    $simple_user_caps[] = $cap->name;
                }

                log_message('info', 'User ' . $this->user_model->get_name($user->id) . ' has just logged in!');
                $this->session->set_userdata(array('user_caps' => $simple_user_caps,
                                                 'user_id' => $user->id,
                                                 'username' => $user->username,
                                                 'roles' => $this->user_model->get_roles($user->id)));
                return true;
            } else {
                $this->session->set_flashdata('message', 'Incorrect username or password, '
                        . 'please verify your details and try again.');
                return false;
            }
        } else {
            $this->session->set_flashdata('message','Incorrect username or password, '
                    . 'please verify your details and try again.');
            return false;
        }
    }

    /**
     * Logs the currently logged-in user out, using DB-based session variables. Also removes capabilities from session data.
     */
    function logout() {
        log_message('info', 'User ' . $this->user_model->get_name($this->session->userdata('user_id')) . ' has just logged out!');
        $this->session->unset_userdata('user_caps');
        $this->session->unset_userdata('user_id');
    }

    /**
     * Checks whether the user is logged in by looking at session variables held in DB.
     * @return bool
     */
    function check_session() {
        $usercaps = $this->session->userdata('user_caps');
        return $this->session->userdata('user_id') && !empty($usercaps);
    }
}
?>
