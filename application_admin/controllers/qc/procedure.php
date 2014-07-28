<?php
/**
 * Contains the Procedure Controller class
 * @package controllers
 */

/**
 * Procedure Controller class
 * @package controllers
 */
class Procedure extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer', array('qc' => array('procedure/browse|QC Procedures')));
        $this->config->set_item('exclude', array('browse'));
        $this->load->model('qc/procedure_model');
        $this->load->model('qc/procedurefile_model');
        $this->load->model('qc/procedureitem_model');
        $this->load->model('qc/spec_model');
        $this->load->model('qc/project_model');
    }

    public function index() {
        return $this->browse();
    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     */
    function browse($outputtype='html') {
        require_capability('qc:viewprocedures');


        $this->load->helper('title');
        $this->load->library('filter');
        log_user_action("is viewing QC procedures");

        $this->filter->add_filter('combo', 'combo', array('procedure_number' => 'Number',
                                                          'procedure_title' => 'Title'));

        $staff_list = $this->user_model->get_users_by_capability('qc:writeprocedures');
        $staff_array = array(null => '-- Select a Staff --');

        foreach ($staff_list as $staff) {
            $staff_array[$staff->id] = $this->user_model->get_name($staff->id, 'l f');
        }

        $this->filter->add_filter('dropdown', $staff_array, 'Updated by', 'procedure_updated_by', null);

        $total_records = $this->procedure_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 1 result
        $limit = null;
        if (!IS_AJAX && $outputtype == 'html') {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($outputtype, array('pdf'));

        if ($outputtype != 'html') {
            $limit = $datatable_params['perpage'];
        }

        $table_data = $this->procedure_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit);

        if ($outputtype == 'html') {
            $table_data = parent::add_action_column('qc', 'procedure', $table_data, array('pdf', 'edit', 'delete'));
            $pageDetails = parent::get_ajax_table_page_details('qc', 'procedure', $table_data['headings'], array('pdf', 'save', 'add'));
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {

            $pageDetails = parent::get_export_page_details('procedure', $table_data);
            $pageDetails['widths'] = array(85, 200, 880, 165, 370, 300);
            parent::output_for_export('qc', 'procedure', $outputtype, $pageDetails, 'ISO-8859-1');
        }
    }

    public function add() {
        return $this->edit();
    }

    public function edit($procedure_id=null) {

        $this->load->helper('url');
        $this->load->helper('date');
        $this->load->helper('form_template');

        require_capability('qc:writeprocedures');

        $items = array();
        $files = array();
        $photos = array();
        $projects = $this->procedure_model->get_projects($procedure_id);

        if (empty($procedure_id)) {
            $procedure_id = $this->input->post('id');
        }

        if (!empty($procedure_id)) {
            require_capability('qc:editprocedures');
            $procedure = $this->procedure_model->get($procedure_id);
            $procedure_data = (array) $procedure;
            $procedure_number = $procedure->number;
            form_element::$default_data = $procedure_data;
            form_element::$default_data['notify_projects'] = true;
            $items = $this->procedure_model->get_items($procedure_id);
            $files = $this->procedure_model->get_files($procedure_id);
            $photos = $this->procedure_model->get_photos($procedure_id);

            $title = "Edit Procedure";
        } else {
            $title = "New Procedure";
            $procedure_number = $this->procedure_model->get_next_number();
        }

        $maintitle_options = array('title' => 'Procedure management', 'expand' => 'procedure', 'icons' => array());
        $detailstitle_options = array('title' => 'Procedure details', 'expand' => 'details', 'icons' => array(), 'level' => 2);
        $itemstitle_options = array('title' => 'Procedure items', 'expand' => 'items', 'icons' => array('add'), 'add_url' => 'javascript:add_item()', 'level' => 2);
        $filestitle_options = array('title' => 'Procedure files', 'expand' => 'files', 'icons' => array('add'), 'add_url' => 'javascript:add_file()', 'level' => 2);
        $photostitle_options = array('title' => 'Procedure photos', 'expand' => 'photos', 'icons' => array('add'), 'add_url' => 'javascript:add_photo()', 'level' => 2);
        $projectstitle_options = array('title' => 'QC Projects using this procedure', 'expand' => 'projects', 'icons' => array(), 'level' => 2);

        $this->config->set_item('replacer', array('qc' => array('procedure/browse|QC Procedures'), 'edit' => "$title $procedure_id", 'add' => $title));

        $pageDetails = array('title' => $title,
                             'main_title' => get_title($maintitle_options),
                             'details_title' => get_title($detailstitle_options),
                             'items_title' => get_title($itemstitle_options),
                             'files_title' => get_title($filestitle_options),
                             'photos_title' => get_title($photostitle_options),
                             'projects_title' => get_title($projectstitle_options),
                             'projects' => $projects,
                             'content_view' => 'qc/procedure/edit',
                             'procedure_id' => $procedure_id,
                             'procedure_number' => $procedure_number,
                             'items' => $items,
                             'files' => $files,
                             'photos' => $photos,
                             'csstoload' => array('jquery.lightbox'),
                             'jstoloadinfooter' => array('jquery/jquery.form',
                                                         'jquery/jquery.domec',
                                                         'jquery/jquery.loading',
                                                         'jquery/jquery.selectboxes',
                                                         'jquery/jquery.jeditable',
                                                         'jquery/jquery.json',
                                                         'jquery/pause',
                                                         'jquery/jquery.lightbox',
                                                         'dateformat',
                                                         'application/qc/procedures_edit')
                             );

        if (!empty($procedure_id)) {
            $pageDetails['procedure_version'] = $procedure->version;
            $pageDetails['revision_date'] = mdate('%d/%m/%Y', $procedure->revision_date);
            $pageDetails['revision_user'] = $this->user_model->get_name($procedure->updated_by);
        }

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {


        require_capability('qc:editprocedures');
        $required_fields = array('title' => 'Title', 'summary' => 'Summary');
        $version = 1;

        if ($procedure_id = (int) $this->input->post('id')) {
            $procedure = $this->procedure_model->get($procedure_id);
            $version = $procedure->version + 1;
            $redirect_url = 'qc/procedure/edit/'.$procedure_id;
        } else {
            $redirect_url = 'qc/procedure/add';
            $procedure_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $action_word = ($procedure_id) ? 'updated' : 'created';

        if ($this->form_validation->run()) {

            $procedure_data = array('title' => $this->input->post('title'),
                                    'summary' => $this->input->post('summary'),
                                    'equipment' => $this->input->post('equipment'),
                                    'equipment_ch' => $this->input->post('equipment_ch'),
                                    'updated_by' => $this->session->userdata('user_id'),
                                    'version' => $version);

            if (empty($procedure_id)) {
                $procedure_data['number'] = $this->procedure_model->get_next_number();
                if (!($procedure_id = $this->procedure_model->add($procedure_data))) {
                    add_message('Could not create this procedure!', 'error');
                    redirect($redirect_url);
                }
            } else {
                if (!$this->procedure_model->edit($procedure_id, $procedure_data)) {
                    add_message('Could not update this procedure!', 'error');
                    redirect($redirect_url);
                } else {
                    if ($this->input->post('notify_projects')) {
                        $this->procedure_model->notify_projects($procedure_id);
                    }
                }
            }

            $procedure = $this->procedure_model->get($procedure_id);
            add_message("procedure $procedure->number has been successfully $action_word!", 'success');

            if ($action_word == 'created') {
                redirect('qc/procedure/edit/'.$procedure_id);
            } else {
                redirect('qc/procedure/browse');
            }
        } else {
            return $this->edit($procedure_id);
        }
    }

    public function process_file() {


        $procedure_id = $this->input->post('id');
        $description = $this->input->post('description');

        $config = array('max_size' => 800000000000);
        $config['upload_path'] = $this->config->item('files_path') . 'qc/procedures/'.$procedure_id.'/files';
        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $config['allowed_types'] = 'pdf|PDF';
        $config['encrypt_name'] = true;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('newfile')) {
            add_message($this->upload->display_errors() . 'Only PDF files are allowed.', 'error');
            redirect("qc/procedure/edit/$procedure_id");
        }

        $file_data = $this->upload->data();

        // If file already exists, delete it and cancel action
        $file_params = array('procedure_id' => $procedure_id, 'file' => $file_data['orig_name'], 'hash' => $file_data['file_name']);
        if ($file = $this->procedurefile_model->get($file_params, true)) {
            add_message('The file ' . $file_data['orig_name'] . ' was already attached to this procedure. ', 'warning');
            unlink($config['upload_path'] . '/' . $file_data['file_name']);
            redirect("qc/procedure/edit/$procedure_id");
        } else {
            $file_params += array('description' => $description,
                              'raw_name' => $file_data['raw_name'],
                              'file_type' => $file_data['file_type'],
                              'file_extension' => $file_data['file_ext'],
                              'file_size' => $file_data['file_size'],
                              'is_image' => $file_data['is_image'],
                              'image_width' => $file_data['image_width'],
                              'image_height' => $file_data['image_height'],
                              'image_type' => $file_data['image_type'],
                              'image_size_str' => $file_data['image_size_str']);

            if ($file_id = $this->procedurefile_model->add($file_params)) {
                add_message("The file {$file_params['file']} was uploaded", 'success');
            } else {
                add_message('The file was uploaded, but the file info could not be recorded in the database...', 'warning');
            }
        }

        redirect("qc/procedure/edit/$procedure_id");
    }

    public function process_photo() {


        $procedure_id = $this->input->post('id');
        $description = $this->input->post('description');

        $config = array();
        $config['upload_path'] = $this->config->item('files_path') . 'qc/procedures/'.$procedure_id.'/photos';
        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $config['allowed_types'] = 'gif|jpg|jpeg|png|bmp|tif|tiff';
        $config['encrypt_name'] = true;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('newphoto')) {
            add_message($this->upload->display_errors() . 'Only image files are allowed.', 'error');
            redirect("qc/procedure/edit/$procedure_id");
        }

        $file_data = $this->upload->data();

        // If file already exists, delete it and cancel action
        $file_params = array('procedure_id' => $procedure_id, 'file' => $file_data['orig_name'], 'hash' => $file_data['file_name']);
        if ($file = $this->procedurefile_model->get($file_params, true)) {
            add_message('The photo ' . $file_data['orig_name'] . ' was already attached to this procedure. ', 'warning');
            unlink($config['upload_path'] . '/' . $file_data['file_name']);
            redirect("qc/procedure/edit/$procedure_id");
        } else {

            $file_params += array('description' => $description,
                              'is_image' => true,
                              'image_width' => $file_data['image_width'],
                              'image_height' => $file_data['image_height'],
                              'image_type' => $file_data['image_type'],
                              'image_size_str' => $file_data['image_size_str']);

            if ($file_id = $this->procedurefile_model->add($file_params)) {
                add_message("The photo {$file_params['file']} was uploaded", 'success');
                // Create thumbnail and small versions of the image
                $small_dir = $file_data['file_path'] . 'small/';
                $medium_dir = $file_data['file_path'] . 'medium/';
                $thumb_dir = $file_data['file_path'] . 'thumb/';

                if (!file_exists($small_dir)) {
                    mkdir($small_dir, 0777, true);
                }

                if (!file_exists($medium_dir)) {
                    mkdir($medium_dir, 0777, true);
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
                $this->image_lib->clear();
                $image_config = array('source_image' => $file_data['full_path'], 'new_image' => $medium_dir . $file_data['file_name'], 'width' => 600, 'height' => 480, 'maintain_ratio' => true);
                $this->image_lib->initialize($image_config);
                $this->image_lib->resize();
            } else {
                add_message('The file was uploaded, but the file info could not be recorded in the database...', 'warning');
            }
        }

        redirect("qc/procedure/edit/$procedure_id");
    }

    /**
     * AJAX method used to add and update procedure items
     */
    public function edit_item() {

        $params = array('number' => $this->input->post('number'),
                        'item' => $this->input->post('item'),
                        'item_ch' => $this->input->post('item_ch'),
                        'procedure_id' => $this->input->post('procedure_id'),
                        'id' => $this->input->post('item_id'));

        $data = new stdClass();

        if (empty($params['id'])) {
            if ($data->item_id = $this->procedureitem_model->add($params)) {
                $data->message = "This procedure item has been successfully added";
                $data->type = 'success';
            } else {
                $data->message = "This procedure item could not be added";
                $data->type = 'error';
            }
        } else {
            $id = $params['id'];
            unset($params['id']);
            if ($this->procedureitem_model->edit($id, $params)) {
                $data->message = "This procedure item has been successfully updated";
                $data->type = 'success';
            } else {
                $data->message = "This procedure item could not be updated";
                $data->type = 'error';
            }
        }

        echo json_encode($data);
    }

    /**
     * AJAX method used to add and update procedure files
     */
    public function edit_file($type='file') {

        $params = array('description' => $this->input->post('description'),
                        'procedure_id' => $this->input->post('procedure_id'),
                        'id' => $this->input->post('file_id'));

        $data = new stdClass();

        $id = $params['id'];
        unset($params['id']);
        if ($this->procedurefile_model->edit($id, $params)) {
            $data->message = "This procedure $type has been successfully updated";
            $data->type = 'success';
        } else {
            $data->message = "This procedure $type could not be updated";
            $data->type = 'error';
        }

        echo json_encode($data);
    }

    public function delete_item() {

        $item_id = $this->input->post('id');
        $data = new stdClass();

        if ($this->procedureitem_model->delete($item_id)) {
            $data->message = "This procedure item has been successfully deleted";
            $data->type = 'success';
        } else {
            $data->message = "This procedure item could not be deleted";
            $data->type = 'error';
        }

        echo json_encode($data);
    }

    public function delete_photo() {

        $file_id = $this->input->post('id');
        $data = new stdClass();

        if ($this->procedurefile_model->delete($file_id, 'photo')) {
            $data->message = "This photo has been successfully deleted";
            $data->type = 'success';
        } else {
            $data->message = "This photo could not be deleted";
            $data->type = 'error';
        }

        echo json_encode($data);
    }

    public function delete_file() {

        $file_id = $this->input->post('id');
        $data = new stdClass();

        if ($this->procedurefile_model->delete($file_id)) {
            $data->message = "This file has been successfully deleted";
            $data->type = 'success';
        } else {
            $data->message = "This file could not be deleted";
            $data->type = 'error';
        }

        echo json_encode($data);
    }


    /**
     * Forces download of a given file (stays on the enquiry edit page)
     * @param int $file_id
     */
    function download_file($file_id, $type='files') {
        require_capability('qc:viewprocedures');
        $this->load->helper('download');
        $this->load->model('qc/procedurefile_model');
        $file = $this->procedurefile_model->get($file_id);

        $data = file_get_contents($this->config->item('files_path') . "qc/procedures/$file->procedure_id/$type/$file->hash");
        force_download($file->file, $data);
    }
}
