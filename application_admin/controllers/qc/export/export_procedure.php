<?php
/**
 * Contains the export controller for procedures
 * @package controllers
 */

require_once APPPATH.'core/MY_Export_Controller.php';

/**
 * Export controller for procedures
 * @package controllers
 */
class Export_procedure extends MY_Export_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function pdf($procedure_id, $save_file=false) {

        $this->load->helper('qc_pdf');

        qc_pdf_procedure_report($procedure_id, false);
    }

    public function csv() {
        echo "Edit controllers/qc/export/export_procedure.php to write the CSV-generating code";

    }

    public function xml() {
        echo "Edit controllers/qc/export/export_procedure.php to write the XML-generating code";

    }

    public function index() {
        echo "Choose pdf, csv or xml";
    }
}
