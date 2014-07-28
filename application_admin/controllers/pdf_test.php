<?php
/**
 * @package controllers
 */
class pdf_test extends CI_Controller {

    function __construct()
    {
        parent::__construct();
    }

    function tcpdf()
    {
        if (!defined('K_CELL_HEIGHT_RATIO')) {
            define('K_CELL_HEIGHT_RATIO', 2.5);
        }

        $this->load->library('pdf');
        // set document information
        $this->pdf->SetSubject('TCPDF Tutorial');
        $this->pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set font
        $this->pdf->SetFont('stsongstdlight', '', 16);

        // add a page
        $this->pdf->AddPage();

        // print a line using Cell()
        $this->pdf->Cell(0, 12, 'Example 001 -  女星隆胸铁证', 1, 1, 'C');

        //Close and output PDF document
        $this->pdf->Output('example_001.pdf', 'I');
    }
}
