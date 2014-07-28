<?php
/**
 * Contains the Enquiry Controller class
 * @package controllers
 */

/**
 * Enquiry Controller class
 * @package controllers
 */
class Enquiry extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('enquiries/enquiry_model');
        $this->config->set_item('replacer', array('enquiries' => array('enquiry|Enquiries')));
        $this->config->set_item('exclude', array('browse'));
    }

    function index() {
        redirect('enquiries/enquiry/browse');
    }

    function assigned_enquiries() {
        return $this->browse('html', $this->session->userdata('user_id'));
    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     * @param string $outputtype
     * @param int $assigned_user_id If given, will filter the enquiries to show only those assigned to the user_id
     */
    function browse($outputtype='html', $assigned_user_id=null) {
        if (!has_capability('enquiries:viewassignedenquiries')) {
            require_capability('enquiries:viewenquiries');
        } else {
            require_capability('enquiries:viewassignedenquiries');
        }

        $this->load->library('filter');

        $staff_list = $this->user_model->get_users_by_capability('enquiries:assignabletoenquiries');
        $staff_array = array(null => '-- Select a Staff --');
        foreach ($staff_list as $staff) {
            $staff_array[$staff->id] = $this->user_model->get_name($staff->id, 'l f');
        }

        $this->filter->add_filter('combo', 'combo', array('enquiries_id' => 'Ref',
                                                          'enquiries_status|ENQUIRIES_ENQUIRY_STATUS' => 'Status',
                                                          'enquiries_priority|ENQUIRIES_ENQUIRY_PRIORITY' => 'Priority',
                                                          'country' => 'Country',
                                                          'enquiries_creation_date' => 'Date created',
                                                          'enquiries_due_date' => 'Date due',
                                                          'company' => 'Enquirer',
                                                          'product' => 'Product'));
        $this->filter->add_filter('checkbox', ENQUIRIES_ENQUIRY_STATUS_ARCHIVED, 'ARCHIVED', 'archivedstatus', 'enquiries_status');
        $this->filter->add_filter('checkbox', ENQUIRIES_ENQUIRY_STATUS_ORDERED, 'CUSTOMER ORDERED', 'customerorderedstatus', 'enquiries_status');
        $this->filter->add_filter('checkbox', ENQUIRIES_ENQUIRY_STATUS_DECLINED, 'DECLINED', 'declinedstatus', 'enquiries_status', true);
        $this->filter->add_filter('dropdown', get_constant_dropdown('ENQUIRIES_ENQUIRY_PRIORITY', true), 'Priority', 'enquiries_priority', null);
        $this->filter->add_filter('dropdown', $staff_array, 'Staff', 'staff_id', null);

        $total_records = $this->enquiry_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;
        $notes = false;

        if (!IS_AJAX && $outputtype == 'html') {
            $limit = 1;
        } else if ($outputtype == 'html') {
            $notes = true;
        }

        $datatable_params = parent::process_datatable_params($outputtype, array('pdf'));

        if ($outputtype != 'html') {
            $limit = $datatable_params['perpage'];
        }

        $table_data = $this->enquiry_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit, $notes, $assigned_user_id, $outputtype);

        if ($outputtype == 'html') {
            $additional_capabilities = array('edit' => 'enquiries:editassignedenquiries', 'pdf' => 'enquiries:viewassignedenquiries');
            $table_data = parent::add_action_column('enquiries', 'enquiry', $table_data, array('edit', 'pdf', 'delete'), $additional_capabilities);
            $pageDetails = parent::get_ajax_table_page_details('enquiries', 'enquiry', $table_data['headings']);
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {
            unset($table_data['headings']['notes']);

            $pageDetails = parent::get_export_page_details('enquiry', $table_data);
            $pageDetails['widths'] = array(125, 240, 155, 300, 200, 200, 248, 365, 325);

            parent::output_for_export('enquiries', 'enquiry', $outputtype, $pageDetails);
        }
    }

    /**
     * Editing an enquiry
     * @param int $enquiry_id
     */
    public function edit($enquiry_id) {

        // Breadcrumb
        $this->config->set_item('replacer', array('enquiries' => array('enquiry|Enquiries'), 'edit' => 'Edit enquiry ' . $enquiry_id));

        $enquiry_id = (int) $enquiry_id;
        if (!$this->_can_edit($enquiry_id)) {
            return null;
        }

        $this->load->model('enquiries/enquiry_file_model');
        $this->load->model('enquiries/enquiry_staff_model');
        $this->load->model('country_model');
        $this->load->helper('form_template');
        $this->load->helper('date');

        $staff_users = $this->user_model->get_users_by_capability('enquiries:assignabletoenquiries');
        $staff_list = array();
        foreach ($staff_users as $user) {
            $staff_list[$user->id] = $user->surname . ' ' . $user->first_name;
        }
        asort($staff_list);

        $enquiry_data = $this->enquiry_model->get_values($enquiry_id);
        $enquiry_data['address_country_name'] = (!empty($enquiry_data['address_country_id'])) ? $this->country_model->get_name($enquiry_data['address_country_id']) : '';
        $enquiry_data['enquiry_creation_date'] = unix_to_human($enquiry_data['enquiry_creation_date']);
        $files = $this->enquiry_file_model->get(array('document_id' => $enquiry_id, 'document_type' => FILE_TYPE_ENQUIRY));

        if (is_null($files)) {
            $files = array();
        }

        // Set due date default to today if not already set
        $due_date = 'Not set';
        if (empty($enquiry_data['enquiry_due_date']) && has_capability('enquiries:doanything')) {
            $due_date = mktime();
        } else {
            $due_date = $enquiry_data['enquiry_due_date'];
        }

        $due_date = unix_to_human($due_date);
        $enquiry_data['enquiry_due_date'] = $due_date;
        $enquiry_data['enquiry_staff[]'] = $this->enquiry_model->get_assigned_staff($enquiry_id);
        form_element::$default_data = $enquiry_data;

        // Set up title bar
        $title_options = array('title' => "Edit enquiry $enquiry_id",
                               'help' => "Edit enquiry $enquiry_id",
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array(
            'title' => 'Edit Enquiry',
            'section_title' => get_title($title_options),
            'csstoload' => array(),
            'jstoloadinfooter' => array('jquery/pause', 'jquery/jquery.json', 'jquery/jquery.urlparser', 'application/enquiries/enquiry_edit'),
            'content_view' => 'enquiries/enquiry/edit',
            'enquiry_data' => $enquiry_data,
            'enquiry_notes' => $this->enquiry_model->get_formatted_notes($enquiry_id),
            'staff_list' => $staff_list,
            'files' => $files,
            'dropdowns' => $this->get_dropdowns(),
            'user_id' => $this->session->userdata('user_id'),
            'next_id' => $this->enquiry_model->get_neighbour_enquiry($enquiry_id, 'next'),
            'previous_id' => $this->enquiry_model->get_neighbour_enquiry($enquiry_id, 'previous')
            );

        $this->load->view('template/default', $pageDetails);
    }

    /**
     * @TODO refactor most of this into MY_Controller
     * @TODO add AJAX upload functionality
     */
    function process_edit() {
        $this->load->model('enquiries/enquiry_product_model');
        $this->load->model('enquiries/enquiry_staff_model');
        $this->load->helper('date');

        $enquiry_id = (int) $this->input->post('enquiry_id');
        if (!$this->_can_edit($enquiry_id)) {
            return null;
        }

        $redirect_url = 'enquiries/enquiry/edit/'.$enquiry_id;

        // Determine next page based on submit button clicked
        $target_enquiry = $enquiry_id;
        if ($this->input->post('next')) {
            if (!$target_enquiry = $this->enquiry_model->get_neighbour_enquiry($enquiry_id, 'next')) {
                add_message('You are already at the last enquiry!', 'warning');
            }
        } else if ($this->input->post('previous')) {
            if (!$target_enquiry = $this->enquiry_model->get_neighbour_enquiry($enquiry_id, 'previous')) {
                add_message('You are already at the first enquiry!', 'warning');
            }
        }

        if (!$target_enquiry) {
            $target_enquiry = $enquiry_id;
        }


        $this->form_validation->set_rules('enquiry_product_title', 'Product Title', 'trim|required');
        $this->form_validation->set_rules('enquiry_product_description', 'Product description', 'trim|required');
        $this->form_validation->set_rules('enquiry_min_annual_qty', 'Minimum annual qty', 'trim|required');
        $this->form_validation->set_rules('enquiry_shipping', 'Shipping Method', 'trim|required');
        $this->form_validation->set_rules('enquiry_country_id', 'Delivery Country', 'trim|required');
        $this->form_validation->set_rules('enquiry_currency', 'Currency', 'trim|required');

        $success = $this->form_validation->run();

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "Enquiry $enquiry_id has been successfully updated!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            return $this->edit($enquiry_id);
        }

        // Process the enquiry data
        $due_date = human_to_unix($this->input->post('enquiry_due_date'));

        if (!$this->enquiry_model->edit_from_post($enquiry_id, 'enquiry_', array('due_date' => $due_date))) {
            add_message('Could not update this enquiry!', 'error');
            redirect($redirect_url);
        }

        // Product
        if (!$this->enquiry_product_model->edit_from_post($this->input->post('enquiry_product_id'), 'enquiry_product_')) {
            add_message('An error occurred, preventing this enquiry\'s product from being updated.', 'error');
            redirect($redirect_url);
        }

        // Files

        // Record file in DB
        if (!empty($_FILES['enquiry_file']['name'])) {
            if (!$this->_process_file('enquiry_file', $enquiry_id)) {
                $errors = true;
                redirect('enquiries/enquiry/edit/'.$target_enquiry);
            }
        }

        // Staff assignments
        if (has_capability('enquiries:assignstafftoenquiries')) {
            $assigned_staff = $this->enquiry_model->get_assigned_staff($enquiry_id);
            $new_assigned_staff = $this->input->post('enquiry_staff');
            if ($assigned_staff != $new_assigned_staff) {
                if (count($new_assigned_staff) > 5) {
                    add_message('You can only assign a maximum of 5 staff to each enquiry.', 'warning');
                    redirect($redirect_url);
                }

                // Delete existing assignments
                $this->db->delete('enquiries_enquiry_staff', array('enquiry_id' => $enquiry_id));

                // Record new assignments
                if (!empty($new_assigned_staff)) {
                    $this->load->library('email');
                    $this->load->model('users/user_model');
                    $this->load->model('users/user_contact_model');

                    foreach ($new_assigned_staff as $user_id) {
                        $this->enquiry_staff_model->add(array('enquiry_id' => $enquiry_id, 'user_id' => $user_id));

                        // Notify staff of new assignment if not already assigned
                        if (!in_array($user_id, $assigned_staff)) {
                            $loggedin_user = $this->user_model->get_name($this->session->userdata('user_id'));
                            $this->email->from(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
                            $this->email->subject('ChinaSavvy website enquiry assignment');
                            $this->email->to($this->user_contact_model->get_by_user_id($user_id, USERS_CONTACT_TYPE_EMAIL, true, true, true));
                            $message = $this->user_model->get_name($user_id) . ",
$loggedin_user has assigned you the task of sourcing information for enquiry Ref. $enquiry_id.

Please read the following instructions from ChinaSavvy concerning this enquiry:

" . $this->enquiry_model->get_formatted_notes($enquiry_id, null, true) . "

";
                            $this->email->message($message);
                            $this->email->send();
                            // echo $this->email->print_debugger();
                        }
                    }
                }
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("Enquiry $enquiry_id has been successfully updated!", 'success');
        redirect('enquiries/enquiry/edit/'.$target_enquiry);
    }

    function _process_file($param_name, $enquiry_id) {
        // Set write permissions for uploaded files
        // Recomended by Paul
        $umask = umask(octdec('0007'));

        $config = array();
        $config['upload_path'] = $this->config->item('files_path').'enquiries/enquiry/'.$enquiry_id;

        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path']);
        }
        // $config['allowed_types'] = ENQUIRIES_UPLOAD_ALLOWED_TYPES;
        $config['allowed_types'] = '*';
        $config['encrypt_name'] = true;
        $this->load->library('upload', $config);

        $success = $this->upload->do_upload($param_name);
        umask($umask);

        if ( ! $success) {
            echo $this->upload->display_errors();
            return false;
        }

        $file_data = $this->upload->data();

        $this->load->model('enquiries/enquiry_file_model');
        $new_file = array('document_type' => FILE_TYPE_ENQUIRY,
                          'document_id' => $enquiry_id,
                          'filename_original' => $file_data['orig_name'],
                          'filename_new' => $file_data['file_name'],
                          'raw_name' => $file_data['raw_name'],
                          'location' => "enquiry/$enquiry_id/",
                          'file_type' => $file_data['file_type'],
                          'file_extension' => $file_data['file_ext'],
                          'file_size' => $file_data['file_size'],
                          'is_image' => $file_data['is_image'],
                          'image_width' => $file_data['image_width'],
                          'image_height' => $file_data['image_height'],
                          'image_type' => $file_data['image_type'],
                          'image_size_str' => $file_data['image_size_str']);
        if (!$file_id = $this->enquiry_file_model->add($new_file)) {
            add_message('The file was uploaded, but the file info could not be recorded in the database...', 'warning');
            redirect($redirect_url);
        } else {
            return true;
        }
    }

    /**
     * Determines whether the logged in user can edit a given enquiry
     * @param int $enquiry_id
     * @return bool
     */
    function _can_edit($enquiry_id) {
        if (!has_capability('enquiries:editassignedenquiries')) {
            require_capability('enquiries:editenquiries');
        } else if (!has_capability('enquiries:editenquiries')) {
            require_capability('enquiries:editassignedenquiries');
            // Check that this enquiry is actually assigned to the current user
            $current_user = $this->session->userdata('user_id');
            $this->load->model('enquiries/enquiry_staff_model');

            if (!$this->enquiry_staff_model->is_assigned($current_user, $enquiry_id)) {
                $pageDetails = array('title' => 'Unauthorised access to enquiry', 'content_view' => 'enquiries/enquiry/unauthorised', 'enquiry_id' => $enquiry_id);
                $this->load->view('template/default', $pageDetails);
                return false;
            }
        } else {
            require_capability('enquiries:editenquiries');
        }
        return true;
    }

    /**
     * Deletes a file, then redirects to the edit page of its associated enquiry
     * @param int $file_id
     */
    function delete_file($file_id) {
        require_capability('enquiries:deletefiles');
        $this->load->model('enquiries/enquiry_file_model');
        $file = $this->enquiry_file_model->get($file_id);

        if (!is_object($file)) {
            $this->session->set_flashdata('message', "File $file_id does not exist. Perhaps you have already deleted it?");
            redirect('enquiries/enquiry/browse');
        }

        $success = $this->enquiry_file_model->delete($file_id);

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->type = 'success';
                $json->message = "File $file->filename_original has been successfully deleted";
            } else {
                $json->type = 'error';
                $json->message = "File $file_id could not be deleted";
            }

            echo json_encode($json);
            return null;
        } else {
            if ($success) {
                $this->session->set_flashdata('message', "File $file->filename_original has been successfully deleted");
            } else {
                $this->session->set_flashdata('message', "File $file_id could not be deleted");
            }
            redirect('enquiries/enquiry/edit/'.$file->document_id);
        }
    }

    /**
     * Forces download of a given file (stays on the enquiry edit page)
     * @param int $file_id
     */
    function download_file($file_id) {
        require_capability('enquiries:viewfiles');
        $this->load->helper('download');
        $this->load->model('enquiries/enquiry_file_model');
        $file = $this->enquiry_file_model->get($file_id);

        $data = file_get_contents($this->config->item('files_path').'enquiries/'.$file->location.$file->filename_new);
        force_download($file->filename_original, $data);
    }

    /**
     * Appends a note to an enquiry, then echoes the formatted notes. To be used only with AJAX calls
     */
    function append_note() {
        if (!IS_AJAX) {
            return null;
        }

        $this->load->model('enquiries/enquiry_note_model');
        $this->load->library('email');

        $enquiry_id = $this->input->post('enquiry_id');
        $user_id = $this->input->post('user_id');
        $note = $this->input->post('note');

        $note_data = array('message' => $note, 'user_id' => $user_id, 'enquiry_id' => $enquiry_id);

        if ($note_id = $this->enquiry_note_model->add($note_data)) {

            $query = $this->db->from('enquiries_enquiry_staff')->where('enquiry_id', $enquiry_id)->get();
            $staff_list = array();

            foreach ($query->result() as $row) {
                $staff_list[] = $row->user_id;
            }

            foreach ($staff_list as $staff_id) {
                $staff = $this->user_model->get($staff_id);

                $this->email->from(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
                $this->email->subject('ChinaSavvy website enquiry notes update');
                $this->email->to($this->user_contact_model->get_by_user_id($staff_id, USERS_CONTACT_TYPE_EMAIL, true, true, true));
                $message = $this->user_model->get_name($staff_id) . ",
The instructions for enquiry $enquiry_id have been updated:

" . $this->enquiry_model->get_formatted_notes($enquiry_id, null, true) . "

Please log into the ChinaSavvy Admin panel to view the details:
https://admin.chinasavvy.com/enquiries/enquiry/edit/$enquiry_id

ChinaSavvy Administration
";
                $this->email->message($message);
                $this->email->send();
            }

            echo nl2br($this->enquiry_model->get_formatted_notes($enquiry_id));
        }
    }

    public function add($user_id=false) {
        require_capability('enquiries:writeenquiries');
        $this->load->driver('cache');

        // Breadcrumb
        $this->config->set_item('replacer', array('enquiries' => array('enquiry|Enquiries'), 'add' => 'Create a new enquiry'));

        $this->load->helper('form_template');
        $this->load->helper('date');
        $this->load->helper('dropdowns');
        $this->load->model('country_model');

        // An optional user_id can be passed to this page to pre-fill company and enquirer details
        if ($user_id) {
            $userdata = $this->user_model->get_values($user_id);
            form_element::set_default_data($userdata + array('company_name_prefill' => $user_id));
        }



        // Set up title bar
        $title_options = array('title' => "New enquiry",
                               'help' => "New enquiry",
                               'expand' => 'page',
                               'icons' => array());


        $pageDetails = array('title' => 'New enquiry',
                             'section_title' => get_title($title_options),
                             'content_view' => 'enquiries/enquiry/add',
                             'next_id' => $this->enquiry_model->get_next_id(),
                             'dropdowns' => $this->get_dropdowns());

        $this->load->view('template/default', $pageDetails);

    }

    public function process_add() {
        require_capability('enquiries:writeenquiries');
        $this->load->model('enquiries/enquiry_product_model');

        $user_id = $this->input->post('company_name_prefill');

        // An optional user_id can be passed to this page to pre-fill company and enquirer details
        if ($user_id) {
            $userdata = $this->user_model->get_values($user_id);
            foreach ($userdata as $key => $val) {
                $this->form_validation->set_rules($key);
                if (empty($_POST[$key])) {
                    $this->form_validation->override_field_data($key, $val);
                }
            }
        }

        foreach ($_POST as $key => $val) {
            $this->form_validation->set_rules($key);
            if (!empty($val)) {
                $this->form_validation->override_field_data($key, $val);
            }
        }

        $redirect_url = 'enquiries/enquiry/add/'.$user_id;

        $required_fields = array(
                                 'company_name' => 'Company Name',
                                 'company_company_type' => 'Company Type',
                                 'address_address1' => 'Address 1',
                                 'address_city' => 'City',
                                 'address_state' => 'State/Province/County',
                                 'address_postcode' => 'Postcode',
                                 'address_country_id' => 'Country',
                                 'user_salutation' => 'Title',
                                 'user_first_name' => 'First Name',
                                 'user_surname' => 'Last Name',
                                 'user_phone' => 'Telephone',
                                 'user_email' => 'Email',
                                 'enquiry_product_title' => 'Product Title',
                                 'enquiry_product_description' => 'Product Description',
                                 'enquiry_min_annual_qty' => 'Minimum Annual Qty',
                                 'enquiry_shipping' => 'Shipping Method',
                                 'enquiry_country_id' => 'Delivery Country',
                                 'enquiry_currency' => 'Currency');

        if (has_capability('enquiries:assignstafftoenquiries')) {
            $required_fields['enquiry_priority'] = 'Priority';
        }

        foreach ($required_fields as $name => $label) {
            $this->form_validation->set_rules($name, $label, 'trim|required');
        }

        $success = $this->form_validation->run();
        $errors = !$success;

        $company_id = $this->_process_company($errors);
        $contact_id = $this->_process_contact($company_id, $errors);
        $product_id = $this->_process_product($errors);
        $enquiry_id = $this->_process_enquiry($contact_id, $product_id, $errors);

        if (!$errors) {
            for ($i = 1; $i <= 5; $i++) {
                if (!empty($_FILES['enquiry_file_'.$i]['name'])) {
                    if (!$this->_process_file('enquiry_file_'.$i, $enquiry_id)) {
                        $errors = true;
                    }
                }
            }
        }

        if ($errors) {
            return $this->add($user_id);
        } else {
            add_message('The enquiry was successfully recorded!');
            redirect('enquiries/enquiry/edit/'.$enquiry_id);
        }
    }

    /**
     * Processes company data from a new enquiry post, inserts or updates company in DB, then returns company id
     * @return int
     */
    private function _process_company(&$errors=false) {
        if ($errors) {
            return false;
        }

        $this->load->model('company_model');
        $this->load->model('company_address_model');

        $company_id = false;
        $company = array('name' => $this->input->post('company_name'));

        // Check the user email to see if we are dealing with a new user/company or an existing one. Email addresses MUST be unique for this to work!
        if ($user_id = $this->user_model->already_exists($this->input->post('user_email'))) {  // The user already exists: update the company details, including address
            $user = $this->user_model->get($user_id);

            if (empty($user->company_id)) {
                // Try to find the company by name, then associate this user to it
                if ($existing_company = $this->company_model->get(array('name' => $company['name']), true)) {
                    $user->company_id = $existing_company->id;
                    $this->user_model->edit($user_id, array('company_id' => $existing_company->id));
                    add_message('This email address already identifies a user, '
                            . 'but that user had no associated company. He/she has now been associated with ' . $existing_company->name, 'warning');
                } else {
                    add_message('This email address already identifies a user, but '
                            . 'that user has no associated company, and could not be associated with any existing company by the name of ' . $company->name, 'error');
                    $errors = true;
                    return false;
                }
            }

            $result = $this->company_model->edit_from_post($user->company_id, 'company_', array('role' => COMPANY_ROLE_ENQUIRER));

            if ($result && !$errors) {
                // Update address
                if (!$this->company_address_model->update_from_formdata($_POST, $user->company_id)) {
                    add_message('Error recording the company address!', 'error');
                    $errors = true;
                }
            } else {
                add_message('Error recording the company info!', 'error');
                $errors = true;
            }

            $company_id = $user->company_id;

        } else { // The user does not exist
            $new_company_name = $company['name'];

            // company exists but user is new
            if ($existing_company = $this->company_model->get(array('name' => $company['name']), true)) {

                // If the address is the same: we don't need to create a new company: This is just an additional user for an existing company, having the same address is OK
                if ($this->company_model->is_same_address($existing_company->id, $_POST)) {
                    return $existing_company->id;
                }
                // If the address is different, create a new company with a different name
                else {
                    $new_company_name = $company['name'] . ' ' . substr(sha1(microtime()), 0, 4);
                }
            }

            $company_id = $this->company_model->add(array(
                    'name' => $new_company_name,
                    'url' => $this->input->post('company_url'),
                    'role' => COMPANY_ROLE_ENQUIRER,
                    'company_type' => $this->input->post('company_company_type')
                    ));

            if ($company_id) {
                // Update address
                if (!$this->company_address_model->update_from_formdata($_POST, $company_id)) {
                    add_message('Error recording the company address!', 'error');
                    $errors = true;
                }
            } else {
                add_message('Error recording the company info!', 'error');
                $errors = true;
            }
        }

        return $company_id;
    }

    /**
     * Processes user data from a new enquiry post, inserts or updates user in DB, then returns user id
     * @return int
     */
    private function _process_contact($company_id, &$errors=false) {
        if ($errors) {
            return false;
        }

        $this->load->model('users/user_contact_model');

        $contact_id = false;

        if (empty($company_id)) {
            $errors = true;
            return false;
        }

        // First, determine if the user is new
        if (!$errors) {
            $contact_id = null;

            $user_email = $this->input->post('user_email');

            if (empty($user_email)) {
                add_message('No user email address given!', 'error');
                $errors = true;
                return false;
            }

            // Check if email address already exists
            $is_new_user = true;

            $user_contact = $this->user_contact_model->get(array('type' => USERS_CONTACT_TYPE_EMAIL, 'default_choice' => 1, 'contact' => $user_email), true);
            if ($user_contact) {
                $is_new_user = false;

                // So the email address exists. Is the associated user still in DB?
                if ($user = $this->user_model->get($user_contact->user_id)) { // yes, use that user_id and update its company_id
                    $contact_id = $user->id;
                    $this->user_model->edit($user->id, array('company_id' => $company_id));
                } else { // No, delete the email from DB
                    $is_new_user = true;
                    $this->user_contact_model->delete($user_contact->id);
                }
            }
        }

        $first_name = $this->input->post('user_first_name');
        $surname = $this->input->post('user_surname');
        $salutation = $this->input->post('user_salutation');

        // Now insert or update user record
        if (!$errors) {
            if ($is_new_user) {

                // Check if a user with the same first name, last name and salutation exists
                $existing_user = $this->user_model->get(array('first_name' => $first_name, 'surname' => $surname, 'salutation' => $salutation), true);
                if ($existing_user) {

                    // Do we have an existing email address for this user? If so, change it to the new one
                    $user_contact = $this->user_contact_model->get(array('type' => USERS_CONTACT_TYPE_EMAIL, 'user_id' => $existing_user->id, 'default_choice' => 1), true);
                    if ($user_contact) {
                        $this->user_contact_model->edit($user_contact->id, array('contact' => $user_email));
                        $contact_id = $existing_user->id;
                    }
                }

                if (empty($contact_id)) {
                    $new_user = array('first_name' => $this->input->post('user_first_name'),
                                      'surname' => $this->input->post('user_surname'),
                                      'company_id' => $company_id,
                                      'salutation' => $this->input->post('user_salutation'));
                    $contact_id = $this->user_model->add($new_user);
                    $this->user_model->assign_contact_data($contact_id, 'user_', $_POST);
                    $enquirer_role = $this->role_model->get(array('name' => 'Chinasavvy enquirer'), true);
                    $contact_role = $this->role_model->get(array('name' => 'Corporate contact'), true);
                    $this->user_model->assign_role($contact_id, $enquirer_role->id);
                    $this->user_model->assign_role($contact_id, $contact_role->id);
                }
            }
        }

        if (empty($contact_id)) {
            add_message('User was not recorded!', 'error');
            $errors = true;
            return false;
        }

        // Check that the user has been recorded correctly
        $recorded_user = $this->user_model->get($contact_id);
        if (empty($recorded_user->company_id)) {
            add_message('User was recorded without a company id! Deleting user now!', 'error');
            $this->user_model->delete($contact_id);
            $errors = true;
            return false;
        }

        return $contact_id;
    }

    /**
     * Processes enquiry product data from a new enquiry post, inserts or updates enquiry product in DB, then returns enquiry product id
     * @return int
     */
    private function _process_product(&$errors=false) {
        if (!$errors) {
            if ($product_id = $this->enquiry_product_model->add_from_post('enquiry_product_')) {
                return $product_id;
            } else {
                add_message('Could not record the enquiry product info!', 'error');
                $errors = true;
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Processes enquiry data from a new enquiry post, inserts or updates enquiry in DB, then returns enquiry id
     * @return int
     */
    private function _process_enquiry($user_id, $enquiry_product_id, &$errors=false) {
        if (!$errors) {
            $data = array(
                    'user_id' => $user_id,
                    'enquiry_product_id' => $enquiry_product_id,
                    'status' => ENQUIRIES_ENQUIRY_STATUS_PENDING,
                    'domain' => $_SERVER['SERVER_NAME']
                    );

            if ($enquiry_id = $this->enquiry_model->add_from_post('enquiry_', $data)) {
                // Record staff notes if sent
                $notes = $this->input->post('enquiry_notes');
                if (!empty($notes)) {
                    $this->load->model('enquiries/enquiry_note_model');
                    $note_id = $this->enquiry_note_model->add(array('enquiry_id' => $enquiry_id, 'user_id' => $user_id, 'message' => $notes));
                }
                return $enquiry_id;
            } else {
                add_message('Could not record the enquiry info!', 'error');
                $errors = true;
                return false;
            }
        } else {
            return false;
        }
    }

    private function get_dropdowns() {
        $this->load->model('country_model');
        $this->load->helper('dropdowns');
        $this->enquiry_model->add_cache_key('enquirers');

        $dropdowns = array(
            'priorities' => get_or_save_cached('priorities', 'get_constant_dropdown', array('ENQUIRIES_ENQUIRY_PRIORITY'), null, false),
            'company_types' => get_or_save_cached('company_types', 'get_constant_dropdown', array('COMPANY_TYPE'), null, false),
            'salutations' => get_or_save_cached('salutations', 'get_salutations', array(), null, false),
            'statuses' => get_or_save_cached('statuses', 'get_constant_dropdown', array('ENQUIRIES_ENQUIRY_STATUS'), null, false),
            'countries' => get_or_save_cached('countries', 'get_dropdown', array(), $this->country_model, false),
            'shipping_methods' => get_or_save_cached('shipping_methods', 'get_constant_dropdown', array('ENQUIRIES_SHIPPING'), null, false),
            'delivery_terms' => get_or_save_cached('delivery_terms', 'get_constant_dropdown', array('ENQUIRIES_ENQUIRY_DELIVERY'), null, false),
            'currencies' => get_or_save_cached('currencies', 'get_constant_dropdown', array('CURRENCY'), null, false),
            'sources' => get_or_save_cached('sources', 'get_constant_dropdown', array('ENQUIRIES_SOURCE', true, true), null, false),
            'enquirers' => get_or_save_cached('enquirers', 'get_potential_enquirers', array(), $this->enquiry_model), false);

        return $dropdowns;
    }
}
?>
