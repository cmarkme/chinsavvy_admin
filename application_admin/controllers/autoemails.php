<?php
/**
 * Contains the Autoemails Controller class
 * @package controllers
 */

/**
 * Autoemails Controller class
 * @package controllers
 */
class Autoemails extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('autoemail_model');
    }

    function index() {
        return $this->browse();
    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     */
    function browse($outputtype='html') {
        require_capability('site:editautoemails');
        $total_records = $this->autoemail_model->count_all_results();
        $this->load->library('filter');

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX) {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($outputtype, array('pdf'));

        if ($outputtype != 'html') {
            $limit = $datatable_params['perpage'];
        }

        $table_data = $this->autoemail_model->get_data_for_listing($datatable_params, array(), $limit);

        $table_data = parent::add_action_column('site', 'autoemails', $table_data, array('edit'));
        $pageDetails = parent::get_ajax_table_page_details('site', 'autoemail', $table_data['headings'], array('add'));

        parent::output_ajax_table($pageDetails, $table_data, $total_records);
    }

    public function edit($autoemail_id) {
        require_capability('site:editautoemails');
        $this->load->helper('form_template');
        $this->load->helper('date');
        $this->load->library('CKeditor');

        $autoemail_data = (array) $this->autoemail_model->get($autoemail_id);
        if ($autoemail_data['status'] != 'Active') {
            $autoemail_data['status'] = false;
        }
        form_element::$default_data = $autoemail_data;
        $title = "Edit autoemail: {$autoemail_data['name']}";

        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());
        $emails_in_queue = count($this->autoemail_model->get_emails($autoemail_id));

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'autoemail/edit',
                             'autoemail_id' => $autoemail_id,
                             'emails_in_queue' => $emails_in_queue,
                             'autoemail' => $this->autoemail_model->get($autoemail_id),
                             'jstoloadinfooter' => array('jquery/jquery.json')
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {
        require_capability('site:editautoemails');
        $required_fields = array('message' => 'Email message', 'subject' => 'Subject');

        $autoemail_id = (int) $this->input->post('id');
        $autoemail = $this->autoemail_model->get($autoemail_id);
        $redirect_url = 'autoemails/edit/'.$autoemail_id;

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "autoemail $autoemail_id has been successfully updated!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($autoemail_id);
        }

        $status = ($this->input->post('status')) ? 'Active' : 'Inactive';

        $autoemail_data = array('subject' => $this->input->post('subject'),
                              'message' => $this->input->post('message'),
                              'status' => $status
                              );

        if (!$this->autoemail_model->edit($autoemail_id, $autoemail_data)) {
            add_message('Could not update this autoemail!', 'error');
            redirect($redirect_url);
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("The autoemail $autoemail_id has been successfully updated!", 'success');
        redirect('autoemails/browse');
    }
}
