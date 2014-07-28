<?php
/**
 * Contains the Division Controller class
 * @package controllers
 */

/**
 * Division Controller class
 * @package controllers
 */
class Division extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('codes' => array('codes/browse|Division Codes')));
        $this->config->set_item('exclude', array('browse'));
        $this->load->library('form_validation');
        $this->load->model('codes/division_model');
    }

    public function index() {
        redirect('codes/division/browse');
    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     */
    public function browse($outputtype='html') {
        require_capability('codes:viewdivisions');


        $this->load->helper('title');
        $this->load->library('filter');

        $this->filter->add_filter('combo', 'combo', array('codes_divisions.id' => 'ID',
                                                          'codes_divisions.name' => 'Name',
                                                          'codes_divisions.code' => 'Code',
                                                          'codes_divisions.creation_date' => 'Date'));

        $total_records = $this->division_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX) {
            $limit = 1;
        }

        $table_data = $this->division_model->get_data_for_listing(parent::process_datatable_params(), $this->filter->filters, $limit);

        if ($outputtype == 'html') {
            $table_data = parent::add_action_column('codes', 'division', $table_data, array('edit', 'delete'));
            $pageDetails = parent::get_ajax_table_page_details('codes', 'division', $table_data['headings'], array('add'));
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {
            $pageDetails = parent::get_export_page_details('division', $table_data);
            $pageDetails['widths'] = array(128, 240, 200, 300, 200, 200, 248, 375, 345);

            parent::output_for_export('codes', 'division', $outputtype, $pageDetails);
        }
    }

    public function add() {
        return $this->edit();
    }

    public function edit($division_id=null) {


        require_capability('codes:writedivisions');
        $this->load->helper('form_template');

        if (!empty($division_id)) { // Editing an existing division
            require_capability('codes:editdivisions');
            $division_data = (array) $this->division_model->get($division_id);
            form_element::$default_data = $division_data;
            $title = "Edit {$division_data['name']} division";
        } else { // Adding a new division
            $title = "Create a new division";
        }

        $this->config->set_item('replacer', array('codes' => array('division|divisions'), 'edit' => $title));

        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'codes/division/edit',
                             'division_id' => $division_id
                             );

        if (!empty($division_id)) {
             $pageDetails['revision_date'] = unix_to_human($division_data['revision_date']);
             $pageDetails['revision_user'] = $this->user_model->get_name($division_data['revision_user_id']);
        }
        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('codes:editdivisions');
        $required_fields = array('name' => 'Name', 'code' => 'Code');

        if ($division_id = (int) $this->input->post('division_id')) {
            $division = $this->division_model->get($division_id);
            $redirect_url = 'codes/division/edit/'.$division_id;
        } else {
            $redirect_url = 'codes/division/add';
            $division_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $this->form_validation->set_rules('code', 'Code', 'trim|max_length[2]|required');
        $success = $this->form_validation->run();
        $action_word = ($division_id) ? 'updated' : 'created';

        // If a code was entered, check whether it is already being used by another supplier
        $code = $this->input->post('code');

        if (!empty($division_id)) {
            $this->db->where('id !=', $division_id);
        }

        if (!empty($code) && $this->division_model->get(array('code' => $code))) {
            add_message("This code ($code) is already used by another division, please try a different one.", 'error');
            $success = false;
        }

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "division $division_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($division_id);
        }

        $division_data = array('name' => $this->input->post('name'),'code' => $this->input->post('code'));

        if (empty($division_id)) {
            if (!($division_id = $this->division_model->add($division_data))) {
                add_message('Could not create this division!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->division_model->edit($division_id, $division_data)) {
                add_message('Could not update this division!', 'error');
                redirect($redirect_url);
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("Division $division_id has been successfully $action_word!", 'success');
        redirect('codes/division/browse');
    }
}
?>
