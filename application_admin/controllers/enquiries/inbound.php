<?php
/**
 * Contains the Inbound quotation Controller class
 * @package controllers
 */

/**
 * Inbound quotation Controller class
 * @package controllers
 */
class Inbound extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('enquiries/inbound_quotation_model');
    }

    function index($quotation_id=null) {
        $form_data = array();

        if (!empty($quotation_id)) {
            $quotation = $this->inbound_quotation_model->get($quotation_id);
        }

        if (!is_null($this->input->post('user_id'))) {

        }

        $pageDetails = array(
            'title' => 'Edit Inbound Quotation',
            'csstoload' => array(),
            'jstoload' => array('jquery/jquery', 'jquery/pause', 'jquery/jquery.json', 'jquery/jquery.urlparser'),
            'content_view' => 'enquiries/inbound/inbound');
        $this->load->view('template/default', $pageDetails);
    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     */
    function browse() {
        require_capability('chinasavvy:viewinbound');


        $this->load->helper('title');
        $this->load->library('filter');

        $this->filter->add_filter('combo', 'combo', array('enquiries_inbound_quotations.id' => 'Ref',
                                                          'enquiries_inbound_quotations.enquiry_id' => 'Enquiry ID',
                                                          'companies.name' => 'Enquirer',
                                                          'enquiries_enquiry_products.title' => 'Product',
                                                          'staff' => 'Staff'));

        $total_records = $this->inbound_quotation_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX) {
            $limit = 1;
        }

        $table_data = $this->inbound_quotation_model->get_data_for_listing(parent::process_datatable_params(), $this->filter->filters, $limit);
        $table_data = parent::add_action_column('enquiries', 'inbound', $table_data);
        $pageDetails = parent::get_ajax_table_page_details('enquiries', 'inbound', $table_data['headings']);

        parent::output_ajax_table($pageDetails, $table_data, $total_records);

    }
}
?>
