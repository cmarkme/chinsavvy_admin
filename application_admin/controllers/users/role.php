<?php
/**
 * Contains the Role Controller class
 * @package controllers
 */

/**
 * Role Controller Class
 * @package controllers
 */
class Role extends MY_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('users/role_model');
		$this->load->model('users/user_model');
        $this->config->set_item('replacer', array('users' => array('role/browse|Roles')));
	}

    function index() {
        return $this->browse();
    }

    function browse($outputtype='html') {

        $this->load->helper('title');
        $this->load->library('filter');

        require_capability('users:viewroles');

        $total_records = $this->role_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX && $outputtype == 'html') {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($outputtype);
        $table_data = $this->role_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit);

        if ($outputtype == 'html') {
            $action_icons = array('Edit this role' => 'edit',
                                  'Edit users for this role' => 'user_edit',
                                  'Edit capabilities for this role' => 'key',
                                  'Duplicate this role' => 'duplicate',
                                  'Delete this role' => 'delete');
            $table_data = parent::add_action_column('users', 'role', $table_data, $action_icons);
            $pageDetails = parent::get_ajax_table_page_details('users', 'role', $table_data['headings'], array());
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {
            $pageDetails = parent::get_export_page_details('role', $table_data);
            $pageDetails['widths'] = array(128, 200, 375, 300, 200, 300);
            parent::output_for_export('users', 'role', $outputtype, $pageDetails);
        }
    }

    function view($role_id) {
        $caps = $this->role_model->get_capabilities($role_id);
    }

    function edit($role_id) {

        $role_id = (int) $role_id;
        require_capability('users:editroles');
        $this->load->helper('form_template');

        $role = $this->role_model->get($role_id);

        form_element::$default_data = (array) $role;

        // Set up title bar
        $title = "Edit $role->name Role";
        $this->config->set_item('replacer', array('users' => array('role/browse|Roles'), 'edit' => $title));
        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'users/role/edit',
                             'role_id' => $role_id
                             );
        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('users:editroles');

        $role_id = (int) $this->input->post('role_id');
        $role = $this->role_model->get($role_id);
        $redirect_url = 'users/role/edit/'.$role_id;

        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('description', 'Description', 'trim|required');

        $success = $this->form_validation->run();

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "The $role->name Role has been successfully updated!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($role_id);
        }

        $role_data = array('name' => $this->input->post('name'), 'description' => $this->input->post('description'));

        if (!$this->role_model->edit($role_id, $role_data)) {
            add_message('Could not update this role!', 'error');
            redirect($redirect_url);
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("The $role->name Role has been successfully updated!", 'success');
        redirect('users/role/browse');
    }

    public function user_edit($role_id) {

        require_capability('users:editroles');
        $role = $this->role_model->get($role_id);


        // Set up title bars
        $list_title = "List of users with the $role->name Role";
        $list_title_options = array('title' => $list_title, 'help' => $list_title, 'expand' => 'page', 'icons' => array());
        $add_title = "Add a user to the $role->name Role";
        $add_title_options = array('title' => $add_title, 'help' => $add_title, 'expand' => 'add_div', 'icons' => array());
        $this->config->set_item('replacer', array('users' => array('role/browse|Roles'), 'user_edit' => "users with the $role->name Role"));

        $pageDetails = array('title' => $list_title,
                             'add_title' => get_title($add_title_options),
                             'list_title' => get_title($list_title_options),
                             'content_view' => 'users/role/user_edit',
                             'role_id' => $role_id,
                             'users' => $this->role_model->get_users($role_id),
                             'jstoloadinfooter' => array('jquery/jquery.json',
                                                         'jquery/jquery.url',
                                                         'jquery/datatables/media/js/jquery.dataTables',
                                                         'datatable_pagination',
                                                         'application/users/role_user_edit')
                             );
        $this->load->view('template/default', $pageDetails);
    }

    public function capabilities($role_id) {
        require_capability('users:editroles');

        $this->load->model('users/capability_model');
        $this->load->helper('recursive_list');

        $role = $this->role_model->get($role_id);
        $allcaps = $this->capability_model->get();

        // For each capability assigned to this role, show a hierarchical tree of dependent capabilities
        $capabilities = $this->role_model->get_capabilities($role_id);
        $dependencies = array();
        foreach ($capabilities as $capability) {
            $cap_array = array();
            $caps_to_check = array();
            $dependents = $this->capability_model->get_dependents($capability->id, $cap_array, $caps_to_check, true, $allcaps);
            $dependencies[$capability->id] = $cap_array;
        }

        // Get a hierarchical array of assignable capabilities
        $nested_caps = $this->capability_model->get_nested_caps(null, $dependencies);

        // Set up title bars
        $list_title = "Edit capabilities for the $role->name Role";
        $list_title_options = array('title' => $list_title, 'help' => $list_title, 'expand' => 'page', 'icons' => array());
        $add_title = "Add a capability to the $role->name Role";
        $add_title_options = array('title' => $add_title, 'help' => $add_title, 'expand' => 'add', 'icons' => array());
        $this->config->set_item('replacer', array('users' => array('role/browse|Roles'), 'capabilities' => $list_title));

        $pageDetails = array('title' => $list_title,
                             'add_title' => get_title($add_title_options),
                             'list_title' => get_title($list_title_options),
                             'content_view' => 'users/role/cap_edit',
                             'role_id' => $role_id,
                             'capabilities' => $capabilities,
                             'dependencies' => $dependencies,
                             'assignable_caps' => $nested_caps,
                             'csstoload' => array('jquery.autocomplete', 'jquery.treeview'),
                             'jstoloadinfooter' => array('jquery/jquery.json',
                                                         'jquery/jquery.url',
                                                         'jquery/jquery-treeview/jquery.treeview',
                                                         'jquery/datatables/media/js/jquery.dataTables',
                                                         'datatable_pagination',
                                                         'application/users/role_cap_edit')
                             );
        $this->load->view('template/default', $pageDetails);
    }

    function get_assignable_users($role_id) {

        $term = $this->input->post('term');

        $users = $this->role_model->get_assignable_users($role_id, $term);

        echo json_encode($users);
    }

    function duplicate($role_id) {

        require_capability('users:writeroles');
        $new_role = $this->role_model->duplicate($role_id);
        add_message("This role is a duplicate, please edit its name and description", 'success');
        redirect('users/role/edit/'.$new_role->id);
    }

    function delete_role_cap($role_id, $cap_id) {

        require_capability('users:edit_roles');
        $this->load->model('users/capability_model');

        $cap = $this->capability_model->get($cap_id);
        $result = $this->role_model->remove_capability($role_id, $cap->name);

        if ($result) {
            add_message('Capability successfully removed from this role!', 'success');
        } else {
            add_message('Capability could not be removed from this role!!', 'error');
        }

        redirect('users/role/capabilities/'.$role_id);

    }

    function delete_role_user($role_id, $user_id) {

        require_capability('users:assignroles');
        $result = $this->user_model->unassign_role($user_id, $role_id);

        if ($result) {
            add_message('User unassignment successful!', 'success');
        } else {
            add_message('User unassignment failed!', 'error');
        }

        redirect('users/role/user_edit/'.$role_id);

    }

    function add_cap_to_role($role_id, $cap_id) {

        require_capability('users:editroles');
        $this->load->model('users/capability_model');

        $cap = $this->capability_model->get($cap_id);
        $result = $this->role_model->add_capability($role_id, $cap->name);

        if ($result) {
            add_message('Capability successfully added to this role!', 'success');
        } else {
            add_message('Capability could not be added!', 'error');
        }

        redirect('users/role/capabilities/'.$role_id);
    }

    function add_role_to_user($role_id, $user_id) {

        require_capability('users:assignroles');
        $result = $this->user_model->assign_role($user_id, $role_id);

        if ($result) {
            add_message('User assignment successful!', 'success');
        } else {
            add_message('User assignment failed!', 'error');
        }

        redirect('users/role/user_edit/'.$role_id);
    }
}
