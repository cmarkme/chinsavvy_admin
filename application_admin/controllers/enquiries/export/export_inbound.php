<?php
/**
 * Contains the export controller for inbound quotations
 * @package controllers
 */

require_once APPPATH.'libraries/MY_Export_Controller.php';

/**
 * Export controller for inbound quotations
 * @package controllers
 */
class Export_inbound extends MY_Export_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function pdf($quotation_id) {
        echo "Edit controllers/enquiries/export/export_inbound.php to write the PDF-generating code (quotation_id: $quotation_id)";
    }

    public function csv($quotation_id) {
        echo "Edit controllers/enquiries/export/export_inbound.php to write the CSV-generating code (quotation_id: $quotation_id)";

    }

    public function xml($quotation_id) {
        echo "Edit controllers/enquiries/export/export_inbound.php to write the XML-generating code (quotation_id: $quotation_id)";

    }

    public function index() {
        echo "Choose pdf, csv or xml";
    }
}
