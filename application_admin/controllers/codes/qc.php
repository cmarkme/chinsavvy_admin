<?php
/**
 * Contains the Qc Controller class
 * @package controllers
 */

/**
 * Qc Controller class
 * @package controllers
 */
class Qc extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('codes' => array('codes/browse|QC Codes')));
        $this->config->set_item('exclude', array('browse'));
        $this->load->library('form_validation');
        $this->load->model('codes/qc_model');
    }

    public function index() {
        redirect('codes/qc/browse');
    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     */
    public function browse($outputtype='html') {
        require_capability('codes:viewqc');


        $this->load->helper('title');
        $this->load->library('filter');

        $this->filter->add_filter('combo', 'combo', array('codes_qc.id' => 'ID',
                                                          'codes_qc.code' => 'Code',
                                                          'codes_qc.description' => 'Description',
                                                          'codes_qc.creation_date' => 'Date'));

        $total_records = $this->qc_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX) {
            $limit = 1;
        }

        $table_data = $this->qc_model->get_data_for_listing(parent::process_datatable_params(), $this->filter->filters, $limit);

        if ($outputtype == 'html') {
            $table_data = parent::add_action_column('codes', 'qc', $table_data, array('edit', 'delete'));
            $pageDetails = parent::get_ajax_table_page_details('codes', 'qc', $table_data['headings'], array('add'));
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {
            $pageDetails = parent::get_export_page_details('qc', $table_data);
            $pageDetails['widths'] = array(128, 240, 200, 300, 200, 200, 248, 375, 345);

            parent::output_for_export('codes', 'qc', $outputtype, $pageDetails);
        }
    }

    public function add() {
        return $this->edit();
    }

    public function edit($qc_id=null) {


        require_capability('codes:writeqc');
        $this->load->helper('form_template');
        $this->load->helper('date');

        if (!empty($qc_id)) { // Editing an existing qc
            require_capability('codes:editqc');
            $qc_data = (array) $this->qc_model->get($qc_id);
            form_element::$default_data = $qc_data;
            $title = "Edit {$qc_data['code']} QC code";
        } else { // Adding a new qc
            $title = "Create a new qc";
        }

        $this->config->set_item('replacer', array('codes' => array('QC|qc'), 'edit' => $title));

        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'codes/qc/edit',
                             'qc_id' => $qc_id
                             );

        if (!empty($qc_id)) {
             $pageDetails['revision_date'] = unix_to_human($qc_data['revision_date']);
             $pageDetails['revision_user'] = $this->user_model->get_name($qc_data['revision_user_id']);
        }

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('codes:editqc');
        $required_fields = array('description' => 'Description', 'code' => 'Code');

        if ($qc_id = (int) $this->input->post('qc_id')) {
            $qc = $this->qc_model->get($qc_id);
            $redirect_url = 'codes/qc/edit/'.$qc_id;
        } else {
            $redirect_url = 'codes/qc/add';
            $qc_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $this->form_validation->set_rules('code', 'Code', 'trim|is_natural_no_zero|max_length[1]|required');

        $success = $this->form_validation->run();
        $action_word = ($qc_id) ? 'updated' : 'created';

        // If a code was entered, check whether it is already being used by another supplier
        $code = $this->input->post('code');

        if (!empty($qc_id)) {
            $this->db->where('id !=', $qc_id);
        }

        if (!empty($code) && $this->qc_model->get(array('code' => $code))) {
            add_message("This code ($code) is already used by another QC code, please try a different one.", 'error');
            $success = false;
        }

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "QC $qc_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($qc_id);
        }

        $qc_data = array('description' => $this->input->post('description'),'code' => $this->input->post('code'));

        if (empty($qc_id)) {
            if (!($qc_id = $this->qc_model->add($qc_data))) {
                add_message('Could not create this qc!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->qc_model->edit($qc_id, $qc_data)) {
                add_message('Could not update this qc!', 'error');
                redirect($redirect_url);
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("Qc $qc_id has been successfully $action_word!", 'success');
        redirect('codes/qc/browse');
    }
}
?>
