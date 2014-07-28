<?php
/**
 * @package controllers
 */
class Login extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('login_model', 'login', true);
        $this->load->library('form_validation');
    }

    function index() {
        if ($this->login->check_session()) {
            redirect('/main');
        }
        $this->load->library('encrypt');

        $rules[] = array('field' => 'username', 'label' => 'Username', 'rules' => 'trim|required');
        $rules[] = array('field' => 'password', 'label' => 'Password', 'rules' => 'required');
        $this->form_validation->set_rules($rules);

        if (!$this->form_validation->run()) {
            $this->load->view('/login_view', array('base_url' => $this->config->item('base_url'), 'message' => $this->session->flashdata('message')));
        } else {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            if ($this->login->auth_user($username, $password)) {
            	//echo "<img src='InMotion-Hosting.png' /></br>Server Error</br>";
            	//echo "<a href='https://admin.chinasavvy.com'>Refresh</a>";exit();
            	
            	
            	
                $previous_url = $this->session->userdata('previous_url');
                if (!empty($previous_url)) {
                    redirect($previous_url);
                } else {
                    redirect('/home');
                }
            } else {
                redirect('/login');
            }
        }
    }

    public function decode_password($user_id) {
        $this->load->library('encrypt');
        $user = $this->user_model->get($user_id);
        echo $this->encrypt->decode($user->password);
    }

    /**
     * This function should be used only once, when migrating the database from the old system to the new.
     * Run it twice, and you will completely screw up all the passwords. Then again, you can decode them but it's better to just run this once then delete or comment out the method.
     * This function also takes care of some general updates such as the QC project's revision strings
    function encrypt_user_passwords($key) {

        $config_key = $this->config->item('encryption_key');

        if ($key != $config_key) {
            redirect('/login');
        }

        $this->load->library('encrypt');
        $this->db->select('id, password');
        $users = $this->user_model->get();

        foreach ($users as $user) {
            $newpassword = $this->encrypt->encode($user->password);
            $this->user_model->edit($user->id, array('password' => $newpassword));
        }

        $this->load->model('qc/project_model');
        $projects = $this->project_model->get();
        foreach ($projects as $project) {
            $this->project_model->update_revision_string($project->id);
        }

        echo "Well done, all passwords properly encrypted!";
    }
     */
}
?>
