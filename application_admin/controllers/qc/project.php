<?php
/**
 * Contains the Project Controller class
 * @package controllers
 */

/**
 * Project Controller class
 * @package controllers
 */
class Project extends MY_Controller {
    var $roots = array(
        'file' => 'files'
    );
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer', array('qc' => array('project/browse|QC Projects')));
        $this->config->set_item('exclude', array('browse'));
        $this->load->model('qc/project_model');
        $this->load->model('qc/spec_model');
        $this->load->model('qc/specphoto_model');
        $this->load->model('qc/specrevision_model');
        $this->load->model('qc/speccategory_model');
        $this->load->model('qc/job_model');
        $this->load->model('qc/revision_model');
        $this->load->model('qc/projectrelated_model');
        $this->load->model('qc/procedure_model');

    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     */
    function browse($outputtype='html') {
        require_capability('qc:viewprojects');


        log_user_action('is browsing QC projects');
        $this->load->helper('title');
        $this->load->library('filter');

        $this->filter->add_filter('text', 'Product code', 'code', 'productcode');
        $this->filter->add_filter('text', 'Product name', 'name', 'productname');
        $this->filter->add_filter('dropdown', get_constant_dropdown('QC_RESULT', true), 'status', 'projectstatus', null);
        $this->filter->add_filter('checkbox', 'NULL', 'approved projects', 'approvedprojectadmin', 'approved_project_admin', 0);
        $this->filter->add_filter('checkbox', 'NULL', 'projects with product specs approved', 'approvedproductadmin', 'approved_product_admin', 0);
        $this->filter->add_filter('checkbox', 'NULL', 'projects with QA specs approved', 'approvedqcadmin', 'approved_qc_admin', 1);

        $total_records = $this->project_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 1 result
        $limit = null;
        if (!IS_AJAX && $outputtype == 'html') {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($outputtype, array('pdf'));

        if ($outputtype != 'html') {
            $limit = $datatable_params['perpage'];
        }

        $table_data = $this->project_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit);

        // Format column data
        foreach ($table_data['rows'] as $key => $row) {
            $status = $row[3];

            $icon = 'traffic_slow';
            if ($status == QC_RESULT_PASS) {
                $icon = 'traffic_go';
            } elseif ($status == QC_RESULT_REJECT) {
                $icon = 'traffic_stop';
            }

            $table_data['rows'][$key][3] = get_lang_for_constant_value('QC_RESULT', $status);

            if ($outputtype == 'html') {
                $table_data['rows'][$key][3] = img(array('src' => 'images/admin/icons/'.$icon.'_16.gif', 'class' => 'icon')) . $table_data['rows'][$key][3];
            }

            $table_data['rows'][$key][5] = (empty($row[5])) ? 'No' : 'Yes';
            $table_data['rows'][$key][6] = (empty($row[6])) ? 'No' : 'Yes';
            $table_data['rows'][$key][7] = (empty($row[7])) ? 'No' : 'Yes';
        }

        if ($outputtype == 'html') {
            $table_data = parent::add_action_column('qc', 'project', $table_data, array('edit', 'delete', 'vault'));
            $pageDetails = parent::get_ajax_table_page_details('qc', 'project', $table_data['headings'], array('pdf', 'save', 'add'));
            parent::output_ajax_table($pageDetails, $table_data, $total_records);
        } else {

            $pageDetails = parent::get_export_page_details('project', $table_data);
            $pageDetails['widths'] = array(85, 300, 380, 135, 170, 200, 200, 200, 200, 200);
            parent::output_for_export('qc', 'project', $outputtype, $pageDetails, 'ISO-8859-1');
        }
    }
    public function vault()
    {
        $this->load->model('qc/project_model');
        $segment_array = $this->uri->segment_array();
        $last = $this->uri->total_segments();
        $record_num = $this->uri->segment($last);
	$hashFiles=$this->project_model->get_files($record_num , $type=null);

	
        // first and second segments are our controller and the 'virtual root'
        //$ConFolder = array_shift( $segment_array );
        $controller = array_shift( $segment_array );
        $virtual_root = array_shift( $segment_array );

        if( empty( $this->roots )) exit( 'no roots defined' );

        // let's check if a virtual root is choosen
        // if this controller is the default controller, first segment is 'index'
     //   if ( $controller == 'index' OR $virtual_root == '' ) show_404();

        // let's check if a virtual root matches
     //   if ( ! array_key_exists( $virtual_root, $this->roots )) show_404();

        // build absolute path
       $path_in_url = '';
       // foreach ( $segment_array as $segment ) $path_in_url.= $segment.'/';
       // $absolute_path = $this->roots[ $virtual_root ].'/'.$path_in_url;
      //  $absolute_path = rtrim( $absolute_path ,'/' );

        // is it a directory or a file ?
        if ( is_dir( 'files/qc/'.$record_num))
        {
            // we'll need this to build links
            $this->load->helper('url');

            $dirs = array();
            $files = array();
            // let's traverse the directory
            if ( $handle = @opendir( 'files/qc/'.$record_num ))
            {
                while ( false !== ($file = readdir( $handle )))
                {
                    if (( $file != "." AND $file != ".." ))
                    {
                        if ( is_dir( 'files/qc/'.$record_num.'/'.$file ))
                        {
                            $dirs[]['name'] = $file;
                        }
                        else
                        {
                            $files[]['name'] = $file;
                        }
                    }
                }
                closedir( $handle );
                sort( $dirs );
                sort( $files );


            }
            // parent directory
            // here to ensure it's available and the first in the array
            if ( $path_in_url != '' )
                array_unshift ( $dirs, array( 'name' => '..' ));

            // send the view
            $data = array(
                'controller' => $controller,
                'virtual_root' => $virtual_root,
                'path_in_url' => $path_in_url,
                'dirs' => $dirs,
                'files' => $files,
                'hashFiles' => $hashFiles
            );
            $this->load->view( 'vault/browse', $data );
        }
        else
        {
            // it's not a directory, but is it a file ?
            if ( is_file( 'files/qc/'.$record_num ))
            {
                // let's serve the file
                header ('Cache-Control: no-store, no-cache, must-revalidate');
                header ('Cache-Control: pre-check=0, post-check=0, max-age=0');
                header ('Pragma: no-cache');

                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                // header('Content-Length: ' . filesize( ".$absolute_path." ));  // Modified
                header('Content-Length: ' . filesize( 'files/'.$record_num ));
                header('Content-Disposition: attachment; filename=' . basename( 'files/'.$record_num ));

                @readfile( 'files/qc/'.$record_num );
            }
            else
            {
                show_404();
            }
        }
    }

    public function add() {
        log_user_action('is creating a new QC project.');
        return $this->edit();
    }

    public function edit($project_id=null) {

        $this->load->helper('url');

        require_capability('qc:viewprojects');

        if (!empty($project_id)) {
            log_user_action('is editing QC project #' . $project_id);
            require_capability('qc:editprojects');
            $project = $this->project_model->get($project_id);
            $title = "Edit Project";
        } else {
            $title = "New Project";

        }

        $maintitle_options = array('title' => 'Project management', 'expand' => 'project', 'icons' => array());
        $detailstitle_options = array('title' => 'Project details', 'expand' => 'details', 'icons' => array(), 'level' => 2);

        // Process files if required
        if (!empty($_FILES) && !empty($project_id)) {
            $this->process_file($project_id);
        }

        if (has_capability('qc:viewproductspecs')) {
            $producticons = array();
            $productaddurl = null;
            $productpdfurl = null;
            $productemailurl = null;

            if (has_capability('qc:editprojects')) {
                $producticons[] = 'add';
                $productaddurl = 'javascript:add_cat(\'product\');';
            }

            if (has_capability('qc:emailproductspecs')) {
                $producticons[] = 'email';
                $productemailurl = "qc/email/index/$project_id/".QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS;
            }

            $producticons[] = 'pdf';
            $productpdfurl = "qc/export_pdf/product_specs";
            $producttitle = get_title(array('title' => 'Product specifications',
                                            'expand' => 'productspecs',
                                            'icons' => $producticons, 'level' => 2,
                                            'add_url' => $productaddurl,
                                            'pdf_url' => $productpdfurl,
                                            'pdf_url_params' => array('project_id' => $project_id),
                                            'email_url' => $productemailurl));
        } else {
            $producttitle = '';
        }

        if (has_capability('qc:viewqcspecs')) {
            $qcicons = array();
            $qcaddurl = null;
            $qcpdfurl = null;
            $qcemailurl = null;
            $reporturl = null;

            if (has_capability('qc:editprojects')) {
                $qcicons[] = 'add';
                $qcaddurl = 'javascript:add_cat(\'qc\');';
            }
            if (has_capability('qc:emailqcspecs')) {
                $qcicons[] = 'email';
                $qcemailurl = "qc/email/index/$project_id/".QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER;
            }
            if (has_capability('qc:viewqcresults')) {
                $qcicons[] = 'report';
                $reporturl = "qc/export_pdf/qc_results";
            }

            $qcicons[] = 'pdf';

            $qctitle = get_title(array('title' => 'QC specifications',
                                       'expand' => 'qcspecs',
                                       'icons' => $qcicons, 'level' => 2,
                                       'add_url' => $qcaddurl,
                                       'pdf_url' => "qc/export_pdf/qc_specs",
                                       'pdf_url_params' => array('project_id' => $project_id),
                                       'email_url' => $qcemailurl,
                                       'report_url' => $reporturl,
                                       'report_url_params' => array('project_id' => $project_id)));
        } else {
            $qctitle = '';
        }

        if (has_capability('qc:doanything')) {
            $inspectortitle = get_title(array('title' => "Inspector's Comments",
                                       'expand' => 'inspectors'
                                       ));
        } else {
            $inspectortitle = '';
        }

        $this->config->set_item('replacer', array('qc' => array('project/browse|QC Projects'), 'edit' => "$title $project_id", 'add' => $title));

        $label_function = function($object) {
            return $object->number . ': ' . $object->title;
        };

        $procedures = $this->procedure_model->get_dropdown('title', '-- Select a Procedure --', $label_function, false, null, 0);

        $inspector_types['qc_inspectors'] = $this->user_model->get_users_by_capability('qc:approveprojects');// TODO refine these
        $inspector_types['qc_managers'] = $this->user_model->get_users_by_capability('qc:doanything');// TODO refine these

        foreach($inspector_types as &$inspectors) {
            $result = array('' => '-- Please Select --');
            foreach ($inspectors as $inspector) {
                $result[$inspector->id] = $inspector->first_name . ' ' . $inspector->surname;
            }
            $inspectors = $result;
        }

        $pageDetails = array('title' => $title,
                             'main_title' => get_title($maintitle_options),
                             'details_title' => get_title($detailstitle_options),
                             'product_title' => $producttitle,
                             'qc_title' => $qctitle,
                             'inspector_title' => $inspectortitle,
                             'content_view' => 'qc/project/edit',
                             'project_id' => $project_id,
                             'procedures' => $procedures,
                             'inspector_types' => $inspector_types,
                             'jstoloadinfooter' => array('jquery/jquery.form',
                                                         'jquery/jquery.domec',
                                                         'jquery/jquery.loading',
                                                         'jquery/jquery.selectboxes',
                                                         'jquery/jquery.jeditable',
                                                         'jquery/jquery.json',
                                                         'jquery/pause',
                                                         'dateformat',
                                                         'application/qc/projects_edit',
                                                         'application/qc/specifications')
                             );
        $this->load->view('template/default', $pageDetails);
    }

    public function process_file($project_id) {

        $this->load->model('qc/projectfile_model');
        log_user_action('is uploading a file to QC project #' . $project_id);

        $type = $this->input->post('type');

        $config = array();
        $config['upload_path'] = $this->config->item('files_path') . "qc/$project_id";
        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }
        $config['allowed_types'] = 'pdf|PDF';
        $config['encrypt_name'] = true;
        $this->load->library('upload', $config);
        xdebug_break();if (!$this->upload->do_upload('project_file')) {
            add_message($this->upload->display_errors() . 'Only PDF files are allowed.', 'error');
            return false;
        }

        $file_data = $this->upload->data();

        // If file already exists, delete it and cancel action
        $file_params = array('project_id' => $project_id, 'file' => $file_data['orig_name'], 'hash' => $file_data['file_name'], 'type' => $type);
        if ($file = $this->projectfile_model->get($file_params, true)) {
            add_message('The file ' . $file_data['orig_name'] . ' was already attached to this project Product specifications. ', 'warning');
            unlink($config['upload_path'] . '/' . $file_data['file_name']);
            return false;
        } else {
            $file_params += array(
                              'raw_name' => $file_data['raw_name'],
                              'file_type' => $file_data['file_type'],
                              'file_extension' => $file_data['file_ext'],
                              'file_size' => $file_data['file_size'],
                              'is_image' => $file_data['is_image'],
                              'image_width' => $file_data['image_width'],
                              'image_height' => $file_data['image_height'],
                              'image_type' => $file_data['image_type'],
                              'image_size_str' => $file_data['image_size_str']);

            if ($file_id = $this->projectfile_model->add($file_params)) {
                add_message("The file {$file_params['file']} was uploaded", 'success');
            } else {
                add_message('The file was uploaded, but the file info could not be recorded in the database...', 'warning');
            }
        }

        return true;
    }

    public function get_shipping_marks($project_id) {

        $project = $this->project_model->get($project_id);

        if (empty($project->shippingmarks)) {
            $project->shippingmarks = QC_DEFAULT_SHIPPING_MARKS_LINE_1 . "\n"
                . QC_DEFAULT_SHIPPING_MARKS_LINE_2 . "\n"
                . QC_DEFAULT_SHIPPING_MARKS_LINE_3 . "\n"
                . QC_DEFAULT_SHIPPING_MARKS_LINE_4 . "\n"
                . QC_DEFAULT_SHIPPING_MARKS_LINE_5 . "\n"
                . QC_DEFAULT_SHIPPING_MARKS_LINE_6 . "\n"
                . QC_DEFAULT_SHIPPING_MARKS_LINE_7;
        }

        echo stripslashes($project->shippingmarks);
        die;
    }

    public function get_spec_photo_counts($project_id) {

        $specs = $this->spec_model->get(array('project_id' => $project_id));
        $photocounts = array();

        if (!empty($specs)) {
            foreach ($specs as $spec) {
                $photocounts[$spec->id] = $this->db->where('spec_id', $spec->id)->count_all_results('qc_spec_photos');
            }
        }
        echo json_encode(stripslashes_deep($photocounts));
        die;
    }

    public function project_suggest() {

        $term = $this->input->post('term');

        $project_list = $this->project_model->get_list($term);

        echo json_encode($project_list);
        die;
    }

    public function related_project_suggest($project_id) {

        $project_list = $this->project_model->get_list($this->input->post('term'), $project_id);

        echo json_encode($project_list);
        die;
    }

    public function get_categories($type=QC_SPEC_CATEGORY_TYPE_PRODUCT) {

        $q = $this->input->post('q');

        if (!empty($q)) {
            $this->db->like('name', $q);
        }

        $speccats = $this->speccategory_model->get(array('type' => $type));
        $cats = '';

        if (!empty($speccats)) {
            foreach ($speccats as $speccat) {
                $cats .= "$speccat->id|$speccat->name\n";
            }
        }
        $cats = rtrim($cats, "\n");
        echo $cats;
        die;
    }

    public function part_suggest() {

        $this->load->model('codes/part_model');

        $term = $this->input->post('term');

        $parts = $this->part_model->get_suggest_list($term);

        $parts_list = array();
        $names = array();
        $nonuniquenames = array();
        $savedparts = array();

        if (!empty($parts)) {
            foreach ($parts as $part) {
                $name = $part->name;
                if ($dupnamekey = array_search($part->name, $names)) {
                    $name = "$part->name: " . substr($part->description, 0, 20);
                    $nonuniquenames[$dupnamekey] = "[{$savedparts[$dupnamekey]->product_number}] {$savedparts[$dupnamekey]->name}: " . substr($savedparts[$dupnamekey]->description, 0, 20);
                } else {
                    $names[$part->id] = $part->name;
                }
                $parts_list[$part->id] = "[$part->product_number] $name";
                $savedparts[$part->id] = clone($part);
            }
        }
        foreach ($nonuniquenames as $key => $newname) {
            $parts_list[$key] = $newname;
        }

        $parts_array = array();
        foreach ($parts_list as $value => $label) {
            $part = new stdClass();
            $part->value = $value;
            $part->label = $label;
            $parts_array[] = $part;
        }

        echo json_encode(stripslashes_deep($parts_array));
        die;

    }

    // @TODO Depending on where the Codes stuff comes from, refactor the SQL portion into the appropriate Model
    public function get_parts() {

        $this->load->model('codes/part_model');

        $this->part_model->setup_for_sql();
        $parts = $this->part_model->get(array('codes_parts.status' => 'Active'), false, 'product_number DESC');

        $data['parts'] = array();
        $names = array();
        $nonuniquenames = array();
        $savedparts = array();

        if (!empty($parts)) {
            foreach ($parts as $part) {
                $name = $part->name;
                if ($dupnamekey = array_search($part->name, $names)) {
                    $name = "$part->name: " . substr($part->description, 0, 20);
                    $nonuniquenames[$dupnamekey] = "[{$savedparts[$dupnamekey]->product_number}] {$savedparts[$dupnamekey]->name}: " . substr($savedparts[$dupnamekey]->description, 0, 20);
                } else {
                    $names[$part->id] = $part->name;
                }
                $data['parts'][$part->id] = "[$part->product_number] $name";
                $savedparts[$part->id] = clone($part);
            }
        }
        foreach ($nonuniquenames as $key => $newname) {
            $data['parts'][$key] = $newname;
        }

        echo json_encode(stripslashes_deep($data));
        die;
    }

    public function add_related($project_id, $related_id) {

        require_capability('qc:editprojects');
        $params = array('project_id' => $project_id, 'related_id' => $related_id);
        $related = $this->projectrelated_model->get($params, true);

        if (empty($related)) {
            $this->projectrelated_model->add($params, array('*'));
            $data['message'] = 'A related product was succesfully associated with this project.';
            $data['type'] = 'success';
            $this->project_model->flag_as_changed($project_id);
        } else {
            $data['message'] = 'This product assocication already exists';
            $data['type'] = 'error';
        }
        log_user_action('has added a related project to QC project #' . $project_id);
        echo json_encode(stripslashes_deep($data));
        die;
    }

    public function delete_related($project_id, $related_id) {

        require_capability('qc:editprojects');
        $params = array('project_id' => $project_id, 'related_id' => $related_id);
        $related = $this->projectrelated_model->get($params, true);

        if (empty($related)) {
            $data['errors']['relatedproduct'] = 'This product association does not exist';
        } else {
            $this->projectrelated_model->delete($related->id, array('*'));
            $data['message'] = 'A related product was succesfully disassociated from this project.';
            $data['type'] = 'success';
            $this->project_model->flag_as_changed($project_id);
        }
        log_user_action("has deleted related project #$related_id from QC project #$project_id");
        echo json_encode(stripslashes_deep($data));
        die;
    }

    public function save_project($part_id, $duplicate=false) {

        require_capability('qc:writeprojects');

        // Verify that the partid exists
        if (empty($part_id)) {
            $data['errors']['partselector'] = 'No product id (partid) given';
            echo json_encode(stripslashes_deep($data));
            die;
        }

        if ($duplicate && has_capability('qc:writeprojects')) {
            if ($project_to_duplicate = $this->project_model->get($duplicate)) {
                $project_id = $this->project_model->duplicate($duplicate, $part_id);
                $data['message'] = 'This project was successfully created, based on ' . $this->project_model->get_name($project_to_duplicate->part_id);
                $data['type'] = 'success';
                $project = $this->project_model->get($project_id);
                $this->get_json_data($project_id, $data);
            }
        } else {
            $params = array('part_id' => $part_id);

            if ($project_id = $this->project_model->add($params)) {
                $data['message'] = 'This project was successfully created.';
                $data['type'] = 'success';
                $this->get_json_data($project_id, $data);
            } else {
                $data['message'] = 'This project could not be created.';
                $data['type'] = 'error';
                echo json_encode(stripslashes_deep($data));
                die;
            }
        }
    }

    public function update_inspection_level($project_id, $new_level) {

        require_capability('qc:editprojects');

        $params = array('inspection_level' => $new_level);

        $result = $this->project_model->edit($project_id, $params);
        if ($result) {
            $data['message'] = "The inspection level was successfully updated.";
            $data['type'] = 'success';
        } else {
            $data['message'] = "The inspection level could not be updated due to an error.";
            $data['type'] = 'error';
        }
        log_user_action("has updated the inspection level to $new_level for QC project #$project_id");
        $this->calculate_and_update_sample_size($project_id);
        echo json_encode(stripslashes_deep($data));
        die;
    }

    public function update_defect_limit($project_id, $type, $new_limit) {

        require_capability('qc:editprojects');

        $result = $this->project_model->edit($project_id, array('defect_'.$type.'_limit' => $new_limit));

        if ($result) {
            $data['message'] = "The $type defect limit was successfully updated.";
            $data['type'] = 'success';
        } else {
            $data['message'] = "The $type defect limit could not be updated due to an error.";
            $data['type'] = 'error';
        }
        log_user_action("has updated the $type defect limit to $new_limit for QC project #$project_id");
        echo json_encode(stripslashes_deep($data));
        die;
    }

    public function update_approved_state($project_id, $approval_type, $user_type, $new_state) {

        $this->load->helper('date');
        require_capability('qc:editprojects');

        $originalvalue = $new_state;

        if (empty($new_state)) {
            $new_state = 'NULL';
        } elseif ($user_type == 'customer') {
            $new_state = human_to_unix($originalvalue);
        } else {
            $new_state = $this->session->userdata('user_id');
        }

        $params = array('approved_' . $approval_type . '_' . $user_type => $new_state);
        $result = $this->project_model->edit($project_id, $params);
        if ($result) {
            if (!empty($originalvalue)) {
                if ($user_type == 'customer') {
                    $data['message'] = "The $approval_type approved date was successfully updated to $originalvalue.";
                } else {
                    $approval_type = ($approval_type == 'project') ? $approval_type . ' was' : $approval_type . ' specifications were';
                    $data['message'] = "The $approval_type recorded as approved.";
                }
            } else {
                if ($user_type == 'customer') {
                    $data['message'] = "The $approval_type specifications were successfully un-approved.";
                } else {
                    $approval_type = ($approval_type == 'project') ? $approval_type . ' was' : $approval_type . ' specifications were';
                    $data['message'] = "The $approval_type recorded as un-approved.";
                }
            }
            $data['type'] = 'success';
        } else {
            $data['message'] = "The $approval_type approved date could not be updated due to an error.";
            $data['type'] = 'error';
        }
        log_user_action("has updated the $approval_type approval state to $new_state for QC project #$project_id");
        echo json_encode(stripslashes_deep($data));
        die;
    }

    public function calculate_and_update_sample_size($project_id)
    {
        $project = $this->project_model->get($project_id);

        $sample_size = Eloquent\SampleSize::getForBatchQty($project->batch_size, $project->inspection_level);

        $this->project_model->edit($project_id, compact('sample_size'));
    }

    public function get_sample_size($project_id)
    {
        $project = $this->project_model->get($project_id);

        echo $project->sample_size;
        die;
    }

    public function update_standard_variable($project_id, $field=null, $new_value=null) {

        require_capability('qc:editprojects');
        $field = (empty($field)) ? $this->input->post('field') : $field;
        $new_value = (empty($new_value)) ? $this->input->post('value') : $new_value;

        if (preg_match('/defect(critical|major|minor)limit/', $field, $matches)) {
            $this->update_defect_limit($project_id, $matches[1], $new_value);
        } elseif (preg_match('/approved_(project|product|qc)_(admin|customer)/', $field, $matches)) {
            $this->update_approved_state($project_id, $matches[1], $matches[2], $new_value);
        } elseif ($field == 'shippingmarks') {
            $this->update_shipping_marks($project_id, $new_value);
        } elseif ($field == 'inspectionlevel') {
            $this->update_inspection_level($project_id, $new_value);
        } elseif ($field == 'samplesize') {
            $field = 'sample_size';
        } elseif ($field == 'customerproductcode') {
            $field = 'customer_code';
        } elseif ($field == 'batchsize') {
            $field = 'batch_size';
        } elseif (substr($field, 0, 10) == 'signatures') {

        }

        $project = $this->project_model->get($project_id);
        $original = $project->$field;

        $params = array($field => $new_value);
        log_user_action("has updated the $field to $new_value for QC project #$project_id");

        if ($this->project_model->edit($project_id, $params)) {
            echo stripslashes($new_value);
            if ($field === 'batch_size') {
                $this->calculate_and_update_sample_size($project_id);
            }
        } else {
            echo stripslashes($original);
        }
        die;
    }

    protected function update_inspectors($field, $value) {


    }

    public function update_shipping_marks($project_id, $new_marks) {

        require_capability('qc:editprojects');
        $project = $this->project_model->get($project_id);
        $original = $project->shippingmarks;

        $params = array('shippingmarks' => $new_marks);
        log_user_action("has updated the shipping marks to $new_marks for QC project #$project_id");
        if ($this->project_model->edit($project_id, $params)) {
            echo stripslashes(nl2br($new_marks));
        } else {
            echo stripslashes($original);
        }
        die;
    }

    public function get_json_data($project_id, $message_data=array()) {

        $this->load->driver('cache');
        $data['title'] = 'New project';

        if (empty($project_id)) {
            echo json_encode(stripslashes_deep($data));
            die;
        } else {
            $data['title'] = 'Editing project';
        }
        $data = $this->project_model->get_details($project_id);

        if (!empty($message_data)) {
            $data += $message_data;
        }

        $data['productspecs'] = $this->project_model->get_specs($project_id, QC_SPEC_CATEGORY_TYPE_PRODUCT);
        $data['qcspecs'] = $this->project_model->get_specs($project_id, QC_SPEC_CATEGORY_TYPE_QC);
        $data['parts'] = $this->project_model->get_parts($project_id);
        $data['jobs'] = $this->project_model->get_jobs($project_id);
        $data['suppliers'] = $this->project_model->get_suppliers($project_id, $data['jobs']);
        $data['files'] = $this->project_model->get_files($project_id);
        $data['sample_sizes'] = Eloquent\SampleSize::all()->toArray();
        $data['inspection_levels'] = get_constant_dropdown('QC_INSPECTION_LEVEL_');
        // Remove default ** PLEASE SELECT ** option
        unset($data['inspection_levels']['']);

        echo json_encode(stripslashes_deep($data));
    }

    /**
     * This is an ADMIN ONLY function used to restore qc_specs records from the qc_spec_revisions and qc_revisions tables, in
     * case something goes horribly wrong and we lose a heap of data. Yep, you guessed it, this just happened :(
     */
    public function restore_specs_from_revision() {
        $this->db->where('project_id < 1053 AND project_id > 1039', null, false);
        $this->db->order_by('project_id ASC, number DESC');
        $revisions = $this->revision_model->get();

        $old_spec_ids = array();

        foreach ($revisions as $rev) {
            if (empty($revisions_array[$rev->project_id])) {
                $revision_data = json_decode($rev->data);
                foreach ($revision_data->qcspecs as $spec_id => $qc_spec) {
                    $old_spec_id = null;
                    if ($existing_spec = $this->spec_model->get($qc_spec->id)) {
                        $old_spec_id = $qc_spec->id;
                        unset($qc_spec->id);
                    }

                    if (array_key_exists($qc_spec->english_id, $old_spec_ids)) {
                        $qc_spec->english_id = $old_spec_ids[$qc_spec->english_id];
                    }

                    $spec_id = $this->spec_model->add($qc_spec);

                    if (empty($qc_spec->english_id) && !empty($old_spec_id)) {
                        $old_spec_ids[$old_spec_id] = $spec_id;
                    }
                }

                foreach ($revision_data->productspecs as $spec_id => $qc_spec) {
                    $old_spec_id = null;
                    if ($existing_spec = $this->spec_model->get($qc_spec->id)) {
                        $old_spec_id = $qc_spec->id;
                        unset($qc_spec->id);
                    }

                    if (array_key_exists($qc_spec->english_id, $old_spec_ids)) {
                        $qc_spec->english_id = $old_spec_ids[$qc_spec->english_id];
                    }

                    $spec_id = $this->spec_model->add($qc_spec);

                    if (empty($qc_spec->english_id) && !empty($old_spec_id)) {
                        $old_spec_ids[$old_spec_id] = $spec_id;
                    }
                }
            }
        }
    }
}
