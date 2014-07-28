<?php
/**
 * @package controllers
 */
class Home extends MY_Controller {

	public function __construct() {
		parent::__construct();
	}

	public function index()
	{
		// Home Page ;-)
        // Load View.
        $pageDetails = array(
            'title' => 'Home of Admin system',
            'csstoload' => array(),
            'jstoload' => array('jquery/jquery'),
            'content_view' => 'home');
        $this->load->view('template/default', $pageDetails);
        
	}
	
}
