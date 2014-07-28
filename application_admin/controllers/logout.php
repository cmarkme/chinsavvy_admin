<?php
/**
 * @package controllers
 */

class Logout extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('login_model', 'login', true);
    }

    function index() {
        $this->session->set_flashdata('message', 'You have been successfully logged out.');
        $this->login_model->logout();
        redirect('login');
    }
}
