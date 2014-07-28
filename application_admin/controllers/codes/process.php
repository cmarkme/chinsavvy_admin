<?php
/**
 * Contains the Process Controller class
 * @package controllers
 */

/**
 * Process Controller class
 * @package controllers
 */
class Process extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('codes' => array('codes/browse|Process Codes')));
        $this->config->set_item('exclude', array('browse'));
        $this->load->library('form_validation');
        $this->load->model('codes/process_model');
    }

    public function index() {
        redirect('codes/process/browse');
    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     */
    public function browse($outputtype='html') {
        require_capability('codes:viewprocesses');


        $this->load->helper('title');
        $this->load->library('filter');

        $this->filter->add_filter('combo', 'combo', array('codes_processes.id' => 'ID',
                                                          'codes_processes.code' => 'Code',
                                                          'codes_processes.description' => 'Description',
                                                          'codes_processes.creation_date' => 'Date'));

        $total_records = $this->process_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX) {
            $limit = 1;
        }

        $table_data = $this->process_model->get_data_for_listing(parent::process_datatable_params(), $this->filter->filters, $limit);

        if ($outputtype == 'html') {
            $table_data = parent::add_action_column('codes', 'process', $table_data, array('edit', 'delete'));
            $pageDetails = parent::get_ajax_table_page_details('codes', 'process', $table_data['headings'], array('add'));
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {
            $pageDetails = parent::get_export_page_details('process', $table_data);
            $pageDetails['widths'] = array(128, 240, 200, 300, 200, 200, 248, 375, 345);

            parent::output_for_export('codes', 'process', $outputtype, $pageDetails);
        }
    }

    public function add() {
        return $this->edit();
    }

    public function edit($process_id=null) {


        require_capability('codes:writeprocesses');
        $this->load->helper('form_template');
        $this->load->helper('date');

        if (!empty($process_id)) { // Editing an existing process
            require_capability('codes:editprocesses');
            $process_data = (array) $this->process_model->get($process_id);
            form_element::$default_data = $process_data;
            $title = "Edit {$process_data['code']} process";
        } else { // Adding a new process
            $title = "Create a new process";
        }

        $this->config->set_item('replacer', array('codes' => array('process|processes'), 'edit' => $title));

        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'codes/process/edit',
                             'process_id' => $process_id
                             );

        if (!empty($process_id)) {
             $pageDetails['revision_date'] = unix_to_human($process_data['revision_date']);
             $pageDetails['revision_user'] = $this->user_model->get_name($process_data['revision_user_id']);
        }

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('codes:editprocesses');
        $required_fields = array('description' => 'Description', 'code' => 'Code');

        if ($process_id = (int) $this->input->post('process_id')) {
            $process = $this->process_model->get($process_id);
            $redirect_url = 'codes/process/edit/'.$process_id;
        } else {
            $redirect_url = 'codes/process/add';
            $process_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $this->form_validation->set_rules('code', 'Code', 'trim|max_length[2]|required');

        $success = $this->form_validation->run();
        $action_word = ($process_id) ? 'updated' : 'created';

        // If a code was entered, check whether it is already being used by another supplier
        $code = $this->input->post('code');

        if (!empty($process_id)) {
            $this->db->where('id !=', $process_id);
        }

        if (!empty($code) && $this->process_model->get(array('code' => $code))) {
            add_message("This code ($code) is already used by another process, please try a different one.", 'error');
            $success = false;
        }

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "process $process_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($process_id);
        }

        $process_data = array('description' => $this->input->post('description'),'code' => $this->input->post('code'));

        if (empty($process_id)) {
            if (!($process_id = $this->process_model->add($process_data))) {
                add_message('Could not create this process!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->process_model->edit($process_id, $process_data)) {
                add_message('Could not update this process!', 'error');
                redirect($redirect_url);
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("Process $process_id has been successfully $action_word!", 'success');
        redirect('codes/process/browse');
    }
}
?>
