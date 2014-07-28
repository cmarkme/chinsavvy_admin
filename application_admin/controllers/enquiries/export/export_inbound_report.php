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
class Export_inbound_report extends MY_Export_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function pdf() {
        echo "Edit controllers/enquiries/export/export_inbound_report.php to write the PDF-generating code";
    }

    public function csv() {
        echo "Edit controllers/enquiries/export/export_inbound_report.php to write the CSV-generating code";

    }

    public function xml() {
        echo "Edit controllers/enquiries/export/export_inbound_report.php to write the XML-generating code";

    }

    public function index() {
        echo "Choose pdf, csv or xml";
    }
}
