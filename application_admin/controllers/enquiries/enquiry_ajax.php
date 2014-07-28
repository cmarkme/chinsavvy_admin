<?php
/**
 * Contains the Enquiry_Ajax controller
 * @package controllers
 */

/**
 * Enquiry_Ajax controller class
 * @package controllers
 */
class Enquiry_Ajax extends MY_AJAX_Controller {
    public $subsystem = 'enquiries';

    public function __construct() {

        parent::__construct();
        $this->load->model('enquiries/enquiry_model');
        $this->model = $this->enquiry_model;
    }

    /**
     * Do not call this method directly, it is used by the parent class
     * within the get_json() method.
     */
    protected function populate_output($output) {

    }

    public function setup_model() {

    }
}
