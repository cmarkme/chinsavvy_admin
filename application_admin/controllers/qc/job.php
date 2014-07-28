<?php
/**
 * Contains the Job Controller class
 * @package controllers
 */

/**
 * Job Controller class
 * @package controllers
 */
class Job extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('qc' => array('project/browse|QC Projects')));
        $this->config->set_item('exclude', array('browse'));
        $this->load->model('qc/project_model');
        $this->load->model('qc/spec_model');
        $this->load->model('qc/speccategory_model');
        $this->load->model('qc/job_model');
        $this->load->model('qc/jobphoto_model');
    }

    function photos($job_id, $spec_id) {

        require_capability('qc:viewprocessphotos');

        $spec = $this->spec_model->get($spec_id);
        $job = $this->job_model->get($job_id);
        $category = $this->speccategory_model->get($job->category_id);
        $project = $this->project_model->get($spec->project_id);
        $photos = $this->jobphoto_model->get(array('spec_id' => $spec_id, 'job_id' => $job_id));

        $title = "QC inspection photos";

        $this->config->set_item('hide_number', true);
        $this->config->set_item('replacer', array('qc' => array('project/browse|QC Projects'),
                                                  'photos' => array("/qc/project/edit/$spec->project_id|Edit project $spec->project_id",
                                                                    "/qc/process/qc_data/$job->category_id/$project->id|QC Inspector's checklist",
                                                                    $title)));

        $details_title = get_title(array('title' => 'Specification details', 'expand' => 'details', 'icons' => array(), 'level' => 2));
        $photos_title = get_title(array('title' => 'Photos', 'expand' => 'photos', 'icons' => array(), 'level' => 2));

        $title_options = array('title' => $title,
                               'expand' => 'spec',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'main_title' => get_title($title_options),
                             'details_title' => $details_title,
                             'photos_title' => $photos_title,
                             'photos' => $photos,
                             'spec' => $spec,
                             'category_name' => $category->name,
                             'job' => $job,
                             'project_id' => $spec->project_id,
                             'part_id' => $project->part_id,
                             'content_view' => 'qc/job/photos',
                             'jstoloadinfooter' => array('jquery/jquery.MultiFile', 'jquery/jquery.jeditable')
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function delete_photo($photo_id) {

        require_capability('qc:deleteprocessphotos');

        $photo = $this->jobphoto_model->get($photo_id);
        $spec = $this->spec_model->get($photo->spec_id);
        $category = $this->speccategory_model->get($spec->category_id);
        $this->jobphoto_model->delete($photo_id);

        $value = $this->input->post('value');
        add_message("The photo $photo->file has been successfully deleted", 'success');
        $this->project_model->flag_as_changed($spec->project_id);
        redirect("qc/job/photos/$photo->job_id/$spec->id");
    }

    public function upload_photos() {

        $spec_id = $this->input->post('spec_id');
        $job_id = $this->input->post('job_id');
        $spec = $this->spec_model->get($spec_id);

        $config = array();
        $config['upload_path'] = $this->config->item('files_path') . "qc/$spec->project_id/process/$job_id/";
        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $config['allowed_types'] = 'gif|jpg|jpeg|png|bmp|tif|tiff';
        $config['encrypt_name'] = true;
        $this->load->library('upload', $config);
        $this->load->library('Multi_upload');

        $files = $this->multi_upload->go_upload('photos');

        if (!$files) {
            add_message($this->upload->display_errors(), 'error');
            redirect("qc/job/photos/$job_id/$spec->id");
        }

        foreach ($files as $file_data) {
            $file_params = array('spec_id' => $spec_id, 'job_id' => $job_id, 'file' => $file_data['orig_name'], 'hash' => $file_data['file_name']);

            if ($file = $this->jobphoto_model->get($file_params, true)) {
                add_message('The photo ' . $file_data['orig_name'] . ' was already attached to this Spec. ', 'warning');
                unlink($config['upload_path'] . '/' . $file_data['file_name']);
                return false;
            } else {
                $file_params += array(
                                  'image_width' => $file_data['image_width'],
                                  'image_height' => $file_data['image_height'],
                                  'image_type' => $file_data['image_type'],
                                  'image_size_str' => $file_data['image_size_str']);

                if ($file_id = $this->jobphoto_model->add($file_params)) {
                    add_message("The file {$file_params['file']} was uploaded", 'success');
                    // Create thumbnail and small versions of the image
                    $small_dir = $file_data['file_path'] . 'small/';
                    $thumb_dir = $file_data['file_path'] . 'thumb/';

                    if (!file_exists($small_dir)) {
                        mkdir($small_dir, 0777, true);
                    }

                    if (!file_exists($thumb_dir)) {
                        mkdir($thumb_dir, 0777, true);
                    }

                    $image_config = array('source_image' => $file_data['full_path'], 'new_image' => $thumb_dir . $file_data['file_name'], 'width' => 120, 'height' => 120, 'maintain_ratio' => true);
                    $this->load->library('image_lib', $image_config);
                    $this->image_lib->resize();
                    $this->image_lib->clear();
                    $image_config = array('source_image' => $file_data['full_path'], 'new_image' => $small_dir . $file_data['file_name'], 'width' => 250, 'height' => 250, 'maintain_ratio' => true);
                    $this->image_lib->initialize($image_config);
                    $this->image_lib->resize();
                } else {
                    add_message('The file was uploaded, but the file info could not be recorded in the database...', 'warning');
                }
            }
        }

        redirect("qc/job/photos/$job_id/$spec->id");
    }

    public function edit_photo_description($photo_id) {

        $value = $this->input->post('value');
        $this->jobphoto_model->edit($photo_id, array('description' => $value));
        echo $value;
        die();
    }
}
