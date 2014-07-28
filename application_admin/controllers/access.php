<?php

class Access extends CI_Controller {
    public function unauthorised($required_capability) {
        $pageDetails = array(
            'title' => 'Unauthorised access',
            'content_view' => 'unauthorised',
            'required_capability' => $required_capability);
        $this->load->view('template/default', $pageDetails);
    }
}
