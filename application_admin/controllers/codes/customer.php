<?php
/**
 * Contains the Customer Controller class
 * @package controllers
 */

/**
 * Customer Controller class
 * @package controllers
 */
class Customer extends MY_Controller {
    /**
     * Because a customer represents data in 3 different tables, we must keep track of inserted data in case an error
     * occurs during insertion of one of these tables. In this case, we go through this array and delete all inserted data
     * to "undo" the operation
     * @var array $inserted_records
     */
    private $inserted_records=array();

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('codes/customer_model');
        $this->load->model('company_model');
        $this->load->model('company_address_model');
        $this->config->set_item('replacer', array('codes' => array('codes/browse|Customer Codes')));
        $this->config->set_item('exclude', array('browse'));
    }

    function index($id=null) {
        $this->browse();
    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     */
    function browse() {
        require_capability('codes:viewcustomers');


        $this->load->helper('title');
        $this->load->library('filter');

        $this->filter->add_filter('combo', 'combo', array('company_id' => 'ID',
                                                          'company_name' => 'Name',
                                                          'countries.country' => 'Country',
                                                          'code' => 'Code'));

        $this->db->where_in('role', array(COMPANY_ROLE_ENQUIRER, COMPANY_ROLE_CUSTOMER));
        $this->db->where('code IS NOT ', 'NULL', false);
        $total_records = $this->customer_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        if (!IS_AJAX) {
            $limit = 1;
        }

        $table_data = $this->customer_model->get_data_for_listing(parent::process_datatable_params(), $this->filter->filters, $limit);
        $table_data = parent::add_action_column('codes', 'customer', $table_data, array('edit', 'delete'));
        $pageDetails = parent::get_ajax_table_page_details('codes', 'customer', $table_data['headings'], array('add'));

        parent::output_ajax_table($pageDetails, $table_data, $total_records);
    }


    public function add() {
        require_capability('codes:writecustomers');
        $this->load->helper('form_template');
        $this->load->model('users/role_model');
        $title = 'Adding a new customer code';

        $this->config->set_item('replacer', array('codes' => array('customer|customers'), 'edit' => 'New customer code'));

        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'codes/customer/add'
                             );

        $this->load->view('template/default', $pageDetails);
    }

    /**
     * Possible edit scenarios:
     * 1. Customer_id (company_id) is given. User wants to:
     *    a. Change company details
     *    b. Change technical contact details
     *    c. Change corporate contact details
     *    d. Assign different technical or corporate contact to this company
     * 2. No customer_id is given: we are creating a new customer
     *    a. User exists in DB, but not company: enter a new company, do not allow edit of user
     *    b. User and company exist in DB: select user and company from dropdowns. Only allow edit of company code
     *    c. Company exists in DB, but not user: select company from dropdown, enter user data, disable company fields
     *    d. Neither user nor company exist in DB: allow entry of all fields, but check for uniqueness of code and email addresses. Redirect if necessary
     */
    public function edit($customer_id=null, $user_exists=false, $company_exists=false) {


        require_capability('codes:writecustomers');
        $this->load->helper('form_template');
        $this->load->helper('date');
        $this->load->model('users/role_model');

        $corporate_contact_user_id = $this->input->post('corporate_contact_user_id');
        $defaults = array();

        if (!empty($customer_id)) { // Editing an existing customer
            require_capability('codes:editcustomers');
            $customer_data = (array) $this->customer_model->get_values($customer_id);
            $defaults = $customer_data;
            $title = "Editing customer code for {$customer_data['company_name']}";
        } else { // Adding a new customer
            $title = "Create a new customer code";
        }

        $corporate_contact = null;
        $corporate_contact_details = array();

        // Corporate user may have its user_id set in POST
        if (!empty($corporate_contact_user_id)) {
            $corporate_contact = $this->user_model->get($corporate_contact_user_id);
        } else if (!empty($customer_id)) {
            $role = $this->role_model->get(array('name LIKE ' => 'Corporate contact'), true);
            $this->user_model->filter_by_role($role->id, $this);
            $this->db->select('users.*');
            $corporate_contact = $this->user_model->get(array('company_id' => $customer_id), true);

            if (empty($corporate_contact)) {
                // Maybe user exists but doesn't have the right role
                $corporate_contact = $this->user_model->get(array('company_id' => $customer_id), true);

                if (!empty($corporate_contact)) {
                    $this->user_model->assign_role($corporate_contact->id, $role->id);
                }
            }
        }

        if (!is_null($corporate_contact) && !empty($corporate_contact->id)) {
            $corporate_contact_details = $this->user_model->get_values($corporate_contact->id);

            foreach ($corporate_contact_details as $key => $val) {
                $corporate_contact_details["corporate_contact_$key"] = $val;
                unset($corporate_contact_details[$key]);
            }

            $corporate_contact_details['id'] = $corporate_contact->id;
            $defaults = array_merge($defaults, $corporate_contact_details);
        }

        // Technical User
        $technical_contact = null;
        $technical_contact_details = array();
        $technical_contact_user_id = $this->input->post('technical_contact_user_id');

        if (!empty($technical_contact_user_id)) {
            $technical_contact = $this->user_model->get($technical_contact_user_id);
        } else if (!empty($customer_id)) {
            $role = $this->role_model->get(array('name LIKE ' => 'Technical contact'), true);
            $this->user_model->filter_by_role($role->id, $this);
            $technical_contact = $this->user_model->get(array('company_id' => $customer_id), true);

            if (!empty($technical_contact)) {

                if ($this->input->post('remove_technical_contact')) {
                    $this->user_model->delete($technical_contact->id);
                    $technical_contact = null;
                }
            }
        }

        if (!is_null($technical_contact) && !empty($technical_contact->user_id)) {

            $technical_contact_details = $this->user_model->get_values($technical_contact->user_id);

            foreach ($technical_contact_details as $key => $val) {
                $technical_contact_details["technical_contact_$key"] = $val;
                unset($technical_contact_details[$key]);
            }

            $defaults = array_merge($defaults, $technical_contact_details);
            $technical_contact_details['id'] = $technical_contact->user_id;
        }

        form_element::$default_data = $defaults;

        $this->config->set_item('replacer', array('codes' => array('customer|customers'), 'edit' => $title));

        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'codes/customer/edit',
                             'customer_id' => $customer_id,
                             'dropdowns' => $this->get_dropdowns(),
                             'corporate_contact_details' => $corporate_contact_details,
                             'technical_contact_details' => $technical_contact_details,
                             'jstoload' => array('jquery/jquery.form', 'jquery/jquery.json', 'application/codes/customer_edit'),
                             'csstoload' => array('jquery.autocomplete')
                             );

        if (!empty($customer_id)) {
             $pageDetails['updated_date'] = unix_to_human($defaults['company_revision_date']);
        }

        $this->load->view('template/default', $pageDetails);
    }

    public function process_add() {
        require_capability('codes:writecustomers');
        $user_exists = ($this->input->post('user_exists')) ? $this->input->post('user_exists') : 0;
        $company_exists = ($this->input->post('company_exists')) ? $this->input->post('company_exists') : 0;
        redirect('codes/customer/edit/0/'.$user_exists.'/'.$company_exists);
    }

    public function process_edit() {


        require_capability('codes:editcustomers');
        $required_fields = array();

        if ($customer_id = (int) $this->input->post('customer_id')) {
            $customer = $this->customer_model->get($customer_id);
            $redirect_url = 'codes/customer/edit/'.$customer_id;
        } else {
            $redirect_url = 'codes/customer/add';
            $customer_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $this->form_validation->set_rules('company_code', 'Code', 'trim|alpha|max_length[2]');

        $errors = !$this->form_validation->run();
        $action_word = ($customer_id) ? 'updated' : 'created';
        $company_code = $this->input->post('company_code');
        $company_id = $this->input->post('company_id');

        // First check if an "existing_comp_id" value was given
        // This value means that an email address associated with an existing company was given,
        // and this company does not yet have a Customer code. The purpose of this form submit is then
        // simply to update the company by giving it a code.
        // The code will then go to the Update section instead of the Insert section
        if ($existing_comp_id = $this->input->post('existing_comp_id')) {
            if (empty($company_code)) {
                add_message('You did not enter a company code for this customer!', 'error');
                $errors = true;
            } else {
                $this->company_model->edit($existing_comp_id, array('code' => $company_code, 'company_type' => COMPANY_TYPE_OTHER, 'role' => COMPANY_ROLE_CUSTOMER));
                $company_id = $existing_comp_id;
            }
        }

        if ($errors) {
            return $this->edit($customer_id);
        }

        $company_data = array(
                'phone' => $this->input->post('company_phone'),
                'fax' => $this->input->post('company_fax'),
                'notes' => $this->input->post('company_notes'),
                'name' => $this->input->post('company_name'),
                'code' => $company_code
                );

        if (empty($company_id)) { // Creating customer
            $company_data['company_type'] = COMPANY_TYPE_OTHER;
            $company_data['role'] = COMPANY_ROLE_CUSTOMER;

            if ($this->company_model->check_company_code($company_code)) {
                // If only the company code is given, we are giving a company code to an existing company.
                $this->inserted_records['companies'][] = $customer_id = $company_data['id'] = $this->company_model->add($company_data);

                if (!is_numeric($company_data['id'])) {
                    add_message('There was an error with the recording of your customer. (customer id = ' . $company_data['id'] . ')', 'error');
                    $errors = true;
                } else {
                    $customer_id = $company_data['id'];
                }

                $this->process_address($company_data, $errors);
                $this->process_address($company_data, $errors, 'shipping');
                $this->process_contact($company_data, $errors, 'corporate');
                $this->process_contact($company_data, $errors, 'technical');
            } else {
                add_message('This company code is already taken!', 'error');
                redirect('codes/customer/edit');
            }

        } else { // Updating customer
            if ($this->company_model->check_company_code($company_code, $company_id)) {
                $company_data['id'] = $company_id;
                $this->process_address($company_data, $errors);
                $this->process_address($company_data, $errors, 'shipping');
                $this->process_contact($company_data, $errors, 'corporate');
                $this->process_contact($company_data, $errors, 'technical');

                if ($errors || !$this->company_model->edit($company_id, $company_data)) {
                    add_message('There was an error with the updating of this customer code', 'error');
                    $errors = true;
                }
            }
        }

        // Cleanup data if errors occurred
        if ($errors) {
            foreach ($this->inserted_records as $table => $array_of_ids) {
                foreach ($array_of_ids as $id) {
                    $this->db->delete($table, array('id' => $id));
                }
            }
            // redirect('codes/customer/edit/'.$customer_id);
            die();
        }

        add_message("Customer $customer_id has been successfully $action_word!", 'success');
        redirect('codes/customer/browse');
    }

    private function process_address($company_data, &$errors, $type='billing') {


        $address_id = $this->input->post($type.'_address_address_id');
        $address_type = ($type == 'billing') ? COMPANY_ADDRESS_TYPE_BILLING : COMPANY_ADDRESS_TYPE_SHIPPING;

        $address_data = array('country_id' => $this->input->post($type.'_address_country_id'),
                              'address1' => $this->input->post($type.'_address_address1'),
                              'address2' => $this->input->post($type.'_address_address2'),
                              'city' => $this->input->post($type.'_address_city'),
                              'province' => $this->input->post($type.'_address_province'),
                              'state' => $this->input->post($type.'_address_state'),
                              'postcode' => $this->input->post($type.'_address_postcode'),
                              'default_address' => true,
                              'company_id' => $company_data['id']
                              );

        if (!$errors && !empty($address_data['country_id'])) {

            if (empty($address_id) && !empty($type)) { // Creating a new address
                $address_data['type'] = $address_type;
                if ($address_id = $this->company_address_model->add($address_data)) {
                    $this->inserted_records['company_addresses'][] = $address_id;
                } else {
                    add_message('There was an error while recording the '.$type.' address for this customer', 'error');
                    $errors = true;
                }
            } else {
                if (!$this->company_address_model->edit($address_id, $address_data)) {
                    add_message('There was an error while recording the '.$type.' address for this customer', 'error');
                    $errors = true;
                }
            }
        } elseif (!$errors && empty($address_id)) { // The address was removed
            if ($address = $this->company_address_model->get(array('company_id' => $company_data['id'], 'type' => $address_type), true)) {

                $this->company_address_model->delete($address->id);
            }
        }
    }

    private function process_contact($company_data, &$errors, $type='corporate') {


        $user_data = array('first_name' => $this->input->post($type.'_contact_user_first_name'),
                           'surname' => $this->input->post($type.'_contact_user_surname'),
                           'salutation' => $this->input->post($type.'_contact_user_salutation'),
                           'ftpuserid' => $this->input->post($type.'_contact_user_ftpuserid')
                          );

        $contact_data = array('email' => $this->input->post($type.'_contact_user_email'),
                              'phone' => $this->input->post($type.'_contact_user_phone'),
                              'mobile' => $this->input->post($type.'_contact_user_mobile')
                             );

        $submitted_password = $this->input->post($type.'_contact_user_password');
        if (!empty($submitted_password)) {
            $user_data['password'] = $submitted_password;
        }

        $user_id = $this->input->post($type.'_contact_id');

        if ($user_id == 0) {
            $user_id = null;
        }

        if (!$errors) {

            if (!empty($user_id)) { // Updating
                if (empty($contact_data['email']) && $type == 'technical') {
                    add_message('You did not enter an email address for the technical contact. '
                        . 'Since this is a required field, we assume that you want to remove '
                        . 'the technical contact altogether. The fields have been reset accordingly.', 'warning');
                    $this->user_model->edit($user_id, array('company_id' => null));

                    foreach ($_POST as $key => $val) {
                        if (preg_match('/technical_contact/', $key, $matches)) {
                            unset($_POST[$key]);
                            unset($data[$key]);
                        }
                    }

                    $_POST['remove_technical_contact'] = true;

                } else {
                    if (!$this->user_model->edit($user_id, $user_data)) {
                        add_message('There was an error while updating the '.$type.' contact for this customer', 'error');
                        $errors = true;
                    } else {
                        $this->user_model->assign_contact_data($user_id, $type.'_contact_user_');
                        $role = $this->role_model->get(array('name' => ucfirst($type) . ' contact'), true);
                        if (empty($role)) {
                            add_message("The '$type contact' role has been deleted or renamed to something else. This is not permitted and makes the entire system unstable, please contact the system administator immediately for a fix.", 'error');
                            return false;
                        }
                        $this->user_model->assign_role($user_id, $role->id);
                    }
                }

            } else { // Creating
                $already_exists = false;

                // Check that the given email address doesn't already identify another user
                if (empty($contact_data['email'])) {
                    add_message('You did not enter an email address for the '.$type.' contact. All other '.$type.' contact fields have been ignored.',
                            'warning');
                    return;
                } elseif ($user_id = $this->user_model->already_exists($contact_data['email'])) {
                    // If email found, override all other entered fields with user from DB
                    $_POST[$type.'_contact_id'] = $user_id;
                    $already_exists = true;
                }

                $user_data['company_id'] = $company_data['id'];

                if (!$already_exists && !($user_id = $this->user_model->add($user_data))) {
                    add_message('There was an error while recording the '.$type.' contact for this customer', 'error');
                    $errors = true;
                } elseif (!$already_exists) {
                    $this->user_model->assign_contact_data($user_id, $type.'_contact_user_');
                    $role = $this->role_model->get(array('name' => ucfirst($type) . ' contact'), true);
                    if (empty($role)) {
                        add_message("The '$type contact' role has been deleted or renamed to something else. This is not permitted and makes the entire system unstable, please contact the system administator immediately for a fix.", 'error');
                        return false;
                    }
                    $this->user_model->assign_role($user_id, $role->id);
                } else {
                    $this->inserted_records['users'][] = $user_id;
                }
            }
        }
    }

    private function get_dropdowns() {

        $this->load->helper('dropdowns');
        $this->load->model('country_model');
        return array('countries' => $this->country_model->get_dropdown(), 'titles' => get_salutations());
    }

    // AJAX FUNCTIONS

    public function email_suggest() {

        $this->load->model('users/user_contact_model');
        $term = $this->input->post('term');

        // Look up email address
        $this->db->select('contact');
        $this->db->limit(50);
        $user_contacts = $this->user_contact_model->get(array('type' => USERS_CONTACT_TYPE_EMAIL, 'contact LIKE ' => "$term%"));
        $emails = array();

        if (!empty($user_contacts)) {
            foreach ($user_contacts as $user_contact) {
                $emails[] = $user_contact->contact;
            }
        }
        echo json_encode($emails);
    }

    public function get_user_details() {

        $this->load->model('company_model');
        $this->load->model('company_address_model');

        $email = $this->input->post('email');

        if ($user_id = $this->user_model->already_exists($email, false)) {
            // Note that, for security reasons, the password is not sent here, otherwise it would be shown in clear for users others than the user logged in.
            // The password field is only for creating a new password for a new user
            $values = $this->user_model->get_values($user_id);
            if (empty($values['company_code'])) {
                $values['company_code'] = null;
            }
            echo json_encode($values);
        }
    }

    public function company_code_check() {

        $this->load->model('company_model');
        $code = $this->input->post('code');

        // Look up company code in DB
        $this->db->select('name');
        $company = $this->company_model->get(array('code' => $code), true);

        if (!empty($company)) {
            echo json_encode($company->name);
        }
    }

}
?>
