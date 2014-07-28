<?php
/**
 * Contains the export controller for enquiries
 * @package controllers
 */

require_once APPPATH.'core/MY_Export_Controller.php';

/**
 * Export controller for enquiries
 * @package controllers
 */
class Export_enquiry extends MY_Export_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function pdf($enquiry_id) {

        $this->load->model('enquiries/enquiry_model');
        $this->load->model('country_model');
        $this->load->helper('date');

        $enquiry_data = $this->enquiry_model->get_values($enquiry_id);
        $enquiry_data['enquiry_due_date'] = unix_to_human($enquiry_data['enquiry_due_date']);
        $enquiry_data['enquiry_creation_date'] = unix_to_human($enquiry_data['enquiry_creation_date']);

        $address2 = (empty($enquiry_data['address_address2'])) ? null : $enquiry_data['address_address2'];
        $countryname = ($country = $this->country_model->get($enquiry_data['address_country_id'])) ? $country->country : null;
        $enquiry_countryname = ($enquiry_country = $this->country_model->get($enquiry_data['enquiry_country_id'])) ? $enquiry_country->country : null;

        $enquiry_data['company_details'] = $enquiry_data['company_name'] . "<br />"
                . $enquiry_data['address_address1'] . "<br />"
                . $address2
                . $enquiry_data['address_city'] . ', ' . $enquiry_data['address_state'] . ' ' . $enquiry_data['address_postcode']
                . ' ' . $countryname;

        // Replace constant values
        $enquiry_data['enquiry_status'] = get_lang_for_constant_value("ENQUIRIES_ENQUIRY_STATUS", $enquiry_data["enquiry_status"]);
        $enquiry_data['enquiry_priority'] = get_lang_for_constant_value("ENQUIRIES_ENQUIRY_PRIORITY", $enquiry_data["enquiry_priority"]);

        // Product data
        $product_data = array(
                'Product title' => $enquiry_data['enquiry_product_title'],
                'Description' => $enquiry_data['enquiry_product_description'],
                'Materials' => $enquiry_data['enquiry_product_materials'],
                'Manufacturing Process' => $enquiry_data['enquiry_product_man_process'],
                'Size' => $enquiry_data['enquiry_product_size'],
                'Weight' => $enquiry_data['enquiry_product_weight'],
                'Colour' => $enquiry_data['enquiry_product_colour'],
                'Packaging' => $enquiry_data['enquiry_product_packaging']);

        // Trading data
        $trading_data = array(
                'Minimum Annual Qty' => $enquiry_data['enquiry_min_annual_qty'],
                'Maximum Annual Qty' => $enquiry_data['enquiry_max_annual_qty'],
                'Minimum 1st Order Qty' => $enquiry_data['enquiry_min_order_qty'],
                'Shipping Method' => get_lang_for_constant_value("ENQUIRIES_SHIPPING", $enquiry_data["enquiry_shipping"]),
                'Delivery Terms' => get_lang_for_constant_value("ENQUIRIES_ENQUIRY_DELIVERY", $enquiry_data["enquiry_delivery_terms"]),
                'Delivery Country' => $enquiry_countryname,
                'Delivery Port' => $enquiry_data['enquiry_delivery_port'],
                'Currency' => get_lang_for_constant_value("CURRENCY", $enquiry_data["enquiry_currency"]),
                'Source of Referral' => get_lang_for_constant_value("ENQUIRIES_SOURCE", $enquiry_data["enquiry_source"])
                );

        $this->load->library('pdf', array('header_title' => "Enquiry $enquiry_id", 'header_font_size' => 14));
        $this->pdf->SetSubject("Enquiry $enquiry_id");

        $output = $this->load->view("enquiries/enquiry/export_pdf", array(
                'enquiry_data' => $enquiry_data,
                'product_data' => $product_data,
                'trading_data' => $trading_data), true);

        $this->pdf->writeHTML($output, false, false, false, false, '');
        $this->pdf->output("enquiry_$enquiry_id.pdf", 'D');
    }

    public function csv() {
        echo "Edit controllers/enquiries/export/export_enquiry.php to write the CSV-generating code";

    }

    public function xml() {
        echo "Edit controllers/enquiries/export/export_enquiry.php to write the XML-generating code";

    }

    public function index() {
        echo "Choose pdf, csv or xml";
    }
}
