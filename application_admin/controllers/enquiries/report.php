<?php
/**
 * Contains the Report Controller class
 * @package controllers
 */

/**
 * Report Controller class
 * @package controllers
 */
class Report extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('enquiries/enquiry_model');
        $this->load->model('enquiries/outbound_quotation_model');
        $this->config->set_item('replacer', array('enquiries' => array('enquiry|Enquiries')));
        $this->config->set_item('exclude', array('browse'));
    }

    // Presents a list of predefined reports, with some sort/filter options. No data is presented until PDF output
    function index() {
        require_capability('enquiries:viewreports');

        $title_options = array('title' => 'Additional reports',
                               'help' => 'Select sorting criteria and generate PDF reports using this form',
                               'expand' => 'reports',
                               'icons' => array());

        $pageDetails = array('title' => 'Additional reports',
                             'section_title' => get_title($title_options),
                             'content_view' => 'enquiries/reports',
                             'jstoload' => array('ckeditor/ckeditor', 'ckeditor/adapters/jquery')
                             );

        $this->load->view('template/default', $pageDetails);
    }

    function generate_pdf() {
        require_capability('enquiries:viewreports');
        $this->load->model('country_model');
        $this->load->helper('date');

        $data = $this->input->post();
        foreach ($data as $field => $value) {
            if (preg_match('/submit_([a-z2-4]*)/i', $field, $matches)) {
                $field_code = $matches[1];
            }
        }

        $order_by = $data[$field_code.'_sort'];
        $order_direction = $data[$field_code.'_direction'];

        if (isset($data['submit_overdue'])) {
            $enquiries = $this->enquiry_model->get_overdue($order_by, $order_direction);
        } else if (isset($data['submit_'.$field_code])) {
            $enquiries = $this->enquiry_model->get_pending($field_code, $order_by, $order_direction);
        } else if (isset($data['submit_'.$field_code])) {
            $enquiries = $this->enquiry_model->get_pending($field_code, $order_by, $order_direction);
        } else if (isset($data['submit_'.$field_code])) {
            $enquiries = $this->enquiry_model->get_pending($field_code, $order_by, $order_direction);
        }

        foreach ($enquiries as $key => $enquiry) {
            $enquiries[$key]->enquiry_creation_date = unix_to_human($enquiry->enquiry_creation_date);
        }

        $titles = array('overdue' => "Overdue Report",
                        'pending'.ENQUIRIES_REPORT_PENDING_30 => "Pending Quotations (last 30 days)",
                        'pending'.ENQUIRIES_REPORT_PENDING_90 => "Pending Quotations (31-90 days ago)",
                        'pending'.ENQUIRIES_REPORT_PENDING_180 => "Pending Quotations (91-180 days ago)");

        $today = mdate('%d-%m-%Y');
        $this->load->library('pdf', array('header_title' => $titles[$field_code] ." $today", 'header_font_size' => 14, 'page_orientation' => 'landscape'));
        $this->pdf->setBaseFont('dejavusanscondensed', '', 6);
        $this->pdf->SetSubject($titles[$field_code] ." $today");

        $output = $this->load->view("enquiries/report", array('enquiries' => $enquiries), true);

        $this->pdf->writeHTML($output, false, false, false, false, '');
        $this->pdf->output($field_code."_report_$today.pdf", 'D');
    }

    function pending($interval=ENQUIRIES_REPORT_PENDING_30) {

    }
}
