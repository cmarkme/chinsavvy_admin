<?php
/**
 * Contains the Commodity Controller class
 * @package controllers
 */

/**
 * Commodity Controller Class
 * @package controllers
 */
class Commodity extends MY_Controller {

	function __construct() {
		parent::__construct();
        $this->load->model('exchange/commodity_model');
        $this->config->set_item('replacer', array('exchange' => array('commodity|Commodities')));
        $this->config->set_item('exclude', array('browse'));
    }

    function index() {
        redirect('exchange/commodity/browse');
    }

    function browse($outputtype='html') {

        $this->load->helper('title');
        $this->load->library('filter');

        require_capability('exchange:viewcommodities');

        $total_records = $this->commodity_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX && $outputtype == 'html') {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($outputtype);
        $table_data = $this->commodity_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit);

        if ($outputtype == 'html') {
            $action_icons = array('Edit this commodity' => 'edit',
                                  'Delete this commodity' => 'delete');
            $table_data = parent::add_action_column('exchange', 'commodity', $table_data, $action_icons);
            $pageDetails = parent::get_ajax_table_page_details('exchange', 'commodity', $table_data['headings'], array('add'));
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {
            $pageDetails = parent::get_export_page_details('exchange', $table_data);
            $pageDetails['widths'] = array(128, 200, 375, 300, 200, 300);
            parent::output_for_export('exchange', 'commodity', $outputtype, $pageDetails);
        }
    }

    public function add() {
        return $this->edit();
    }

    public function edit($commodity_id=null) {


        require_capability('exchange:writecommodities');
        $this->load->helper('form_template');

        if (!empty($commodity_id)) { // Editing an existing commodity
            require_capability('exchange:editcommodities');
            $commodity_data = (array) $this->commodity_model->get($commodity_id);
            form_element::$default_data = $commodity_data;
            $title = "Edit {$commodity_data['name']} Commodity";
        } else { // Adding a new commodity
            $title = "Create a new commodity";
        }

        $this->config->set_item('replacer', array('exchange' => array('commodity|Commodities'), 'edit' => $title));

        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'exchange/commodity/edit',
                             'commodity_id' => $commodity_id,
                             'categories' => get_constant_dropdown('EXCHANGE_COMMODITY_CATEGORY_')
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('exchange:editcommodities');
        $required_fields = array('name' => 'Name', 'category' => 'Category');

        if ($commodity_id = (int) $this->input->post('commodity_id')) {
            $commodity = $this->commodity_model->get($commodity_id);
            $redirect_url = 'exchange/commodity/edit/'.$commodity_id;
        } else {
            $redirect_url = 'exchange/commodity/add';
            $commodity_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();
        $action_word = ($commodity_id) ? 'updated' : 'created';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "Commodity $commodity_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($commodity_id);
        }

        $commodity_data = array('name' => $this->input->post('name'),'category' => $this->input->post('category'));

        if (empty($commodity_id)) {
            if (!($commodity_id = $this->commodity_model->add($commodity_data))) {
                add_message('Could not create this commodity!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->commodity_model->edit($commodity_id, $commodity_data)) {
                add_message('Could not update this commodity!', 'error');
                redirect($redirect_url);
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("Commodity $commodity_id has been successfully $action_word!", 'success');
        redirect('exchange/commodity/browse');
    }


}
