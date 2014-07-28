<?php
/**
 * Contains the Outbound quotation Controller class
 * @package controllers
 */

/**
 * Outbound quotation Controller class
 * @package controllers
 */
class Outbound extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('enquiries/outbound_quotation_model');
        $this->config->set_item('replacer', array('enquiries' => array('outbound|Outbound Quotations')));
        $this->config->set_item('exclude', array('browse'));
    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     */
    function browse($outputtype='html') {
        require_capability('enquiries:viewoutbound');


        $this->load->helper('title');
        $this->load->library('filter');

        $this->filter->add_filter('combo', 'combo', array('enquiries_outbound_quotations_id' => 'Ref',
                                                          'enquiries_outbound_quotations_enquiry_id' => 'Enquiry ID',
                                                          'company' => 'Enquirer',
                                                          'product' => 'Product'));
        $staff_list = array(null => '-- Select One --') + $this->outbound_quotation_model->get_staff_list();
        $this->filter->add_filter('dropdown', $staff_list, 'Staff', 'staff_id', null);
        $total_records = $this->outbound_quotation_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX && $outputtype == 'html') {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($outputtype, array('pdf'));

        if ($outputtype != 'html') {
            $limit = $datatable_params['perpage'];
        }

        $table_data = $this->outbound_quotation_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit);

        if ($outputtype == 'html') {
            $table_data = parent::add_action_column('enquiries', 'outbound', $table_data);
            $pageDetails = parent::get_ajax_table_page_details('enquiries', 'outbound', $table_data['headings']);
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {
            $pageDetails = parent::get_export_page_details('outbound', $table_data);
            $pageDetails['widths'] = array(128, 200, 525, 630, 200, 400);
            parent::output_for_export('enquiries', 'outbound', $outputtype, $pageDetails);
        }
    }

    /**
     * Editing an outbound quotation
     * @param int $quotation_id
     */
    public function edit($quotation_id) {


        // Breadcrumb
        $this->config->set_item('replacer', array('enquiries' => array('outbound/browse|Outbound Quotations'), 'edit' => 'Edit quotation ' . $quotation_id));

        $quotation_id = (int) $quotation_id;
        if (!has_capability('enquiries:editoutbound')) {
            return null;
        }

        $this->load->model('enquiries/enquiry_model');
        $this->load->model('country_model');
        $this->load->helper('form_template');
        $this->load->helper('date');

        $quotation_data = $this->outbound_quotation_model->get_values($quotation_id);

        $quotation_data['address_country_name'] = (!empty($quotation_data['address_country_id'])) ? $this->country_model->get_name($quotation_data['address_country_id']) : '';
        $quotation_data['quotation_creation_date'] = unix_to_human($quotation_data['quotation_creation_date']);

        form_element::$default_data = $quotation_data;

        // Set up title bar
        $title_options = array('title' => "Edit outbound quotation $quotation_id",
                               'help' => "Edit outbound quotation $quotation_id",
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array(
            'title' => 'Edit Outbound Quotation',
            'section_title' => get_title($title_options),
            'csstoload' => array(),
            'jstoloadinfooter' => array('jquery/pause', 'jquery/jquery.json', 'jquery/jquery.urlparser', 'application/enquiries/outbound_edit'),
            'content_view' => 'enquiries/outbound/edit',
            'quotation_data' => $quotation_data,
            'dropdowns' => $this->get_dropdowns());
        $this->load->view('template/default', $pageDetails);

    }

    function process_edit() {

        $this->load->model('enquiries/enquiry_model');
        $this->load->model('enquiries/enquiry_product_model');

        $quotation_id = (int) $this->input->post('quotation_id');
        $redirect_url = 'enquiries/outbound/edit/'.$quotation_id;
        $failure_redirect = $redirect_url;

        if ($this->input->post('resend')) {
            $redirect_url = 'enquiries/outbound_send/show/'.$quotation_id;
        }

        $this->form_validation->set_rules('quotation_min_qty', 'Minimum Order Qty', 'trim|required');
        $this->form_validation->set_rules('quotation_freight', 'Freight Type', 'required');
        $this->form_validation->set_rules('quotation_delivery_terms', 'Delivery Terms', 'required');
        $this->form_validation->set_rules('quotation_country_id', 'Delivery Country', 'required');
        $this->form_validation->set_rules('quotation_delivery_point', 'Delivery Point', 'trim|required');

        $success = $this->form_validation->run();

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "Outbound Quotation $quotation_id has been successfully updated!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($quotation_id);
        }

        // Process the quotation data
        $quotation_data = array('product_lead_time' => $this->input->post('quotation_product_lead_time'),
                                'tool_lead_time' => $this->input->post('quotation_product_lead_time'),
                                'tool_cost' => $this->input->post('quotation_tool_cost'),
                                'notes' => $this->input->post('quotation_notes'),
                                'price' => $this->input->post('quotation_price'),
                                'unit' => $this->input->post('quotation_unit'),
                                'currency' => $this->input->post('quotation_currency'),
                                'min_qty' => $this->input->post('quotation_min_qty'),
                                'freight' => $this->input->post('quotation_freight'),
                                'delivery_terms' => $this->input->post('quotation_delivery_terms'),
                                'delivery_point' => $this->input->post('quotation_delivery_point'),
                                'country_id' => $this->input->post('quotation_country_id'),
                                'payment_terms' => $this->input->post('quotation_payment_terms'),
                                'tool_cost_payment_terms' => $this->input->post('quotation_tool_cost_payment_terms'),
                                'sample_cost' => $this->input->post('quotation_sample_cost'),
                                'sample_time' => $this->input->post('quotation_sample_time'),
                                'sample_payment_terms' => $this->input->post('quotation_sample_payment_terms'),
                                'staff_id' => $this->input->post('quotation_staff_id')
                        );
        if (!$this->outbound_quotation_model->edit($quotation_id, $quotation_data)) {
            add_message('Could not update this quotation!', 'error');
            redirect($failure_redirect);
        }

        $original_quotation = $this->outbound_quotation_model->get($quotation_id);

        $enquiry = $this->enquiry_model->get($original_quotation->enquiry_id);

        if (!is_null($original_quotation->product_id) && $enquiry->enquiry_product_id != $original_quotation->product_id) {
            $product_id = $original_quotation->product_id;
        } else {
            $product_id = $enquiry->enquiry_product_id;
        }

        // Process product data
        $product_data = array('title' => $this->input->post('enquiry_product_title'),
                              'description' => $this->input->post('enquiry_product_description'),
                              'materials' => $this->input->post('enquiry_product_materials'),
                              'man_process' => $this->input->post('enquiry_product_man_process'),
                              'size' => $this->input->post('enquiry_product_size'),
                              'weight' => $this->input->post('enquiry_product_weight'),
                              'colour' => $this->input->post('enquiry_product_colour'),
                              'packaging' => $this->input->post('enquiry_product_packaging'));

        if (!$this->enquiry_product_model->edit($product_id, $product_data)) {
            add_message('An error occurred, preventing this quotation\'s product from being updated.', 'error');
            redirect($failure_redirect);
        }

        $this->outbound_quotation_model->edit($quotation_id, array('product_id' => $product_id));

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("Quotation $quotation_id has been successfully updated!", 'success');
        redirect($redirect_url);
    }

    public function add($enquiry_id=false) {
        require_capability('enquiries:writeoutbound');

        $this->load->helper('form_template');
        $this->load->helper('date');
        $this->load->helper('dropdowns');
        $this->load->model('country_model');
        $this->load->model('enquiries/enquiry_model');

        // An optional enquiry_id can be passed to this page to pre-fill company, enquirer and product details
        if ($enquiry_id) {
            $enquirydata = $this->enquiry_model->get_values($enquiry_id);
            foreach ($enquirydata as $key => $val) {
                $this->form_validation->set_rules($key);
                if (empty($_POST[$key])) {
                    $this->form_validation->override_field_data($key, $val);
                } else {
                    $this->form_validation->override_field_data($key, $_POST[$key]);
                }
            }
        }

        // Set up title bar
        $title_options = array('title' => "New Outbound Quotation",
                               'help' => "New Outbound Quotation",
                               'expand' => 'page',
                               'icons' => array());

        $dropdowns = $this->get_dropdowns();
        $dropdowns['enquiries'] = $this->enquiry_model->get_dropdown();

        form_element::$default_data = array('enquiry_prefill' => $enquiry_id, 'enquiry_id' => $enquiry_id);

        $pageDetails = array('title' => 'New Outbound Quotation',
                             'section_title' => get_title($title_options),
                             'content_view' => 'enquiries/outbound/add',
                             'dropdowns' => $dropdowns,
                             'next_id' => $this->outbound_quotation_model->get_next_id()
                             );
        $this->load->view('template/default', $pageDetails);
    }

    public function process_add() {
        require_capability('enquiries:writeoutbound');

        $this->load->model('enquiries/enquiry_product_model');
        $this->load->model('enquiries/enquiry_model');

        $enquiry_id = $this->input->post('enquiry_prefill');

        // An optional enquiry_id can be passed to this page to pre-fill company and enquirer details
        if ($enquiry_id) {
            $enquirydata = $this->enquiry_model->get_values($enquiry_id);
            foreach ($enquirydata as $key => $val) {
                $this->form_validation->set_rules($key);
                if (empty($_POST[$key])) {
                    $this->form_validation->override_field_data($key, $val);
                }
            }
        }

        // Set default value for submitting staff
        $this->form_validation->override_field_data('quotation_staff_id', $this->session->userdata('user_id'));

        foreach ($_POST as $key => $val) {
            $this->form_validation->set_rules($key);
            if (empty($_POST[$key])) {
                $this->form_validation->override_field_data($key, $val);
            }
        }

        $failure_url = 'enquiries/outbound/add/'.$enquiry_id;

        $required_fields = array('quotation_min_qty' => 'Minimum Order Qty',
                                 'quotation_freight' => 'Freight Type',
                                 'quotation_delivery_terms' => 'Delivery Terms',
                                 'quotation_country_id' => 'Delivery Country',
                                 'quotation_delivery_point' => 'Delivery Point');

        foreach ($required_fields as $name => $label) {
            $this->form_validation->set_rules($name, $label, 'trim|required');
        }

        $success = $this->form_validation->run();

        if ($success) {
            if (!($quotation_id = $this->outbound_quotation_model->add_from_post('quotation_'))) {
                add_message('Could not record the Quotation data!', 'error');
                return $this->add($enquiry_id);
            } else { // Add the enquiry id
                $this->outbound_quotation_model->edit($quotation_id, array('enquiry_id' => $enquiry_id));
            }
        } else {
            return $this->add($enquiry_id);
        }

        // Update enquiry status
        $this->enquiry_model->edit($enquiry_id, array('status' => ENQUIRIES_ENQUIRY_STATUS_STARTED));

        // Create a new enquiry product based off the original
        if ($product_id = $this->enquiry_product_model->add_from_post('enquiry_product_')) {
            $this->outbound_quotation_model->edit($quotation_id, array('product_id' => $product_id));
            add_message('The quotation was successfully recorded!');
            redirect('enquiries/outbound_send/show/'.$quotation_id);
        } else {
            add_message('Product info could not be recorded!', 'error');
            return $this->add($enquiry_id);
        }
    }

    private function get_dropdowns() {

        $this->load->model('country_model');

        $staff_users = $this->user_model->get_users_by_capability('enquiries:editoutbound');
        $staff_list = array();
        foreach ($staff_users as $staff) {
            $staff_list[$staff->id] = "$staff->surname $staff->first_name";
        }

        $dropdowns = array(
            'tool_payment_terms' => get_constant_dropdown('ENQUIRIES_TOOL_PAYMENT_TERMS'),
            'countries' => $this->country_model->get_dropdown(),
            'shipping_methods' => get_constant_dropdown('ENQUIRIES_SHIPPING'),
            'delivery_terms' => get_constant_dropdown('ENQUIRIES_ENQUIRY_DELIVERY'),
            'currencies' => get_constant_dropdown('CURRENCY'),
            'payment_terms' => get_constant_dropdown('ENQUIRIES_OUTBOUND_PAYMENT_TERMS'),
            'staff_list' => $staff_list
        );

        return $dropdowns;
    }
}
