<?php
/**
 * Contains the Setting Controller class
 * @package controllers
 */

/**
 * Setting Controller class
 * @package controllers
 */
class Setting extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('setting_model');
        $this->config->set_item('replacer', array('settings' => array('index|Settings')));
        $this->config->set_item('exclude', array('index'));

        // Being a global controller, settings doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
    }

    public function index($outputtype='html') {
        require_capability('site:doanything');

        $this->load->library('filter');

        $this->filter->add_filter('combo', 'combo', array('setting_id' => 'ID',
                                                          'name' => 'Name',
                                                          'value' => 'Value'
                                                          ));

        $total_records = $this->setting_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;

        if (!IS_AJAX && $outputtype == 'html') {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($outputtype);

        $table_data = $this->setting_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit);

        if ($outputtype == 'html') {
            $table_data = parent::add_action_column('site', 'setting', $table_data, array('edit', 'delete'));
            $pageDetails = parent::get_ajax_table_page_details('site', 'setting', $table_data['headings'], array('add'));
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {
            unset($table_data['headings']['notes']);

            $pageDetails = parent::get_export_page_details('setting', $table_data);
            $pageDetails['widths'] = array(128, 240, 200, 300, 200, 200, 248, 375, 345);

            parent::output_for_export('site', 'setting', $outputtype, $pageDetails);
        }
    }

    public function add() {
        return $this->edit();
    }

    public function edit($setting_id=null) {

        require_capability('site:doanything');
        $this->load->helper('form_template');

        if (!empty($setting_id)) {
            require_capability('site:doanything');
            $setting_data = $this->setting_model->get($setting_id);
            form_element::$default_data = (array) $setting_data;

            // Set up title bar
            $title = "Edit {$setting_data->name} Setting";
        } else { // adding a new setting
            $title = "Create a new Setting";
        }

        $this->config->set_item('replacer', array('setting' => array('index|Settings'), 'edit' => $title, 'add' => $title));
        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'setting/edit',
                             'setting_id' => $setting_id,
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('site:editsettings');

        $required_fields = array('name' => 'Name',
                                 'value' => 'Value',
                                 );

        if ($setting_id = (int) $this->input->post('setting_id')) {
            $setting = $this->setting_model->get($setting_id);
            $redirect_url = 'setting/edit/'.$setting_id;
        } else {
            $redirect_url = 'setting/add';
            $setting_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();
        $action_word = ($setting_id) ? 'updated' : 'created';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "Setting $setting_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($setting_id);
        }

        $setting_data = array(
                'name' => $this->input->post('name'),
                'value' => $this->input->post('value'),
                );

        if (empty($setting_id)) {
            if (!($setting_id = $this->setting_model->add($setting_data))) {
                add_message('Could not create this setting!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->setting_model->edit($setting_id, $setting_data)) {
                add_message('Could not update this setting!', 'error');
                redirect($redirect_url);
            }
        }

        // By now we should have a setting_id
        $redirect_url = 'setting/edit/'.$setting_id;

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("Setting $setting_id has been successfully $action_word!", 'success');
        redirect($redirect_url);
    }
}
