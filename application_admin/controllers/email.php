<?php
/**
 * Contains the Email Controller class
 * @package controllers
 */

/**
 * Email Controller class
 * @package controllers
 */
class Email extends MY_Controller {
    function __construct() {
        parent::__construct();

        $this->load->model('users/user_contact_model');
        $this->load->model('country_model');
    }

    /**
     * @param int $user_id Will prefill the "to" field with the user's main email address
     * @param string $enquiry_id Will prefill the subject of the email
     */
    function index($user_id=null, $enquiry_id=null) {

        $this->load->model('enquiries/enquiry_model');
        $this->load->model('enquiries/outbound_quotation_model');
        $this->load->library('CKeditor');

        require_capability('site:sendemails');

        $defaults = array('body' => '');
        $staff = $this->user_model->get($this->session->userdata('user_id'));
        $staff->name = $this->user_model->get_name($staff, 'l f');
        $staff->email = $this->user_contact_model->get_by_user_id($staff->id, USERS_CONTACT_TYPE_EMAIL, true, true);

        $user_id = (is_null($user_id)) ? $this->input->post('user_id') : $user_id;
        $enquiry_id = (is_null($enquiry_id)) ? $this->input->post('enquiry_id') : $enquiry_id;

        $quotation_id = $this->input->post('quotation_id');
        $formdata = $this->input->post('formdata');
        $error = $this->input->post('error');

        if (!empty($user_id)) {
            $user = $this->user_model->get($user_id);
            if (empty($user)) {
                echo "There is no record in the database for user_id: {$user_id}";
            } else {
                $defaults['to'] = $this->user_contact_model->get_by_user_id($user_id, USERS_CONTACT_TYPE_EMAIL, true, true) . ', ';

                if (!empty($enquiry_id)) {
                    $defaults['subject'] = "RE: Enquiry " . $enquiry_id;
                } elseif (!empty($quotation_id)) {
                    $defaults['subject'] = 'RE: Quotation ' . $quotation_id;
                }

                $defaults['body'] = "Dear {$this->user_model->get_name($user_id)},";
            }
        }

        // PROCESS EMAIL SUBMISSION
        if (!empty($formdata)) {
            $this->send();
        }

        if ($error) {
            add_message(unserialize($error), 'error');
        }

        $adminusers = $this->user_model->get_users_by_capability('enquiries:receiveemailsasadmin');
        $allstaff_options = '<option value="0">-- Add an Admin&#145;s address --</option>';
        $admin_emails = array();

        foreach ($adminusers as $thisuser) {
            $email = $this->user_contact_model->get_by_user_id($thisuser->id, USERS_CONTACT_TYPE_EMAIL, true, true);

            if (!empty($email) && $thisuser->status != 'Suspended') {
                $allstaff_options .= '<option value="'.$email.'">'.$this->user_model->get_name($thisuser, 'l f')."</option>\n";
                $admin_emails[$thisuser->id] = $email;
            }
        }

        $where_conditions = array('users.status' => 'Active', 'disabled' => 0);
        $enquirers = $this->user_model->get_users_by_capability('enquiries:writeenquiries', array('enquiries:viewenquiries'), $where_conditions);
        $allenquirers_options = '<option value="0">-- Add an Enquirer&#145;s address --</option>';
        $enquirer_emails = array();

        foreach ($enquirers as $enquirer) {
            $email = $this->user_contact_model->get_by_user_id($enquirer->id, USERS_CONTACT_TYPE_EMAIL, true, true);
            if (!empty($email)) {
                $allenquirers_options .= '<option value="'.$email.'">'.$this->user_model->get_name($enquirer, 'l f')."</option>\n";
                $enquirer_emails[$enquirer->id] = $email;
            }
        }

        $this->db->where_not_in('status', array(ENQUIRIES_ENQUIRY_STATUS_DECLINED, ENQUIRIES_ENQUIRY_STATUS_ARCHIVED));
        $enquiries = $this->enquiry_model->get();
        $enquiry_options = $this->enquiry_model->get_dropdown();

        $enquirer_emails_js = '';
        foreach($enquirer_emails as $user_id => $email) {
            $enquirer_emails_js .= "{email: '$email', user_id: $user_id},";
        }
        $enquirer_emails_js = substr($enquirer_emails_js, 0, -1);

        $title_options = array('title' => 'Email form',
                               'help' => 'Use this form to email customers or other people. Selected admins will receive a copy of this email.',
                               'expand' => 'entry_form',
                               'icons' => array());

        $pageDetails = array('title' => 'Email form',
                             'section_title' => get_title($title_options),
                             'content_view' => 'email/send',
                             'enquirer_emails_js' => $enquirer_emails_js,
                             'admin_emails' => $admin_emails,
                             'staff' => $staff,
                             'user_id' => $user_id,
                             'enquiry_id' => $enquiry_id,
                             'allstaff_options' => $allstaff_options,
                             'allenquirers_options' => $allenquirers_options,
                             'enquiry_options' => $enquiry_options,
                             'defaults' => $defaults,
                             'enquirer_emails' => $enquirer_emails,
                             'jstoloadinfooter' => array('jquery/jquery.json')
                             );

        $this->load->view('template/default', $pageDetails);
    }

    function send() {

        $this->load->library('email');
        $this->load->helper('regexp');

        $enquiry_id = $this->input->post('enquiry_id');
        $message = $this->input->post('body');
        $subject = $this->input->post('subject');
        $user_id = $this->input->post('user_id');

        $this->email->message($message);
        $this->email->from($this->input->post('from'), $this->input->post('fromname'));
        $this->email->to($this->input->post('to'));
        $this->email->cc($this->input->post('cc'));
        $this->email->bcc($this->input->post('bcc'));

        if (!empty($enquiry_id)) {
            // Record email sending as an enquiry note
            $note = 'Note copied from sent email'."\n";

            // Strip signature
            if ($sigpos = strpos($message, '------')) {
                $note .= substr($message, 0, $sigpos);
            } else {
                $note .= $message;
            }

            $this->load->model('enquiries/enquiry_note_model');
            $this->load->model('enquiries/enquiry_model');
            $this->load->helper('date');
            $enquiry = $this->enquiry_model->get($enquiry_id);
            $this->enquiry_note_model->add(array('enquiry_id' => $enquiry_id, 'user_id' => $this->session->userdata('user_id'), 'message' => $note, 'type' => 'staff'));
            $subject .= " $enquiry_id (" . mdate('%d/%m/%Y', $enquiry->creation_date) . ')';
        }

        $this->email->subject($subject);

        // Add attachments
        $attached_files = array();
        if (!empty($_FILES['attachments'])) {
            $this->load->helper('file');

            $filecount = count($_FILES['attachments']['name']);
            for ($i = 0; $i < $filecount; $i++) {
                // Save the file on disk under its real name
                $filepath = $this->config->item('files_path').$_FILES['attachments']['name'][$i];
                write_file($filepath, read_file($_FILES['attachments']['tmp_name'][$i]));
                $this->email->attach($filepath);
                $attached_files[] = $filepath;
            }
        }

        if ($this->email->send()) {
            add_message('Email sent successfully!', 'success');
            foreach ($attached_files as $file) {
                unlink($file);
            }
        } else {
            add_message('Error sending email!', 'error');
        }

        redirect('email/index/'.$user_id);
    }

    function fetch_user_emails($country_id) {

        if (IS_AJAX) {
            $users = $this->user_model->get_by_country_id($country_id);
            echo json_encode($users);
        }
    }

    function save_csv_email_list() {

        $userids = json_decode($this->input->post('userids'));
        $csvfile = fopen($this->config->item('files_path').'enquirers.csv', 'w+');

        fputcsv($csvfile, array('First Name', 'Last Name', 'Email address', 'Country', 'Company Name'));

        foreach ($userids as $user_id) {
            $user = $this->user_model->get_for_csv($user_id);
            fputcsv($csvfile, array($user->first_name, $user->surname, $user->email, $user->country, $user->company_name));
        }
        fclose($csvfile);
        echo json_encode($userids);
    }

    function download_csv() {

        $this->load->helper('file');
        $fullpath = $this->config->item('files_path').'enquirers.csv';
        $mimetype = get_mime_by_extension($fullpath);
        header("Content-type:$mimetype");
        header("Content-Disposition:attachment;filename=email_list.csv");
        readfile($fullpath);
    }
}
