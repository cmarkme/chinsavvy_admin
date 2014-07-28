<?php
/**
 * Contains the (codes) Project Controller class
 * @package controllers
 */

/**
 * (codes) Project Controller class
 * @package controllers
 */
class Project extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('codes/codes_project_model');
        $this->load->model('company_model');
        $this->load->model('codes/division_model');
        $this->config->set_item('replacer', array('codes' => array('codes/browse|Project Codes')));
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
        require_capability('codes:viewprojects');


        $this->load->helper('title');
        $this->load->library('filter');

        $this->filter->add_filter('combo', 'combo', array('codes_projects_id' => 'ID',
                                                          // 'project_number' => 'Project Number', // Need to support concatenated fields before enabling sort or filter
                                                          'company_code' => 'Customer Code',
                                                          'codes_projects_name' => 'Name',
                                                          'codes_projects_description' => 'Description',
                                                          'codes_projects_due_date' => 'Due Date',
                                                          'codes_projects_creation_date' => 'Creation Date',
                                                          'codes_projects_completed' => 'Completed'));
        $this->filter->add_filter('checkbox', 1, 'Completed', 'completedstatus', 'codes_projects_completed');

        $total_records = $this->codes_project_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX) {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($outputtype, array('pdf'));

        if ($outputtype != 'html') {
            $limit = $datatable_params['perpage'];
        }

        $table_data = $this->codes_project_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit);

        if ($outputtype == 'html') {
            $table_data = parent::add_action_column('codes', 'project', $table_data, array('edit', 'duplicate', 'delete'));
            $pageDetails = parent::get_ajax_table_page_details('codes', 'project', $table_data['headings'], array('add'));

            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {

            $pageDetails = parent::get_export_page_details('project', $table_data);
            $pageDetails['widths'] = array(125, 240, 155, 300, 200, 200, 248, 365, 325);

            parent::output_for_export('codes', 'project', $outputtype, $pageDetails);
        }
    }

    public function add() {
        return $this->edit();
    }

    public function edit($project_id=null) {
        require_capability('codes:writeprojects');
        $this->load->helper('form_template');
        $this->load->helper('date');

        if (!empty($project_id)) { // Editing an existing project
            require_capability('codes:editprojects');
            $project_data = (array) $this->codes_project_model->get($project_id);
            $project_data['creation_date'] = unix_to_human($project_data['creation_date']);
            $project_data['customer_po_date'] = unix_to_human($project_data['customer_po_date']);
            $project_data['due_date'] = unix_to_human($project_data['due_date']);
            form_element::$default_data = $project_data;
            $title = "Edit {$project_data['name']} project";
        } else { // Adding a new project
            require_capability('codes:writeprojects');
            $title = "Create a new project";
        }

        $this->config->set_item('replacer', array('codes' => array('project|projects'), 'edit' => $title));

        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'codes/project/edit',
                             'project_id' => $project_id,
                             'dropdowns' => $this->get_dropdowns(),
                             'jstoload' => array('application/codes/project_edit')
                             );

        if (empty($project_id)) {
            form_element::$default_data['number'] = $this->codes_project_model->generate_number();
        } else {
            $pageDetails['status_update_date'] = unix_to_human($project_data['status_date']);
            $pageDetails['revision_date'] = unix_to_human($project_data['revision_date']);
            $pageDetails['revision_user'] = $this->user_model->get_name($project_data['revision_user_id']);
            $pageDetails['project_number'] = $project_data['number'];
        }

        $this->load->view('template/default', $pageDetails);
    }

    public function duplicate($project_id) {

        if ($result = $this->codes_project_model->duplicate($project_id)) {
            add_message("The project $project_id has been successfully duplicated", 'success');
        } else {
            add_message("The project $project_id could not be duplicated", 'error');
        }
        redirect('/codes/project/browse');
    }

    public function process_edit() {

        $this->load->helper('date');

        require_capability('codes:editprojects');
        $required_fields = array('company_id' => 'Company Name', 'division_id' => 'Division', 'name' => 'Name', 'description' => 'Description');

        if ($project_id = (int) $this->input->post('id')) {
            $project = $this->codes_project_model->get($project_id);
            $redirect_url = 'codes/project/edit/'.$project_id;
        } else {
            $redirect_url = 'codes/project/add';
            $project_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();
        $action_word = ($project_id) ? 'updated' : 'created';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "project $project_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($project_id);
        }

        $project_data = array('name' => $this->input->post('name'),
                              'company_id' => $this->input->post('company_id'),
                              'division_id' => $this->input->post('division_id'),
                              'creation_date' => human_to_unix($this->input->post('creation_date')),
                              'customer_project_number' => $this->input->post('customer_project_number'),
                              'customer_po_number' => $this->input->post('customer_po_number'),
                              'customer_po_date' => human_to_unix($this->input->post('customer_po_date')),
                              'description' => $this->input->post('description'),
                              'due_date' => human_to_unix($this->input->post('due_date')),
                              'status_text' => $this->input->post('status_text'),
                              'status_description' => $this->input->post('status_description'),
                              'completed' => $this->input->post('completed'),
                              'revision_date' => mktime(),
                              'revision_user_id' => $this->session->userdata('user_id')
                              );

        if (empty($project_id)) {
            $project_data['number'] = $this->input->post('number');

            if (!($project_id = $this->codes_project_model->add($project_data))) {
                add_message('Could not create this project!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->codes_project_model->edit($project_id, $project_data)) {
                add_message('Could not update this project!', 'error');
                redirect($redirect_url);
            }

            // If project was flagged as "completed", cascade the status to associated parts
            $original_project = $this->codes_project_model->get($project_id);
            if (!$original_project->completed && $project_data['completed']) {
                $parts = $this->part_model->get(array('project_id' => $project_id));
                if (!empty($parts)) {
                    foreach ($parts as $part) {
                        $this->part_model->edit($part->id, array('completed' => true));
                    }
                }
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("The project $project_id has been successfully $action_word!", 'success');
        redirect('codes/project/browse');
    }

    /**
     * Prepares associative arrays of companies (code customers) and divisions, as well as status preset names
     * @return array
     */
    private function get_dropdowns() {

        $this->load->helper('dropdowns');

        $dropdowns = array(
            'companies' => $this->company_model->get_customer_list(),
            'divisions' => $this->division_model->get_dropdown(),
            'status_codes' => unserialize(CODES_STATUS_CODES)
            );

       return $dropdowns;
    }

    /**
     * Overriding the delete function of MY_Controller, because the model name is non-standard.
     */
    public function delete($project_id) {
        return parent::delete($project_id, 'codes_project');
    }
}
?>
