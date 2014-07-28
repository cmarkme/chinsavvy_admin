<?php
/**
 * Contains the Supplier Controller class
 * @package controllers
 */

/**
 * Supplier Controller class
 * @package controllers
 */
class Supplier extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('codes/supplier_model');
        $this->load->model('company_address_model');
        $this->config->set_item('replacer', array('codes' => array('codes/browse|Supplier Codes')));
        $this->config->set_item('exclude', array('browse'));
    }

    public function index() {
        redirect('codes/supplier/browse');
    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     */
    public function browse($outputtype='html') {
        require_capability('codes:viewsuppliers');


        $this->load->helper('title');
        $this->load->library('filter');

        $this->filter->add_filter('combo', 'combo', array('company_id' => 'ID',
                                                          'company_name' => 'Name',
                                                          'code' => 'Code'));

        $this->db->where(array('role' => COMPANY_ROLE_SUPPLIER, 'company_type' => COMPANY_TYPE_MANUFACTURER));
        $total_records = $this->supplier_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX) {
            $limit = 1;
        }

        $table_data = $this->supplier_model->get_data_for_listing(parent::process_datatable_params(), $this->filter->filters, $limit);

        if ($outputtype == 'html') {
            $table_data = parent::add_action_column('codes', 'supplier', $table_data, array('edit', 'delete'));
            $pageDetails = parent::get_ajax_table_page_details('codes', 'supplier', $table_data['headings'], array('add'));
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {
            $pageDetails = parent::get_export_page_details('supplier', $table_data);
            $pageDetails['widths'] = array(128, 240, 200, 300, 200, 200, 248, 375, 345);

            parent::output_for_export('codes', 'supplier', $outputtype, $pageDetails);
        }
    }

    public function add() {
        return $this->edit();
    }

    public function edit($supplier_id=null) {


        require_capability('codes:writesuppliers');
        $this->load->helper('form_template');
        $this->load->helper('date');

        if (!empty($supplier_id)) { // Editing an existing supplier
            require_capability('codes:editsuppliers');
            $supplier_data = (array) $this->supplier_model->get_values($supplier_id);
            form_element::$default_data = $supplier_data;
            $title = "Editing supplier code for {$supplier_data['company_name']}";
        } else { // Adding a new supplier
            $title = "Create a new supplier code";
        }

        $this->config->set_item('replacer', array('codes' => array('supplier|suppliers'), 'edit' => $title));

        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'codes/supplier/edit',
                             'supplier_id' => $supplier_id,
                             'dropdowns' => $this->get_dropdowns()
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('codes:editsuppliers');
        $required_fields = array('company_name' => 'Name', 'address_billing_country_id' => 'Country', 'address_billing_city' => 'City');

        if ($supplier_id = (int) $this->input->post('supplier_id')) {
            $supplier = $this->supplier_model->get($supplier_id);
            $redirect_url = 'codes/supplier/edit/'.$supplier_id;
        } else {
            $redirect_url = 'codes/supplier/add';
            $supplier_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $this->form_validation->set_rules('company_code', 'Code', 'trim|max_length[3]');

        $success = $this->form_validation->run();
        $action_word = ($supplier_id) ? 'updated' : 'created';

        // If a code was entered, check whether it is already being used by another supplier
        $code = $this->input->post('company_code');

        if (!empty($supplier_id)) {
            $this->db->where('id !=', $supplier_id);
        }

        if (!empty($code) && $this->supplier_model->get(array('role' => COMPANY_ROLE_SUPPLIER, 'code' => $code))) {
            add_message("This code ($code) is already used by another supplier, please try a different one.", 'error');
            $success = false;
        }

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "supplier $supplier_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($supplier_id);
        }

        $supplier_data = array('name' => $this->input->post('company_name'),
                               'url' => $this->input->post('company_url'),
                               'phone' => $this->input->post('company_phone'),
                               'fax' => $this->input->post('company_fax'),
                               'email' => $this->input->post('company_email'),
                               'role' => COMPANY_ROLE_SUPPLIER,
                               'company_type' => COMPANY_TYPE_MANUFACTURER
                               );

        if (!empty($code)) {
            $supplier_data['code'] = $code;
        }

        $address_data = array('country_id' => $this->input->post('address_billing_country_id'),
                              'address1' => $this->input->post('address_billing_address1'),
                              'address2' => $this->input->post('address_billing_address2'),
                              'city' => $this->input->post('address_billing_city'),
                              'province' => $this->input->post('address_billing_province'),
                              'state' => $this->input->post('address_billing_state'),
                              'postcode' => $this->input->post('address_billing_postcode'),
                              'default_address' => true,
                              'type' => COMPANY_ADDRESS_TYPE_BILLING
                              );

        if (empty($supplier_id)) {
            if (!($supplier_id = $this->supplier_model->add($supplier_data))) {
                add_message('Could not create this supplier!', 'error');
                redirect($redirect_url);
            } else { // Create an address
                $address_data['company_id'] = $supplier_id;
                $this->company_address_model->add($address_data);
            }

        } else {
            if (!$this->supplier_model->edit($supplier_id, $supplier_data)) {
                add_message('Could not update this supplier!', 'error');
                redirect($redirect_url);
            } else { // Update the address
                $this->db->select('id');
                $existing_address = $this->company_address_model->get(array('type' => COMPANY_ADDRESS_TYPE_BILLING, 'company_id' => $supplier_id, 'default_address' => 1), true);
                $this->company_address_model->edit($existing_address->id, $address_data);
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("Supplier $supplier_id has been successfully $action_word!", 'success');
        redirect('codes/supplier/browse');
    }

    private function get_dropdowns() {

        $this->load->model('country_model');
        $this->load->helper('dropdowns');
        return array('countries' => $this->country_model->get_dropdown(), 'salutations' => get_salutations());
    }
}
?>
