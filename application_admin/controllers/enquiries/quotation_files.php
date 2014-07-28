<?php
class Quotation_files extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('enquiries/enquiry_file_model');
    }

    public function index() {
        return $this->show();
    }

    function show() {

        if ($this->input->post('attach')) {
            $errors = !$this->add_file();
        }

        // TODO Get the files from the physical location on disk: /home/chinas7/public_html/public/*
        $files = $this->enquiry_file_model->get(array('document_type' => FILE_TYPE_PUBLIC));

        // Set up title bar
        $title_options = array('title' => "Files automatically attached to outbound quotations",
                               'help' => "Files automatically attached to outbound quotations",
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => 'Outbound Quotation attachments',
                             'section_title' => get_title($title_options),
                             'content_view' => 'enquiries/quotation_files/view',
                             'files' => $files);

        $this->load->view('template/default', $pageDetails);
    }

    function add_file() {
        $config = array();
        $config['upload_path'] = '/home/chinas7/public_html/public';
        // $config['allowed_types'] = ENQUIRIES_UPLOAD_ALLOWED_TYPES;
        $config['allowed_types'] = '*';
        $config['encrypt_name'] = true;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('new_file')) {
            add_message($this->upload->display_errors(), 'error');
            return false;
        }

        $file_data = $this->upload->data();

        // If file already exists, delete it and cancel action
        if ($file = $this->enquiry_file_model->get(array('document_type' => FILE_TYPE_PUBLIC, 'filename_original' => $file_data['orig_name']), true)) {
            add_message('A file of this name (' . $file_data['orig_name'] . ') already exists. Please choose a file with a different name.', 'warning');
            unlink($config['upload_path'] . '/' . $file_data['file_name']);
            return false;
        }

        $new_file = array('document_type' => FILE_TYPE_PUBLIC,
                          'filename_original' => $file_data['orig_name'],
                          'filename_new' => $file_data['file_name'],
                          'raw_name' => $file_data['raw_name'],
                          'location' => "",
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

    function delete_file($file_id) {

        if (!$file = $this->enquiry_file_model->get($file_id)) {
            add_message('This file could not be deleted!', 'error');
            return $this->show($quotation_id);
        }

        $file_name = $this->config->item('public_dir').$file->filename_new;

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

        return $this->show();
    }

    function serve_file($file_id) {

        if (!$file = $this->enquiry_file_model->get($file_id)) {
            add_message('The requested file could not be found in the database!', 'error');
            redirect("enquiries/quotation_files/show");
        }

        $fullpath = $this->config->item('public_dir').$file->filename_new;
        if (!file_exists($fullpath)) {
            $fullpath = $this->config->item('public_dir').$file->filename_original;
        }

        if (!file_exists($fullpath)) {
            add_message('The requested file could not be found on disk!', 'error');
            redirect("enquiries/quotation_files/show");
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
}
