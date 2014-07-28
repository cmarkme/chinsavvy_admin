<?php
/**
 * Contains the Part Controller class
 * @package controllers
 */

/**
 * Part Controller class
 * @package controllers
 */
class Part extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('codes/part_model');
        $this->load->model('codes/codes_project_model');
        $this->load->model('company_model');
        $this->load->model('codes/division_model');
        $this->config->set_item('replacer', array('codes' => array('codes/browse|Product Codes')));
        $this->config->set_item('exclude', array('browse'));
    }

    function index() {
        return $this->browse();
    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     */
    function browse($outputtype='html') {
        require_capability('codes:viewparts');


        $this->load->helper('title');
        $this->load->library('filter');

        $this->filter->add_filter('combo', 'combo', array('codes_parts_id' => 'ID',
                                                          'product_number' => 'Product Number',
                                                          'part_number' => 'Part Number',
                                                          'company_code' => 'Customer Code',
                                                          'codes_parts_name' => 'Name',
                                                          'codes_parts_description' => 'Description',
                                                          'codes_parts__2d_data' => '2D Data',
                                                          'codes_parts__2d_data_rev' => '2D Data Rev',
                                                          'codes_parts__3d_data' => '3D Data',
                                                          'codes_parts__3d_data_rev' => '3D Data Rev',
                                                          'codes_parts_other_data' => 'Other Data',
                                                          'codes_parts_other_data_date' => 'Other Data Date',
                                                          'codes_parts_due_date' => 'Due Date',
                                                          'codes_parts_creation_date' => 'Creation Date'));
        $this->filter->add_filter('checkbox', 1, 'Completed', 'completedstatus', 'codes_parts_completed');

        $total_records = $this->part_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX) {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($outputtype, array('pdf'));

        if ($outputtype != 'html') {
            $limit = $datatable_params['perpage'];
        }

        $table_data = $this->part_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit);

        if ($outputtype == 'html') {
            $table_data = parent::add_action_column('codes', 'part', $table_data, array('edit', 'delete'));
            $pageDetails = parent::get_ajax_table_page_details('codes', 'part', $table_data['headings'], array('add'));

            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {

            $pageDetails = parent::get_export_page_details('part', $table_data);
            $pageDetails['widths'] = array(125, 240, 155, 300, 200, 200, 248, 365, 325);

            parent::output_for_export('codes', 'part', $outputtype, $pageDetails);
        }
    }

    public function add() {
        return $this->edit();
    }

    public function edit($part_id=null, $project_id=null) {


        require_capability('codes:writeparts');
        $this->load->helper('form_template');
        $this->load->helper('date');

        if (empty($part_id)) { // Adding a new part
            require_capability('codes:writeparts');
            $title = "Create a new part";
            $this->db->limit(1);
            $this->db->order_by('creation_date DESC');

            if (empty($project_id)) {
                $project = $this->codes_project_model->get(array(), true);
                form_element::$default_data['project_id'] = $project_id = $project->id;
            } else {
                $project = $this->codes_project_model->get($project_id);
                form_element::$default_data['project_id'] = $project_id;
            }

            // Prefill fields from project data
            form_element::$default_data += array('name' => $project->name,
                                                 'description' => $project->description,
                                                 'customer_po_date' => unix_to_human($project->customer_po_date),
                                                 'customer_po_number' => $project->customer_po_number,
                                                 'due_date' => unix_to_human($project->due_date),
                                                 'status_text' => $project->status_text,
                                                 'status_description' => $project->status_description);
        } else { // Editing an existing part
            require_capability('codes:editparts');
            $part_data = (array) $this->part_model->get($part_id);
            $part_data['creation_date'] = unix_to_human($part_data['creation_date']);
            $part_data['customer_po_date'] = unix_to_human($part_data['customer_po_date']);
            $part_data['due_date'] = unix_to_human($part_data['due_date']);
            if (empty($project_id)) {
                $project_id = $part_data['project_id'];
            }
            form_element::$default_data = $part_data;
            $title = "Edit {$part_data['name']} part";
        }

        $this->config->set_item('replacer', array('codes' => array('part|parts'), 'edit' => $title));

        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'codes/part/edit',
                             'part_id' => $part_id,
                             'dropdowns' => $this->get_dropdowns(),
                             'project_id' => $project_id,
                             'jstoload' => array('application/codes/part_edit')
                             );

        if (empty($part_id)) {
            form_element::$default_data['number'] = $pageDetails['product_number'] = $this->part_model->generate_number();
        } else {
            $pageDetails['status_update_date'] = unix_to_human($part_data['status_date']);
            $pageDetails['revision_date'] = unix_to_human($part_data['revision_date']);
            $pageDetails['revision_user'] = $this->user_model->get_name($part_data['revision_user_id']);
            $pageDetails['product_number'] = $part_data['number'];
        }

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        $this->load->helper('date');

        require_capability('codes:editparts');
        $required_fields = array('name' => 'Name', 'description' => 'Description');
        $project_id = $this->input->post('project_id');

        if ($part_id = (int) $this->input->post('id')) {
            $part = $this->part_model->get($part_id);
            $part_number = $part->number;
            $project_id = $part->project_id;
            $redirect_url = 'codes/part/edit/'.$part_id;
        } else {
            $redirect_url = 'codes/part/add';
            $part_number = $this->part_model->generate_number($project_id);
            $part_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();
        $action_word = ($part_id) ? 'updated' : 'created';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "part $part_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($part_id);
        }

        $part_data = array('project_id' => $project_id,
                           'name' => $this->input->post('name'),
                           'name_ch' => $this->input->post('name_ch'),
                           'number' => $part_number,
                           '_2d_data' => $this->input->post('_2d_data'),
                           '_2d_data_rev' => $this->input->post('_2d_data_rev'),
                           '_3d_data' => $this->input->post('_3d_data'),
                           '_3d_data_rev' => $this->input->post('_3d_data_rev'),
                           'other_data' => $this->input->post('other_data'),
                           'other_data_date' => human_to_unix($this->input->post('other_data_date')),
                           'customer_po_number' => $this->input->post('customer_po_number'),
                           'customer_po_date' => human_to_unix($this->input->post('customer_po_date')),
                           'description' => $this->input->post('description'),
                           'revision_user_id' => $this->session->userdata('user_id'),
                           'due_date' => human_to_unix($this->input->post('due_date')),
                           'revision_date' => mktime(),
                           'status_text' => $this->input->post('status_text'),
                           'status_description' => $this->input->post('status_description'),
                           'completed' => $this->input->post('completed'),
                           );

        if (empty($part_id)) {
            unset($part_data['creation_date']);
            if (!($part_id = $this->part_model->add($part_data))) {
                add_message('Could not create this part!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->part_model->edit($part_id, $part_data)) {
                add_message('Could not update this part!', 'error');
                redirect($redirect_url);
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("The part $part_id has been successfully $action_word!", 'success');
        redirect('codes/part/browse');
    }

    /**
     * Prepares associative arrays of projects and status codes
     * @return array
     */
    private function get_dropdowns() {

        $this->load->helper('dropdowns');

        $dropdowns = array(
            'projects' => $this->codes_project_model->get_dropdown(),
            'status_codes' => unserialize(CODES_STATUS_CODES)
            );

       return $dropdowns;
    }
}
?>
