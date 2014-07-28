<?php
/**
 * @package controllers
 */
class User extends MY_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('users/user_model');
        $this->config->set_item('replacer', array('users' => array('user|Users'), 'add' => 'Create new user account'));
	}

    function index($outputtype='html') {

        $this->load->helper('title');
        $this->load->library('filter');
        require_capability('users:viewusers');

        $this->filter->add_filter('combo', 'combo', array('user_id' => 'ID',
                                                        'user_first_name' => 'First Name',
                                                        'user_surname' => 'Last Name',
                                                        'user_email' => 'Email'));
        $this->filter->add_filter('dropdown', $this->role_model->get_dropdown(), 'Role', 'role_id', null);

        $total_records = $this->user_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX && $outputtype == 'html') {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($outputtype);
        $table_data = $this->user_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit);

        // Convert email addresses to links
        foreach ($table_data['rows'] as $key => $row)  {
            if (!empty($row[2])) {
                $table_data['rows'][$key][2] = anchor('email/index/'.$row[0], $row[2]);
            }
        }

        if ($outputtype == 'html') {
            $action_icons = array('Edit this user' => 'edit',
                                  'Edit permissions' => 'key',
                                  'Delete this user' => 'delete');
            $table_data = parent::add_action_column('users', 'user', $table_data, $action_icons);
            $pageDetails = parent::get_ajax_table_page_details('users', 'user', $table_data['headings'], array('add'));
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {
            $pageDetails = parent::get_export_page_details('user', $table_data);
            $pageDetails['widths'] = array(128, 200, 375, 300, 200, 300);
            parent::output_for_export('users', 'user', $outputtype, $pageDetails);
        }
    }

    function capabilities($user_id) {

        require_capability('users:viewusers');
        $this->load->model('users/capability_model');
        $this->load->helper('form_template');

        $user = $this->user_model->get($user_id);
        $user_roles = $this->user_model->get_roles($user_id);
        $all_caps = $this->capability_model->get(null, false, 'name');

        // Add a better label to capabilities
        foreach ($all_caps as $key => $cap) {
            $parts = explode(':', $cap->name);
            $all_caps[$key]->label = ucfirst($parts[0]) . ' system: ' . $cap->description;
        }

        $roles_and_caps = $this->capability_model->get_with_roles();
        $role_caps = $roles_and_caps['roles'];
        $cap_roles = $roles_and_caps['capabilities'];
        $av_roles = $this->user_model->get_available_roles($user_id);

        // Build user_capabilities based on role_caps and user_roles
        $user_capabilities = array();

        if (!empty($user_roles)) {
            foreach ($user_roles as $user_role) {
                $user_role_caps = $role_caps[$user_role->id];
                foreach ($user_role_caps as $cap_id => $cap) {
                    $user_capabilities[] = $cap->cap_name;
                }
            }
        } else {
            $user_roles = array();
        }

        $available_roles = array(null => '-- Select a new role --');

        foreach ($av_roles as $role) {
            $available_roles[$role->id] = $role->name;
        }

        // Set up title bars
        $add_title = "Add a role to " . $this->user_model->get_name($user);
        $add_title_options = array('title' => $add_title, 'help' => $add_title, 'expand' => 'add', 'icons' => array());
        $roles_title = "Roles for " . $this->user_model->get_name($user);
        $roles_title_options = array('title' => $roles_title, 'help' => $roles_title, 'expand' => 'roles', 'icons' => array());
        $capabilities_title = "Capabilities for " . $this->user_model->get_name($user);
        $capabilities_title_options = array('title' => $capabilities_title, 'help' => $capabilities_title, 'expand' => 'capabilities', 'icons' => array());

        $pageDetails = array('title' => 'User Permissions for ' . $this->user_model->get_name($user),
                             'add_title' => get_title($add_title_options),
                             'roles_title' => get_title($roles_title_options),
                             'capabilities_title' => get_title($capabilities_title_options),
                             'content_view' => 'users/user/role_edit',
                             'user_roles' => $user_roles,
                             'user_capabilities' => $user_capabilities,
                             'available_roles' => $available_roles,
                             'role_caps' => $role_caps,
                             'all_caps' => $all_caps,
                             'cap_roles' => $cap_roles,
                             'user_id' => $user_id,
                             'jstoloadinfooter' => array('jquery/jquery.json',
                                                         'jquery/jquery.url',
                                                         'jquery/datatables/media/js/jquery.dataTables',
                                                         'datatable_pagination',
                                                         'application/users/user_role_edit')
                             );
        $this->load->view('template/default', $pageDetails);
    }

    function add_role($user_id, $role_id) {

        require_capability('users:assignroles');
        $result = $this->user_model->assign_role($user_id, $role_id);

        if ($result) {
            add_message('Role successfully added!', 'success');
        } else {
            add_message('Role could not be added!', 'error');
        }

        redirect('users/user/capabilities/'.$user_id);

    }

    function delete_user_role($user_id, $role_id) {

        require_capability('users:assignroles');
        $result = $this->user_model->unassign_role($user_id, $role_id);

        if ($result) {
            add_message('Role successfully removed!', 'success');
        } else {
            add_message('Role could not be removed!', 'error');
        }

        redirect('users/user/capabilities/'.$user_id);

    }

    function add() {

        $this->load->helper('form_template');
        $this->load->helper('dropdowns');
        $editor_config = array('ckeditor_basePath' => $this->config->item('ckeditor_basePath'),
                               'ckeditor_toolbar' => $this->config->item('ckeditor_default_toolbar'),
                               'ckeditor_extraPlugins' => null);
        $this->load->library('CKeditor', $editor_config);

        // Set up title bars
        $top_title = "Creating new user";
        $top_title_options = array('title' => $top_title, 'help' => $top_title, 'expand' => 'page', 'icons' => array());
        $details_title = "Personal Details";
        $details_title_options = array('title' => $details_title, 'help' => $details_title, 'expand' => 'details', 'icons' => array());
        $contacts_title = "Contacts";
        $contacts_title_options = array('title' => $contacts_title, 'help' => $contacts_title, 'expand' => 'contacts', 'icons' => array());

        $pageDetails = array(
                'title' => $top_title,
                'top_title' => get_title($top_title_options),
                'details_title' => get_title($details_title_options),
                'contacts_title' => get_title($contacts_title_options),
                'salutations' => get_salutations(),
                'csstoload' => array(),
                'jstoloadinfooter' => array('jquery/jquery.domec',
                                            'jquery/jquery.form',
                                            'jquery/jquery.json',
                                            'jquery/jquery.loading',
                                            'jquery/pause',
                                            'jquery/jquery.selectboxes',
                                            'application/users/user_edit'),
                'content_view' => 'users/user/edit');
        $this->load->view('template/default', $pageDetails);
    }

    function edit($user_id) {

        $this->load->helper('form_template');
        $this->load->helper('dropdowns');
        $this->load->model('users/user_option_model');
        $this->load->model('company_model');
        $editor_config = array('ckeditor_basePath' => $this->config->item('ckeditor_basePath'),
                               'ckeditor_toolbar' => $this->config->item('ckeditor_default_toolbar'),
                               'ckeditor_extraPlugins' => null);
        $this->load->library('CKeditor', $editor_config);

        $config_key = $this->config->item('encryption_key');
        $this->load->library('encrypt');

        $user = $this->user_model->get($user_id);
        $user_options = $this->user_option_model->get_by_user_id($user_id, null, false);
        $company_name = (!empty($user->company_id) && ($company = $this->company_model->get($user->company_id))) ? $company->name : '';

        $first_name_chinese = null;
        $surname_chinese = null;

        if (is_array($user_options)) {
            foreach ($user_options as $user_option) {
                if ($user_option->name == 'first_name_ch') {
                    $first_name_chinese = $user_option->value;
                }
                if ($user_option->name == 'last_name_ch') {
                    $surname_chinese = $user_option->value;
                }
            }
        }

        form_element::$default_data = array('user_id' => $user_id,
                                            'action' => 'edit_user',
                                            'first_name' => $user->first_name,
                                            'surname' => $user->surname,
                                            'username' => $user->username,
                                            'password' => $this->encrypt->decode($user->password),
                                            'salutation' => $user->salutation,
                                            'disabled' => $user->disabled,
                                            'signature' => $user->signature,
                                            'first_name_chinese' => $first_name_chinese,
                                            'surname_chinese' => $surname_chinese
                                            );

        // Set up title bars
        $top_title = "Editing user " . $this->user_model->get_name($user);
        $top_title_options = array('title' => $top_title, 'help' => $top_title, 'expand' => 'page', 'icons' => array());
        $details_title = "Personal Details";
        $details_title_options = array('title' => $details_title, 'help' => $details_title, 'expand' => 'details', 'icons' => array());
        $contacts_title = "Contacts";
        $contacts_title_options = array('title' => $contacts_title, 'help' => $contacts_title, 'expand' => 'contacts', 'icons' => array());

        $this->config->set_item('replacer', array('users' => array('user|Users'), 'edit' => $top_title));

        $pageDetails = array(
                'title' => $top_title,
                'top_title' => get_title($top_title_options),
                'details_title' => get_title($details_title_options),
                'contacts_title' => get_title($contacts_title_options),
                'salutations' => get_salutations(),
                'csstoload' => array(),
                'user_id' => $user_id,
                'company_name' => $company_name,
                'company_id' => $user->company_id,
                'jstoload' => array('jquery/jquery.domec',
                                            'jquery/jquery.form',
                                            'jquery/jquery.json',
                                            'jquery/jquery.loading',
                                            'jquery/pause',
                                            'jquery/jquery.selectboxes',
                                            'application/users/user_edit'),
                'content_view' => 'users/user/edit');
        $this->load->view('template/default', $pageDetails);
    }

    function process_edit() {

        $this->load->model('users/user_option_model');
        $config_key = $this->config->item('encryption_key');
        $this->load->library('encrypt');

        $debug = true;

        if (!IS_AJAX && !$debug) {
            show_error("This page can only be accessed through an AJAX request!");
            return false;
        }

        $json_data = array('errors' => array());

        $first_name = $this->input->post('first_name');
        $surname = $this->input->post('surname');
        $username = $this->input->post('username');
        $salutation = $this->input->post('salutation');
        $user_id = $this->input->post('user_id');

        if (empty($first_name)) {
            $json_data['errors']['first_name'] = 'Please enter a first name for this user.';
        }

        if (empty($surname)) {
            $json_data['errors']['surname'] = 'Please enter a Last name for this user.';
        }

        if (empty($salutation)) {
            $json_data['errors']['salutation'] = 'Please enter a title for this user.';
        }

        // Test username for uniqueness
        if (!$user_id && $this->user_model->get(array('username' => $username), true)) {
            $json_data['errors']['username'] = 'This username is already in use, please choose another one.';
        }

        $user = array(
                'first_name' => $first_name,
                'surname' => $surname,
                'salutation' => $salutation,
                'username' => $username,
                'signature' => $this->input->post('signature'),
                'password' => $this->encrypt->encode($this->input->post('password')),
                'disabled' => $this->input->post('disabled'));

        if (empty($json_data['errors'])) {
            if (empty($user_id)) {
                if (!($user_id = $this->user_model->add($user))) {
                    $json_data['message'] = 'The user could not be entered into the database for an unknown reason.';
                    $json_data['type'] = 'error';
                } else {
                    $json_data['user_id'] = $user_id;
                    $json_data['message'] = 'The new user was successfully recorded';
                    $json_data['type'] = 'success';
                }
            } else {
                if (!$this->user_model->edit($user_id, $user)) {
                    $json_data['message'] = 'The user details could not be updated for an unknown reason.';
                    $json_data['type'] = 'error';
                } else {
                    $json_data['user_id'] = $user_id;
                    $json_data['message'] = 'The user\'s details were successfully updated';
                    $json_data['type'] = 'success';
                }
            }
        } else {
            $json_data['message'] = 'Some data entry errors prevented the processing of this user. See error messages in red below.';
            $json_data['type'] = 'error';
        }

        $user_options = array('first_name_ch' => $this->input->post('first_name_chinese'), 'last_name_ch' => $this->input->post('surname_chinese'));

        $this->user_option_model->update_options($user_id, $user_options);

        if (empty($json_data['message']) && empty($json_data['errors'])) {
            $json_data['message'] = 'No changes made to this user';
            $json_data['type'] = 'warning';
        }
        echo json_encode($json_data);
    }

    function get_data($user_id) {

        $debug = true;
        $this->load->model('users/user_option_model');
        $config_key = $this->config->item('encryption_key');
        $this->load->library('encrypt');

        if (!IS_AJAX && !$debug) {
            show_error("This page can only be accessed through an AJAX request!");
            return false;
        }

        $user = $this->user_model->get($user_id);

        $user_data = array('user_id' => $user_id,
                           'first_name' => $user->first_name,
                           'surname' => $user->surname,
                           'username' => $user->username,
                           'password' => $this->encrypt->decode($user->password),
                           'salutation' => $user->salutation,
                           'disabled' => $user->disabled,
                           'signature' => $user->signature
                           );

        $user_options = $this->user_option_model->get_by_user_id($user_id, null, false);

        $user_data['first_name_chinese'] = null;
        $user_data['surname_chinese'] = null;

        if (is_array($user_options)) {
            foreach ($user_options as $user_option) {
                if ($user_option->name == 'first_name_ch') {
                    $user_data['first_name_chinese'] = $user_option->value;
                }
                if ($user_option->name == 'last_name_ch') {
                    $user_data['surname_chinese'] = $user_option->value;
                }
            }
        }

        $user_data['emails'] = array();
        $user_data['phones'] = array();
        $user_data['mobiles'] = array();
        $user_data['faxes'] = array();

        $emails = $this->user_contact_model->get_by_user_id($user_id, USERS_CONTACT_TYPE_EMAIL, false);

        foreach ($emails as $email) {
            $user_data['emails'][] = (array) $email;
        }

        $workphones = $this->user_contact_model->get_by_user_id($user_id, USERS_CONTACT_TYPE_PHONE, false);
        foreach ($workphones as $workphone) {
            $user_data['phones'][] = (array) $workphone;
        }

        $faxes = $this->user_contact_model->get_by_user_id($user_id, USERS_CONTACT_TYPE_FAX, false);
        foreach ($faxes as $fax) {
            $user_data['faxes'][] = (array) $fax;
        }

        $mobiles = $this->user_contact_model->get_by_user_id($user_id, USERS_CONTACT_TYPE_MOBILE, false);
        foreach ($mobiles as $mobile) {
            $user_data['mobiles'][] = (array) $mobile;
        }

        echo json_encode($user_data);
    }

    function update_default_contact($contact_id) {

        $debug = true;
        $this->load->model('users/user_contact_model');

        if (!IS_AJAX && !$debug) {
            show_error("This page can only be accessed through an AJAX request!");
            return false;
        }

        $this->user_contact_model->set_as_default($contact_id);
        $contact = $this->user_contact_model->get($contact_id);

        $data['message'] = 'This ' . get_lang_for_constant_value('USERS_CONTACT_TYPE_', $contact->type) . ' has been set as the default.';
        $data['type'] = 'success';
        echo json_encode($data);
    }

    function set_notification($contact_id, $value) {

        if (!IS_AJAX) {
            show_error("This page can only be accessed through an AJAX request!");
            return false;
        }
        $this->load->model('users/user_contact_model');

        $value = ($value == 'false') ? 0 : 1;
        $not = ($value) ? '' : 'NOT';

        $this->user_contact_model->edit($contact_id, array('receive_notifications' => $value));

        $data['message'] = "This email address has been set to $not receive notifications.";
        $data['type'] = 'success';

        echo json_encode($data);
    }

    function delete_contact($contact_id) {

        if (!IS_AJAX) {
            show_error("This page can only be accessed through an AJAX request!");
            return false;
        }
        $this->load->model('users/user_contact_model');

        $contact = $this->user_contact_model->get($contact_id);
        $result = $this->user_contact_model->delete($contact_id);
        $data['message'] = 'The ' . get_lang_for_constant_value('USERS_CONTACT_TYPE_', $contact->type) . ' has been successfully deleted';
        $data['type'] = 'success';
        echo json_encode($data);
    }

    function save_contact() {

        if (!IS_AJAX) {
            show_error("This page can only be accessed through an AJAX request!");
            return false;
        }

        $this->load->model('users/user_contact_model');

        $data = array('errors' => array());

        $contact_id = $this->input->post('contact_id');
        $field_name = $this->input->post('field_name');
        preg_match('/(fax|email|phone|mobile)\[([0-9])*\]/', $field_name, $matches);
        $type_label = $matches[1];
        $contact_type = constant('USERS_CONTACT_TYPE_'.strtoupper($type_label));

        $contact_data['user_id'] = $this->input->post('user_id');
        $contact_data['type'] = $contact_type;
        $contact_data['contact'] = $this->input->post('value');

        // Email validation
        if ($contact_type == USERS_CONTACT_TYPE_EMAIL && !preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $contact_data['contact'])) {
            $data['message'] = 'Invalid email address!';
            $data['type'] = 'error';
            $index = 'email_0';
            if ($contact_id) {
                $index = "email_$contact_id";
            }
            $data['errors'][$index] = 'This email address is invalid, please correct it.';
            echo json_encode($data);
            die();
        }

        if ($contact_id) {
            $this->user_contact_model->edit($contact_id, $contact_data);
            $data['message'] = 'The ' . $type_label . ' has been successfully updated';
        } else {
            $this->user_contact_model->add($contact_data);
            $data['message'] = 'The ' . $type_label . ' has been successfully added';
        }

        $data['type'] = 'success';
        echo json_encode($data);
    }
}
?>
