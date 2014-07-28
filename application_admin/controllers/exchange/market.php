<?php
/**
 * Contains the Market Controller class
 * @package controllers
 */

/**
 * Market Controller Class
 * @package controllers
 */
class Market extends MY_Controller {

	function __construct() {
		parent::__construct();
        $this->load->model('exchange/market_model');
        $this->config->set_item('replacer', array('exchange' => array('market|Exchange Markets')));
        $this->config->set_item('exclude', array('browse'));
    }

    function index() {
        redirect('exchange/market/browse');
    }

    function browse($outputtype='html') {

        $this->load->helper('title');
        $this->load->library('filter');

        require_capability('exchange:viewmarkets');

        $total_records = $this->market_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX && $outputtype == 'html') {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($outputtype);
        $table_data = $this->market_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit);

        // Change commodity array to a html list
        foreach ($table_data['rows'] as $key => $row) {
            if (!empty($row[3])) {
                $list = '<ul>';
                foreach ($row[3] as $commodity_id => $commodity_name) {
                    $list .= '<li>'.$commodity_name.'</li>';
                }
                $list .= '</ul>';
                $table_data['rows'][$key][3] = $list;
            }
        }

        if ($outputtype == 'html') {
            $action_icons = array('Edit this market' => 'edit',
                                  'Delete this market' => 'delete');
            $table_data = parent::add_action_column('exchange', 'market', $table_data, $action_icons);
            $pageDetails = parent::get_ajax_table_page_details('exchange', 'market', $table_data['headings'], array('add'));
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {
            $pageDetails = parent::get_export_page_details('exchange', $table_data);
            $pageDetails['widths'] = array(128, 200, 375, 300, 200, 300);
            parent::output_for_export('exchange', 'market', $outputtype, $pageDetails);
        }
    }

    public function add() {
        return $this->edit();
    }

    public function edit($market_id=null) {


        require_capability('exchange:writemarkets');
        $this->load->helper('form_template');
        $this->load->model('exchange/commodity_model');

        $commodities = $this->commodity_model->get_dropdown('name', false, false, 'category', 'EXCHANGE_COMMODITY_CATEGORY_');
        $assigned_commodities = array();

        if (!empty($market_id)) { // Editing an existing market
            require_capability('exchange:editmarkets');
            $market_data = (array) $this->market_model->get($market_id);
            $market_data['commodities[]'] = $this->market_model->get_assigned_commodities($market_id);
            form_element::$default_data = $market_data;
            $title = "Edit {$market_data['name']} Market";
        } else { // Adding a new market
            $title = "Create a new market";
        }

        $this->config->set_item('replacer', array('exchange' => array('market|Exchange Markets'), 'edit' => $title, 'add' => $title));

        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'exchange/market/edit',
                             'market_id' => $market_id,
                             'currencies' => get_constant_dropdown('CURRENCY_'),
                             'commodities' => $commodities
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('exchange:editmarkets');
        $required_fields = array('name' => 'Name', 'currency_id' => 'Currency');

        if ($market_id = (int) $this->input->post('market_id')) {
            $market = $this->market_model->get($market_id);
            $redirect_url = 'exchange/market/edit/'.$market_id;
        } else {
            $redirect_url = 'exchange/market/add';
            $market_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();
        $action_word = ($market_id) ? 'updated' : 'created';

        $market_data = array('name' => $this->input->post('name'),'currency_id' => $this->input->post('currency_id'));

        if (empty($market_id)) {
            if (!($market_id = $this->market_model->add($market_data))) {
                add_message('Could not create this market!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->market_model->edit($market_id, $market_data)) {
                add_message('Could not update this market!', 'error');
                redirect($redirect_url);
            }
        }

        // Update commodities
        $commodities = $this->input->post('commodities');
        $this->db->delete('exchange_market_commodities', array('market_id' => $market_id));
        foreach ($commodities as $commodity_id) {
            $this->db->insert('exchange_market_commodities', array('market_id' => $market_id, 'commodity_id' => $commodity_id));
        }

        add_message("Market $market_id has been successfully $action_word!", 'success');
        redirect('exchange/market/browse');
    }


}
