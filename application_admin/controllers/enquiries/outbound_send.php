<?php
/**
 * Contains the Outbound_send Controller class
 * @package controllers
 */

/**
 * Outbound_send Controller class
 * @package controllers
 */
class Outbound_send extends MY_Controller {
    public $staff_email;
    public $staff_name;
    public $staff_signature;
    public $customer_email;
    public $customer_name;

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('enquiries/outbound_quotation_model');
        $this->load->model('enquiries/enquiry_file_model');
    }

    function _setup($quotation_id) {

        $this->load->model('users/user_contact_model');
        $this->load->model('users/user_model');

        $quotation_data = $this->outbound_quotation_model->get_values($quotation_id);
        $this->customer_email = $this->user_contact_model->get_by_user_id($quotation_data['customer_id'], USERS_CONTACT_TYPE_EMAIL, true, true);
        $this->customer_name = $this->user_model->get_name($quotation_data['customer_id']);

        $this->staff_email = $this->user_contact_model->get_by_user_id($this->session->userdata('user_id'), USERS_CONTACT_TYPE_EMAIL, true, true);
        $this->staff_name = $this->user_model->get_name($this->session->userdata('user_id'), 'f l');
        $staff = $this->user_model->get($this->session->userdata('user_id'));
        $this->staff_signature = $staff->signature;
    }

    function show($quotation_id) {

        $this->_setup($quotation_id);

        // Generate PDF if not already done
        $quotation_file = $this->config->item('files_path')."enquiries/outbound/$quotation_id/outbound_quotation_$quotation_id.pdf";
        if (!file_exists($quotation_file)) {
            $this->session->set_flashdata('redirect_url', "enquiries/outbound_send/show/$quotation_id");
            $this->session->set_flashdata('save_only', true);
            redirect("enquiries/export/export_outbound/pdf/$quotation_id");
        }

        if ($this->input->post('attach')) {
            $errors = !$this->add_file($quotation_id);
        }

        if ($this->input->post('send')) {
            $this->send($quotation_id);
        }

        $emails = $this->input->post('emails');
        $files = $this->enquiry_file_model->get(array('document_type' => FILE_TYPE_OUTBOUND, 'document_id' => $quotation_id));

        if (is_null($files)) {
            $files = array();
        }

        for ($i = 1; $i < 6; $i++) {
            $this->form_validation->set_rules("email$i", "Email recipient $i", 'valid_email');
        }
        $success = $this->form_validation->run();

        $public_files = $this->enquiry_file_model->get(array('document_type' => FILE_TYPE_PUBLIC));

        // Set up title bar
        $title_options = array('title' => "Email attachments for quotation #$quotation_id. Main recipient: $this->customer_email",
                               'help' => "Select which documents to send to the enquirer (or add more), then click Email Enquirer",
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => 'Outbound Quotation attachments',
                             'section_title' => get_title($title_options),
                             'content_view' => 'enquiries/outbound/send',
                             'quotation_id' => $quotation_id,
                             'staff_id' => $this->session->userdata('user_id'),
                             'files' => $files,
                             'public_files' => $public_files);

        $this->load->view('template/default', $pageDetails);
    }

    function add_file($quotation_id) {


        $config = array();
        $config['upload_path'] = $this->config->item('files_path').'enquiries/outbound/'.$quotation_id;
        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path']);
        }
        // $config['allowed_types'] = ENQUIRIES_UPLOAD_ALLOWED_TYPES;
        $config['allowed_types'] = '*';
        $config['encrypt_name'] = true;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('extra_file')) {
            add_message($this->upload->display_errors(), 'error');
            return false;
        }

        $file_data = $this->upload->data();

        // If file already exists, delete it and cancel action
        if ($file = $this->enquiry_file_model->get(array('document_type' => FILE_TYPE_OUTBOUND, 'document_id' => $quotation_id, 'filename_original' => $file_data['orig_name']), true)) {
            add_message('A file of this name (' . $file_data['orig_name'] . ') is already part
                     of the attachments list. Please choose a file with a different name.', 'warning');
            unlink($config['upload_path'] . '/' . $file_data['file_name']);
            return false;
        }

        $new_file = array('document_type' => FILE_TYPE_OUTBOUND,
                          'document_id' => $quotation_id,
                          'filename_original' => $file_data['orig_name'],
                          'filename_new' => $file_data['file_name'],
                          'raw_name' => $file_data['raw_name'],
                          'location' => "outbound/$quotation_id/",
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
            return false;
        } else {
            return true;
        }
    }

    function delete_file($quotation_id, $file_id) {

        if (!$file = $this->enquiry_file_model->get($file_id)) {
            add_message('This file could not be deleted!', 'error');
            return $this->show($quotation_id);
        }

        $file_name = $this->config->item('files_path').'enquiries/' . $file->location . $file->filename_new;

        if (file_exists($file_name)) {
            unlink($file_name);
        } else {
            add_message('The file could not be found on the hard disk, but it has been removed from the attachments list anyway.', 'warning');
        }

        if ($this->enquiry_file_model->delete($file_id)) {
            add_message("The file $file->filename_original was successfully deleted", 'success');
        } else {
            add_message("The file $file->filename_original could not be deleted from the database for an unknown reason.", 'error');
        }

        return $this->show($quotation_id);
    }

    function send($quotation_id) {

        $this->_setup($quotation_id);

        $this->load->library('zip');

        $file_location = $this->config->item('files_path')."enquiries/outbound/$quotation_id/";

        $this->zip->read_file($file_location."outbound_quotation_$quotation_id.pdf");

        $archive_location = $this->config->item('files_path')."enquiries/outbound/$quotation_id/";

        if ($this->input->post('public_files')) {
            foreach ($this->input->post('public_files') as $public_file_id => $ignore) {
                $file = $this->enquiry_file_model->get($public_file_id);
                $this->zip->read_file($this->config->item('public_dir').$file->filename_new);
            }
        }

        if ($files = $this->enquiry_file_model->get(array('document_type' => FILE_TYPE_OUTBOUND, 'document_id' => $quotation_id))) {
            if (is_null($files)) {
                $files = array();
            }

            foreach ($files as $file) {
                $fullpath = $this->config->item('files_path')."enquiries/".$file->location.$file->filename_new;
                if (!file_exists($fullpath)) {
                    $fullpath = $this->config->item('files_path')."enquiries/".$file->location.$file->filename_original;
                }
                $this->zip->read_file($fullpath);
            }
        }

        $archive_location = $this->config->item('files_path')."enquiries/outbound/$quotation_id/";
        if (!file_exists($archive_location)) {
            mkdir($archive_location);
        }

        // Save the zip file on disk
        $archive_file = $archive_location . 'attachments.zip';
        $this->zip->archive($archive_file);

        // Set up the email
        $this->load->library('email');
        $quotation = $this->outbound_quotation_model->get($quotation_id);
        $this->email->from($this->staff_email, $this->staff_name);
        $this->email->attach($archive_file);
        $this->email->to($this->customer_email);
        $this->email->subject("Chinasavvy Outbound Quotation No $quotation_id; Enquiry No $quotation->enquiry_id");
        $message = '=====================================

Dear ' . $this->customer_name . "

I have pleasure in attaching our quotation in response to your recent enquiry.

Attached to this email is a zip archive containing the quotation for the product for which you filled and submitted an enquiry form.  It also contains additional files related to your enquiry.

We hope the quotation meets with your approval. Should you have any queries please do not hesitate to contact us, either via email or at any of the contact details below.

Best regards,

$this->staff_signature
====================================";

        $this->email->message($message);

        // BCC admins
        $admin_users = $this->user_model->get_users_by_capability('enquiries:getoutboundnotifications');
        $admin_emails = array();

        foreach ($admin_users as $admin_user) {
            $admin_emails[] = $this->user_contact_model->get_by_user_id($admin_user->id, USERS_CONTACT_TYPE_EMAIL, true, true, true);
        }
        $this->email->bcc($admin_emails);

        // CC additional recipients
        $additional_emails = array();

        for ($i = 1; $i < 6; $i++) {
            if ($email = $this->input->post("email$i")) {
                $additional_emails[] = $email;
            }
        }

        $this->email->cc($additional_emails);

        if ($this->email->send()) {
            add_message('Your email was sent successfully.', 'success');
            // Update enquiry status to CUSTOMER QUOTED
            $this->load->model('enquiries/enquiry_model');
            if (!$this->enquiry_model->edit($quotation->enquiry_id, array('status' => ENQUIRIES_ENQUIRY_STATUS_QUOTED))) {
                add_message('There was an error changing the status of the enquiry to CUSTOMER QUOTED!', 'error');
            } else {
                // Record a system enquiry note
                $this->load->model('enquiries/enquiry_note_model');
                $this->enquiry_note_model->add(array('type' => 'system', 'message' => "Customer quoted (quotation id $quotation_id).", 'enquiry_id' => $quotation->enquiry_id));
            }

        } else {
            add_message('Email could not be sent!', 'error');
            return false;
        }

        return true;
    }

    function serve_file($file_id, $quotation_id) {

        if (!$file = $this->enquiry_file_model->get($file_id)) {
            add_message('The requested file could not be found in the database!', 'error');
            redirect("enquiries/outbound_send/show/$quotation_id");
        }

        $fullpath = $this->config->item('files_path')."enquiries/".$file->location.$file->filename_new;
        if ($file->document_type == FILE_TYPE_PUBLIC) {
            $fullpath = $this->config->item('public_dir').$file->filename_new;
        }

        if (!file_exists($fullpath)) {
            $fullpath = $this->config->item('files_path')."enquiries/".$file->location.$file->filename_original;
        }

        if (!file_exists($fullpath)) {
            add_message('The requested file could not be found on disk!', 'error');
            redirect("enquiries/outbound_send/show/$quotation_id");
        }

        $this->load->helper('file');
        if ($mimetype = get_mime_by_extension($fullpath)) {
            header("Content-type:$mimetype");
        } else {
            header("Content-type:octet-stream");
        }

        header("Content-Disposition:attachment;filename=$file->filename_original");
        readfile($fullpath);
    }

    function serve_pdf($quotation_id) {

        $file_location = $this->config->item('files_path')."enquiries/outbound/$quotation_id/";
        $full_path = $file_location."outbound_quotation_$quotation_id.pdf";
        if (file_exists($full_path)) {
            header("Content-type:application/pdf");
            header("Content-Disposition:attachment;filename=outbound_quotation_$quotation_id.pdf");
            readfile($full_path);
        } else {
            $this->session->set_flashdata('redirect_url', "enquiries/outbound_send/serve_pdf/$quotation_id");
            $this->session->set_flashdata('save_only', true);
            redirect("enquiries/export/export_outbound/pdf/$quotation_id");
        }
    }
}
