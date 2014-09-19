<?php
/**
 * Contains the Export_pdf Controller class
 * @package controllers
 */

/**
 * Export_pdf Controller class
 * @package controllers
 */
class Export_pdf extends MY_Controller {
    function __construct() {
        parent::__construct();
    }

    public function product_specs() {
        $this->load->helper('qc_pdf_helper');
	$revision_no=$this->input->post('revision_no');
        pdf_product_specs(null,$revision_no);

    }

    public function qc_specs() {
        $this->load->helper('qc_pdf_helper');
        pdf_qc_specs();
    }

    public function qc_field_sheet()
    {
        $this->load->helper('qc_pdf_helper');
        pdf_qc_field_sheet();
    }

    public function qc_results($category_id=null, $project_id=null) {
        $this->load->helper('qc_pdf_helper');
        if (!empty($category_id)) {
            $_POST['category_id'] = $category_id;
            $_POST['project_id'] = $project_id;
        }
        pdf_qc_results();
    }

    public function qc_suppliers() {

        $this->load->helper('qc_pdf_helper');
        pdf_qc_suppliers();
    }
}
