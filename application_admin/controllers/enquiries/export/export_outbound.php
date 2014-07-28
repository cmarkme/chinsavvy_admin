<?php
/**
 * Contains the export controller for outbound quotations
 * @package controllers
 */

require_once APPPATH.'core/MY_Export_Controller.php';

/**
 * Export controller for outbound quotations
 * @package controllers
 */
class Export_outbound extends MY_Export_Controller {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Export outbound quotation to PDF and save it to disk.
     * @param int $quotation_id
     */
    public function pdf($quotation_id) {

        $this->load->model('enquiries/outbound_quotation_model');
        $this->load->model('country_model');
        $this->load->helper('date');

        $quotation_data = $this->outbound_quotation_model->get_values($quotation_id);
        $quotation_data['quotation_creation_date'] = unix_to_human($quotation_data['quotation_creation_date']);
        $countryname = ($country = $this->country_model->get($quotation_data['quotation_country_id'])) ? $country->country : null;
        $enquiry_countryname = ($country = $this->country_model->get($quotation_data['address_country_id'])) ? $country->country : null;

        // Replace constant values
        $quotation_data['quotation_currency'] = get_lang_for_constant_value("CURRENCY", $quotation_data["quotation_currency"]);
        $quotation_data['quotation_freight'] = get_lang_for_constant_value("ENQUIRIES_SHIPPING", $quotation_data["quotation_freight"]);
        $quotation_data['quotation_delivery_terms'] = get_lang_for_constant_value("ENQUIRIES_ENQUIRY_DELIVERY", $quotation_data["quotation_delivery_terms"]);
        $quotation_data['quotation_payment_terms'] = get_lang_for_constant_value("ENQUIRIES_OUTBOUND_PAYMENT_TERMS", $quotation_data["quotation_payment_terms"]);
        $quotation_data['quotation_tool_cost_payment_terms'] = get_lang_for_constant_value("ENQUIRIES_TOOL_PAYMENT_TERMS", $quotation_data["quotation_tool_cost_payment_terms"]);

        $this->load->library('pdf', array('header_title' => "Outbound Quotation $quotation_id", 'header_font_size' => 14));
        $this->pdf->SetSubject("Outbound Quotation $quotation_id");

        $company_info = "{$quotation_data['company_name']}
            {$quotation_data['address_address1']}
            {$quotation_data['address_address2']}
            {$quotation_data['address_city']}, {$quotation_data['address_state']} {$quotation_data['address_postcode']},
            $enquiry_countryname";

        if ($quotation_data['quotation_tool_cost'] == 0) {
            $quotation_data['quotation_tool_cost'] = '<strong style="color: red">Please refer to attached document for tool cost quote information.</strong>';
        }
        if ($quotation_data['quotation_price'] == '0.00') {
            $quotation_data['quotation_price'] = '<strong style="color: red">Please refer to attached document for price quote information.</strong>';
        }

        $quotation_info = array('Product' => $quotation_data['enquiry_product_title'],
                                'Description' => $quotation_data['enquiry_product_description'],
                                'Materials' => $quotation_data['enquiry_product_materials'],
                                'Manufacturing Process' => $quotation_data['enquiry_product_man_process'],
                                'Size' => $quotation_data['enquiry_product_size'],
                                'Weight' => $quotation_data['enquiry_product_weight'],
                                'Colour' => $quotation_data['enquiry_product_colour'],
                                'Packaging' => $quotation_data['enquiry_product_packaging'],
                                'Currency' => $quotation_data['quotation_currency'],
                                'Tool Cost' => $quotation_data['quotation_tool_cost'],
                                'Tool Lead Time' => $quotation_data['quotation_tool_lead_time'],
                                'Sample Cost' => $quotation_data['quotation_sample_cost'],
                                'Sample Lead Time' => $quotation_data['quotation_sample_time'],
                                'Product Unit' => $quotation_data['quotation_unit'],
                                'Product Minimum Order' => $quotation_data['quotation_min_qty'],
                                'Product Lead Time' => $quotation_data['quotation_product_lead_time'],
                                'Freight Type' => $quotation_data['quotation_freight'],
                                'Delivery Terms' => $quotation_data['quotation_delivery_terms'],
                                'Delivery Country' => $countryname,
                                'Delivery Point' => $quotation_data['quotation_delivery_point'],
                                'Product Payment Terms' => $quotation_data['quotation_payment_terms'],
                                'Tool Payment Terms' => $quotation_data['quotation_tool_cost_payment_terms'],
                                'Sample Payment Terms' => $quotation_data['quotation_sample_payment_terms'],
                                'Notes' => $quotation_data['quotation_notes'] . "<br /><br />"
                              . "Quotation subject to our Standard Terms of Business<br />Quotation valid 15 days<br />Samples shipped by courier at customer's expense"
                              . "<br /><br />Shipping costs, where applicable, can change from week to week with higher rates between July to October. Adjustments will "
                              . "be advised at time of receipt of order");

        $output = $this->load->view("enquiries/outbound/export_pdf", array('quotation_data' => $quotation_data, 'company_info' => nl2br($company_info), 'quotation_info' => $quotation_info), true);

        $this->pdf->setY(60);
        $this->pdf->writeHTML($output, false, false, false, false, '');

        // Save file to disk for easy attachment to emails
        $directory = $this->config->item('files_path')."enquiries/outbound/$quotation_id/";
        if (!file_exists($directory)) {
            mkdir($directory);
        }

        $save_only = $this->session->flashdata('save_only');
        $this->pdf->output($directory . "outbound_quotation_$quotation_id.pdf", ($save_only) ? 'F' : 'FD');
        if ($save_only) {
            redirect($this->session->flashdata('redirect_url'));
        }
    }

    public function csv($quotation_id) {
        echo "Edit controllers/enquiries/export/export_outbound.php to write the CSV-generating code (quotation_id: $quotation_id)";

    }

    public function xml($quotation_id) {
        echo "Edit controllers/enquiries/export/export_outbound.php to write the XML-generating code (quotation_id: $quotation_id)";

    }

    public function index() {
        echo "Choose pdf, csv or xml";
    }
}
