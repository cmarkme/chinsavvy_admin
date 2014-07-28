<?php
/**
 * @package controllers
 */
class db_backup extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->dbutil();
        //$this->load->model('login_model', 'login', true);
        //$this->load->library('form_validation');
    }

    function index() {
    	
    	
    	
    	$pageDetails = array(
            'title' => 'Home of Admin system',
            'csstoload' => array(),
            'jstoload' => array('jquery/jquery'),
            'content_view' => 'home');
    	if ($this->session->userdata('user_id')==3868)
    	{
    		$dbs = $this->dbutil->list_databases();
    		 
    		foreach ($dbs as $db)
    		{
    			$pageDetails['db_list'][]=$db;
    		}
    	
    	}
        $this->load->view('template/default', $pageDetails);
    	
        
}
}

