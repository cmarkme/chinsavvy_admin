<?php
/**
 * @package controllers
 */
class file extends MY_Controller {
    

    function add() {
       $this->load->view('vault/add');
    }
    function browse() {
    	$this->load->view('vault/browse');
    }
}

