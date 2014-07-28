<?php
/**
 * Contains the Company Controller class
 * @package controllers
 */

/**
 * Company Controller class
 * @package controllers
 */
class Company extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('company_model');
        $this->load->model('company_address_model');
        $this->config->set_item('replacer', array('company' => array('index|Companies')));
        $this->config->set_item('exclude', array('index'));

        // Being a global controller, companies doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
    }

    public function index($outputtype='html') {
        require_capability('site:viewcompanies');


        $this->load->library('filter');

        $this->filter->add_filter('combo', 'combo', array('company_id' => 'ID',
                                                          'company_code' => 'Code',
                                                          'company_name' => 'Name',
                                                          'company_type|COMPANY_TYPE' => 'Type',
                                                          'company_role|COMPANY_ROLE' => 'Role',
                                                          // 'company_country' => 'Country',
                                                          'company_email' => 'Email'
                                                          ));
        $this->filter->add_filter('dropdown', get_constant_dropdown('COMPANY_TYPE', true), 'Company type', 'company_type', null);
        $this->filter->add_filter('dropdown', get_constant_dropdown('COMPANY_ROLE', true), 'Company role', 'company_role', null);

        $total_records = $this->company_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;

        if (!IS_AJAX && $outputtype == 'html') {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($outputtype);

        $table_data = $this->company_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit);

        if ($outputtype == 'html') {
            $table_data = parent::add_action_column('site', 'company', $table_data, array('edit', 'delete'));
            $pageDetails = parent::get_ajax_table_page_details('site', 'company', $table_data['headings'], array('add'));
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {
            unset($table_data['headings']['notes']);

            $pageDetails = parent::get_export_page_details('company', $table_data);
            $pageDetails['widths'] = array(128, 240, 200, 300, 200, 200, 248, 375, 345);

            parent::output_for_export('site', 'company', $outputtype, $pageDetails);
        }
    }

    public function add() {
        return $this->edit();
    }

    public function edit($company_id=null) {


        require_capability('site:writecompanies');
        $this->load->helper('form_template');

        $assigned_users = array();
        $unassigned_users = array(null => '-- Select an un-assigned user --');

        if (!empty($company_id)) {
            require_capability('site:editcompanies');
            $company_data = $this->company_model->get_values($company_id);
            $company_data['address_country_name'] = (!empty($company_data['address_country_id'])) ? $this->country_model->get_name($company_data['address_country_id']) : '';

            // List of users not currently assigned to a company
            $this->db->select('id')->select("CONCAT(users.surname, ' ', users.first_name) AS name", false)->select("IF (company_id = $company_id, 1, 0) AS this_company", false);
            $this->db->where("company_id IS NULL OR company_id = 0 OR company_id = $company_id");
            $this->db->order_by('name');
            if ($users = $this->user_model->get()) {

                foreach ($users as $user) {
                    if ($user->this_company) {
                        $delete_icon = img(array('src' => 'images/admin/icons/delete_16.gif',
                                                 'class' => 'icon',
                                                 'onclick' => 'return deletethis();',
                                                 'title' => 'Un-assign this user from this company'));
                        $delete_link = anchor("company/remove_user/$company_id/$user->id", $delete_icon);
                        $assigned_users[$user->id] = anchor("users/user/edit/$user->id", $user->name . $delete_link, 'title="Edit this user"');
                    } else {
                        $unassigned_users[$user->id] = $user->name;
                    }
                }
            }

            form_element::$default_data = (array) $company_data;

            // Set up title bar
            $title = "Edit {$company_data['company_name']} Company";
        } else { // adding a new company
            $title = "Create a new Company";
        }

        $this->config->set_item('replacer', array('company' => array('index|Companies'), 'edit' => $title, 'add' => $title));
        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'company/edit',
                             'company_id' => $company_id,
                             'dropdowns' => $this->get_dropdowns(),
                             'assigned_users' => $assigned_users,
                             'unassigned_users' => $unassigned_users
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function get_dropdowns() {

        $this->load->model('country_model');

        $users = $this->user_model->get(array('disabled' => 0));
        $user_list = array();
        foreach ($users as $user) {
            $user_list[$user->id] = "$user->surname $user->first_name";
        }

        $dropdowns = array(
                'roles' => get_constant_dropdown('COMPANY_ROLE'),
                'company_types' => get_constant_dropdown('COMPANY_TYPE'),
                'countries' => $this->country_model->get_dropdown());

        return $dropdowns;
    }

    public function process_edit() {

        require_capability('site:editcompanies');

        $required_fields = array('company_name' => 'Name',
                                 'company_role' => 'Role',
                                 'company_type' => 'Type',
                                 'address_billing_address1' => 'Billing address 1',
                                 'address_billing_city' => 'Billing city',
                                 'address_billing_state' => 'Billing state/province/county',
                                 'address_billing_postcode' => 'Billing postcode',
                                 'address_billing_country_id' => 'Billing country');

        if ($company_id = (int) $this->input->post('company_id')) {
            $company = $this->company_model->get($company_id);
            $redirect_url = 'company/edit/'.$company_id;
            $required_fields['address_billing_id'] = 'Billing address ID';
        } else {
            $redirect_url = 'company/add';
            $company_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $this->form_validation->set_rules('company_code', 'Code', 'callback_check_company_code['.$company_id.']');

        $success = $this->form_validation->run();
        $action_word = ($company_id) ? 'updated' : 'created';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "Company $company_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($company_id);
        }

        $company_data = array(
                'name' => $this->input->post('company_name'),
                'name_ch' => $this->input->post('company_name_ch'),
                'role' => $this->input->post('company_role'),
                'company_type' => $this->input->post('company_type'),
                'code' => $this->input->post('company_code'),
                'url' => $this->input->post('company_url'),
                'phone' => $this->input->post('company_phone'),
                'fax' => $this->input->post('company_fax'),
                'email' => $this->input->post('company_email'),
                'email2' => $this->input->post('company_email2'),
                'notes' => $this->input->post('company_notes')
                );

        if (empty($company_data['code'])) {
            $company_data['code'] = null;
        }

        if (empty($company_id)) {
            if (!($company_id = $this->company_model->add($company_data))) {
                add_message('Could not create this company!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->company_model->edit($company_id, $company_data)) {
                add_message('Could not update this company!', 'error');
                redirect($redirect_url);
            }
        }

        $address_billing_id = $this->input->post('address_billing_id');
        $address_billing_data = array(
                'country_id' => $this->input->post('address_billing_country_id'),
                'city' => $this->input->post('address_billing_city'),
                'type' => COMPANY_ADDRESS_TYPE_BILLING,
                'state' => $this->input->post('address_billing_state'),
                'postcode' => $this->input->post('address_billing_postcode'),
                'province' => $this->input->post('address_billing_province'),
                'address1' => $this->input->post('address_billing_address1'),
                'address2' => $this->input->post('address_billing_address2')
                );

        if (empty($address_shipping_id) && empty($company)) { // Creating first billing address for new company
            $address_billing_data['company_id'] = $company_id;
            if (!$this->company_address_model->add($address_billing_data)) {
                add_message('Could not create this company\'s billing address!', 'error');
                redirect($redirect_url);
            }

        } else {
            if (!$this->company_address_model->edit($address_billing_id, $address_billing_data)) {
                add_message('Could not update this company\'s billing address!', 'error');
                redirect($redirect_url);
            }
        }

        // By now we should have a company_id
        $redirect_url = 'company/edit/'.$company_id;

        $address_shipping_id = $this->input->post('address_shipping_id');
        $address_shipping_data = array(
                'country_id' => $this->input->post('address_shipping_country_id'),
                'type' => COMPANY_ADDRESS_TYPE_SHIPPING,
                'city' => $this->input->post('address_shipping_city'),
                'state' => $this->input->post('address_shipping_state'),
                'postcode' => $this->input->post('address_shipping_postcode'),
                'province' => $this->input->post('address_shipping_province'),
                'address1' => $this->input->post('address_shipping_address1'),
                'address2' => $this->input->post('address_shipping_address2')
                );

        if (empty($address_shipping_id)) {
            $address_shipping_data['company_id'] = $company_id;
            if (!$this->company_address_model->add($address_shipping_data)) {
                add_message('Could not create this company\'s shipping address!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->company_address_model->edit($address_shipping_id, $address_shipping_data)) {
                add_message('Could not update this company\'s shipping address!', 'error');
                redirect($redirect_url);
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("Company $company_id has been successfully $action_word!", 'success');
        redirect($redirect_url);
    }

    /**
     * Callback function used by validation rule. Checks that the company code is the valid length and isn't already used by some other company
     * @param string $code
     * @param int $company_id
     * @return bool
     */
    function check_company_code($code, $company_id=null) {

        return $this->company_model->check_company_code($code, $company_id, 'check_company_code');
    }

    function add_user($company_id, $user_id) {

        $user = $this->user_model->get($user_id);
        $this->user_model->edit($user_id, array('company_id' => $company_id));
        add_message("User " . $this->user_model->get_name($user, 'l f') . " has been successfully added to this company.", 'success');
        redirect("company/edit/$company_id");
    }

    function remove_user($company_id, $user_id) {

        $user = $this->user_model->get($user_id);
        $this->user_model->edit($user_id, array('company_id' => null));
        add_message("User " . $this->user_model->get_name($user, 'l f') . " has been successfully removed from this company.", 'success');
        redirect("company/edit/$company_id");
    }
}
