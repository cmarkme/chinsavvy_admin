<?php
/**
 * Contains the Verification Controller class
 * @package controllers
 */

/**
 * Verification Controller class
 * @package controllers
 */
class Verification extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('verification' => array('index|Verification codes')));
        $this->config->set_item('exclude', array('index'));

        // Being a global controller, companies doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
    }

    public function index($outputtype='html') {
        require_capability('site:doanything');
        $this->load->helper('form_template');
        $this->db->from('config');
        $this->db->where('name', 'verification');
        $query = $this->db->get();
        if ($query->num_rows > 0) {
            $row = $query->result();
            $verification = $row[0]->value;
        } else {
            $verification = null;
        }
        $title = 'Google verification code';
        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'verification/edit',
                             'verification' => $verification
                             );

        form_element::$default_data = array('verification' => $verification);
        $this->load->view('template/default', $pageDetails);

    }

    public function process_edit() {

        require_capability('site:doanything');

        $required_fields = array('verification' => 'Google verification code');

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();
        $action_word = 'updated';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "Verification code has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit();
        }

        $this->db->where('name', 'verification')->update('config', array('value' => $this->input->post('verification')));

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("The Google verification code has been successfully $action_word!", 'success');
        redirect('verification');
    }
}
