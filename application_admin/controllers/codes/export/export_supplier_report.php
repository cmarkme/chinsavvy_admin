<?php
/**
 * Contains the export controller for supplier
 * @package controllers
 * @subpackage codes
 */

require_once APPPATH.'libraries/MY_Export_Controller.php';

/**
 * Export controller for supplier
 * @package controllers
 * @subpackage codes
 */
class Export_supplier extends MY_Export_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function pdf() {
        echo "Edit controllers/codes/export/export_supplier.php to write the PDF-generating code";
    }

    public function csv() {
        echo "Edit controllers/codes/export/export_supplier.php to write the CSV-generating code";

    }

    public function xml() {
        echo "Edit controllers/codes/export/export_supplier.php to write the XML-generating code";

    }

    public function index() {
        echo "Choose pdf, csv or xml";
    }
}
