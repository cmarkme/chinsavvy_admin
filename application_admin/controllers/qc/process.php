<?php
/**
 * Contains the Process Controller class
 * @package controllers
 */

/**
 * Process Controller class
 * @package controllers
 */
class Process extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('qc' => array('project/browse|QC Projects')));
        $this->config->set_item('exclude', array('browse'));
        $this->load->model('qc/project_model');
        $this->load->model('qc/spec_model');
        $this->load->model('qc/specresult_model');
        $this->load->model('qc/speccategory_model');
        $this->load->model('qc/job_model');
        $this->load->model('qc/jobfile_model');
    }

    // Save new QC spec revision, delete existing job completely
    public function restart($job_id) {

        // To trigger the QC revision increase, update the first spec in the project
        $job = $this->job_model->get($job_id);

        if ($spec = $this->spec_model->get(array('project_id' => $job->project_id, 'category_id' => $job->category_id), true)) {
            $this->spec_model->edit($spec->id, array('units' => rand(0, 999)));
        }

        $this->project_model->save_revision($job->project_id);

        $this->job_model->delete($job_id);

        // Update project status
        $this->project_model->update_acceptance_status($job->project_id);

        $this->session->set_flashdata('category_id', $job->category_id);
        $this->session->set_flashdata('project_id', $job->project_id);
        redirect("qc/process/qc_data/");
    }

    public function qc_data($category_id=null, $project_id=null, $supplier_id=null) {

        $this->load->helper('date');
        $this->load->helper('form_template');

        $error = false;
        $no_job = false;
        $job_id = $this->input->post('job_id');
        $job_id = (empty($job_id)) ? $this->session->flashdata('job_id') : $job_id;

        if (!empty($job_id)) {
            if (!($job = $this->job_model->get($job_id))) {
                add_message('This QC job does not exist!', 'error');
                $error = true;
            } else {
                $category_id = $job->category_id;
                $project_id = $job->project_id;
                $supplier_id = $job->supplier_id;
            }
        } else {
            $category_id = (empty($category_id)) ? $this->session->flashdata('category_id') : $category_id;
            $project_id = (empty($project_id)) ? $this->session->flashdata('project_id') : $project_id;
            $supplier_id = (empty($supplier_id)) ? $this->session->flashdata('supplier_id') : $supplier_id;

            $job_params = array('category_id' => $category_id, 'project_id' => $project_id);
            if (!empty($supplier_id)) {
                $job_params['supplier_id'] = $supplier_id;
            }

            if (empty($category_id) || empty($project_id)) {
                $error = true;
            } else if ($job = $this->job_model->get($job_params, true)) {
                $supplier_id = $job->supplier_id;
                $job_id = $job->id;
            } else {
                $no_job = true;
                $job_id = null;
            }
        }

        if ($error) {
            $this->load->view('template/default', array('title' => 'Error!', 'content_view' => 'qc/process/error'));
            return;
        }

        $process_specs = $this->speccategory_model->get_specs($category_id, $project_id);
        $project_details = $this->project_model->get_details($project_id);
        $category = $this->speccategory_model->get($category_id);
        $project = $this->project_model->get($project_id);

        // Get list of additional QC specs
        if (!$no_job) {
            $job_specs = $this->job_model->get_additional_specs($job->id);

            foreach ($job_specs[QC_SPEC_TYPE_ADDITIONAL] as $language) {
                $process_specs[$language[QC_SPEC_LANGUAGE_EN]->id] = $language[QC_SPEC_LANGUAGE_EN];
            }
            foreach ($job_specs[QC_SPEC_TYPE_OBSERVATION] as $language) {
                $process_specs[$language[QC_SPEC_LANGUAGE_EN]->id] = $language[QC_SPEC_LANGUAGE_EN];
            }
        } else {
            $job_specs = null;
        }

        $files = array();

        if (!empty($job_specs)) {
            $files = $this->job_model->get_files($job->id);

            foreach ($job_specs as $spec_type => $spec_array) {
                $spec_count = 1;

                if (!empty($spec_array)) {
                    foreach ($spec_array as $key => $specs) {
                        $job_specs[$spec_type][$key][QC_SPEC_LANGUAGE_EN]->photos_count = $this->spec_model->get_photos_count($job_id);
                        $job_specs[$spec_type][$key][QC_SPEC_LANGUAGE_EN]->spec_count = $spec_count;

                        if (empty($specs[QC_SPEC_LANGUAGE_CH])) {
                            $job_specs[$spec_type][$key][QC_SPEC_LANGUAGE_EN]->action = 'add_spec';
                            $job_specs[$spec_type][$key][QC_SPEC_LANGUAGE_EN]->hidden_field = 'english_id';
                            $job_specs[$spec_type][$key][QC_SPEC_LANGUAGE_EN]->hidden_value = $specs[QC_SPEC_LANGUAGE_EN]->id;
                        } else {
                            $job_specs[$spec_type][$key][QC_SPEC_LANGUAGE_EN]->action = 'edit_spec';
                            $job_specs[$spec_type][$key][QC_SPEC_LANGUAGE_EN]->hidden_field = 'spec_id';
                            $job_specs[$spec_type][$key][QC_SPEC_LANGUAGE_EN]->hidden_value = $specs[QC_SPEC_LANGUAGE_CH]->id;
                        }

                        $job_specs[$spec_type][$key][QC_SPEC_LANGUAGE_EN]->style = '';

                        $job_specs[$spec_type][$key][QC_SPEC_LANGUAGE_CH]->data =
                            (empty($specs[QC_SPEC_LANGUAGE_CH]->data)) ? 'Enter Chinese specification here...' : $specs[QC_SPEC_LANGUAGE_CH]->data;
                        $job_specs[$spec_type][$key][QC_SPEC_LANGUAGE_CH]->onclick =
                            (empty($specs[QC_SPEC_LANGUAGE_CH]->data)) ? ' onclick="$(this).val(\'\');$(this).css(\'font-style\', \'\');$(this).attr(\'onclick\', \'\');" ' : '';
                        $job_specs[$spec_type][$key][QC_SPEC_LANGUAGE_CH]->style =
                            (empty($specs[QC_SPEC_LANGUAGE_CH]->data)) ? ' style="font-style: italic; text-align: left;" ' : '';

                        $spec_count++;
                    }
                }
            }
        }

        // Add more info to the specs for the purpose of display
        $spec_number = 1;
        $found_additional = false;
        $found_observation = false;

        foreach ($process_specs as $spec_id => $spec) {
            $process_specs[$spec_id] = $process_specs[$spec_id];

            $process_specs[$spec_id]->subnumber = '';
            $process_specs[$spec_id]->rowclass = '';

            if ($process_specs[$spec_id]->language != QC_SPEC_LANGUAGE_EN || $process_specs[$spec_id]->project_id != $project_id) {
                unset($process_specs[$spec_id]);
                continue;
            }

            if ($process_specs[$spec_id]->type == QC_SPEC_TYPE_ADDITIONAL) {
                $process_specs[$spec_id]->subnumber = 'A.';
                $process_specs[$spec_id]->rowclass = 'additional';
                if (!$found_additional) {
                    $spec_number = 1;
                    $found_additional = true;
                }
            } else if ($process_specs[$spec_id]->type == QC_SPEC_TYPE_OBSERVATION) {
                $process_specs[$spec_id]->subnumber = 'B.';
                $process_specs[$spec_id]->rowclass = 'observation';
                if (!$found_observation) {
                    $spec_number = 1;
                    $found_observation = true;
                }
            }

            $process_specs[$spec_id]->spec_number = $spec_number;
            $process_specs[$spec_id]->specs_result = $this->specresult_model->get(array('specs_id' => $spec_id), true);
            $process_specs[$spec_id]->importance_label = get_lang_for_constant_value('QC_SPEC_IMPORTANCE_', $process_specs[$spec_id]->importance);
            $process_specs[$spec_id]->photos_count = $this->spec_model->get_photos_count($spec_id, $job_id);
            $process_specs[$spec_id]->defect_percentage =
                (empty($process_specs[$spec_id]->specs_result->defects))
                ? '0%'
                : round(($process_specs[$spec_id]->specs_result->defects
                    / $project->sample_size) * 100) . '%';

            if (has_capability('qc:editqcprocesses')) {
                $process_specs[$spec_id]->checked = (empty($process_specs[$spec_id]->specs_result->checked)) ? '' : 'checked="checked"';
            } else {
                $process_specs[$spec_id]->checked = (empty($process_specs[$spec_id]->checked)) ? 'No' : 'Yes';
            }

            $spec_number++;
        }


        // Get list of users with QC Inspector role
        // This blacklist excludes most roles other than QC Inspector in order to speed up and narrow down the search.
        // Be careful: if admin changes the capabilities of the Inspector role, this code may return a different list of users!
        $blacklist = array('enquiries:writeenquiries', 'site:doanything', 'qc:doanything', 'qc:editproductspecs', 'enquiries:annotateenquiries', 'qc:writeqcprocesses', 'exchange:doanything');
        $users = $this->role_model->get_users_by_capabilities(array('qc:viewqcspecs', 'qc:viewqcprocesses'), $blacklist);
        $inspectors = array(null => 'Select an inspector...');
        foreach ($users as $user) {
            $inspectors[$user->id] = $this->user_model->get_name($user);
        }

        $title = 'QC Inspector\'s check list & report';
        $main_title = get_title(array('title' => $title, 'expand' => 'page', 'icons' => array()));
        $project_title = get_title(array('title' => 'Project details', 'expand' => 'details', 'icons' => array(), 'level' => 2));
        $process_title = get_title(array('title' => 'Process details', 'expand' => 'report', 'icons' => array('pdf'), 'pdf_url' => 'qc/export_pdf/qc_results/'.$category_id.'/'.$project_id, 'level' => 2));
        $files_title = get_title(array('title' => 'Process files', 'expand' => 'files', 'icons' => array('add'), 'add_url' => 'javascript:add_file()', 'level' => 2));
        $qc_checks_title = get_title(array('title' => 'QC checks to be performed', 'expand' => 'checks', 'icons' => array(), 'level' => 2));
        $additional_title = get_title(array('title' => 'Additional Specifications', 'expand' => 'specifications', 'icons' => array(), 'level' => 2));

        $this->config->set_item('hide_number', true);
        $this->config->set_item('replacer', array('qc' => array('project/browse|QC Projects'),
                                                  'qc_data' => array("/qc/project/edit/$spec->project_id|Edit project $spec->project_id", $title)));

        $pageDetails = array('title' => $title,
                             'main_title' => $main_title,
                             'project_title' => $project_title,
                             'process_title' => $process_title,
                             'qc_checks_title' => $qc_checks_title,
                             'files_title' => $files_title,
                             'additional_title' => $additional_title,
                             'content_view' => 'qc/process/data',
                             'project_id' => $project_id,
                             'category_id' => $category_id,
                             'category_name' => $category->name,
                             'inspectors' => $inspectors,
                             'project_details' => $project_details,
                             'process_specs' => $process_specs,
                             'job_id' => $job_id,
                             'job' => $job,
                             'no_job' => $no_job,
                             'job_specs' => $job_specs,
                             'files' => $files,
                             'disabled' => (has_capability('qc:editqcprocesses')) ? null : 'disabled="disabled"',
                             'jstoloadinfooter' => array('jquery/jquery.domec',
                                                         'jquery/jquery.loading',
                                                         'jquery/jquery.json',
                                                         'jquery/pause',
                                                         'dateformat',
                                                         'application/qc/qc_data',
                                                         'application/qc/specifications',
                                                         'application/qc/process_edit')
                             );

        if (!$no_job) {
            $pageDetails['reportdate'] = (empty($job->report_date)) ? 'Click to select a date...' : mdate('d/m/Y', $job->report_date);
            $pageDetails['inspectiondate'] = (empty($job->inspection_date)) ? 'Click to select a date...' : mdate('d/m/Y', $job->inspection_date);
        }

        $this->load->view('template/default', $pageDetails);
    }

    public function process_file($category_id, $project_id) {


        $job_id = $this->input->post('id');
        $description = $this->input->post('description');

        $config = array('max_size' => 800000000000);
        $config['upload_path'] = $this->config->item('files_path') . 'qc/pdf/qc_jobs/'.$job_id;
        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $config['allowed_types'] = 'pdf|PDF';
        $config['encrypt_name'] = true;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('newfile')) {
            add_message($this->upload->display_errors() . 'Only PDF files are allowed.', 'error');
            redirect("qc/process/qc_data/$category_id/$project_id");
        }

        $file_data = $this->upload->data();

        // If file already exists, delete it and cancel action
        $file_params = array('job_id' => $job_id, 'file' => $file_data['orig_name'], 'hash' => $file_data['file_name']);
        if ($file = $this->jobfile_model->get($file_params, true)) {
            add_message('The file ' . $file_data['orig_name'] . ' was already attached to this job. ', 'warning');
            unlink($config['upload_path'] . '/' . $file_data['file_name']);
            redirect("qc/process/qc_data/$category_id/$project_id");
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

            if ($file_id = $this->jobfile_model->add($file_params)) {
                add_message("The file {$file_params['file']} was uploaded", 'success');
            } else {
                add_message('The file was uploaded, but the file info could not be recorded in the database...', 'warning');
            }
        }

        redirect("qc/process/qc_data/$category_id/$project_id");
    }

    public function add_spec($category_id, $project_id) {

        require_capability('qc:editqcprocesses');

        $job_id = $this->input->post('job_id');
        $spec_data = $this->input->post('data');
        $spec_type = $this->input->post('spec_type');
        $language = $this->input->post('language');
        $english_id = $this->input->post('english_id');

        $spec_params = array('job_id' => $job_id,
                             'type' => $spec_type,
                             'data' => $spec_data,
                             'language' => $language,
                             'category_id' => $category_id,
                             'project_id' => $project_id);

        if ($language == QC_SPEC_LANGUAGE_CH) {
            $spec_params['english_id'] = $english_id;
        }

        $spec_id = $this->spec_model->add($spec_params);
        // Add a matching specresult record
        $this->specresult_model->add(array('specs_id' => $spec_id));
        $this->project_model->flag_as_changed($project_id);
        add_message('A specification has been added', 'success');
        $this->session->set_flashdata('job_id', $job_id);
        redirect("qc/process/qc_data/$category_id/$project_id");
    }

    public function edit_spec($category_id, $project_id) {

        require_capability('qc:editqcprocesses');
        $this->spec_model->edit($this->input->post('spec_id'), array('data' => $this->input->post('data')));
        $this->project_model->flag_as_changed($project_id);
        // Update project status
        $this->project_model->update_acceptance_status($project_id);
        add_message('The specification/observaton has been successfully updated', 'success');
        redirect("qc/process/qc_data/$category_id/$project_id");
    }

    public function delete_spec($category_id, $project_id) {

        require_capability('qc:editqcprocesses');
        $this->spec_model->delete($this->input->post('spec_id'));

        $this->project_model->flag_as_changed($project_id);
        add_message('The specification/observaton has been successfully deleted', 'success');
        redirect("qc/process/qc_data/$category_id/$project_id");
    }

    function get_percentage_dropdown($name, $value, $id) {
        $retval = '<select name="'.$name.'" id="'.$id.'">'."\n";
        for ($i = 0; $i <= 100; $i++) {
            $selected = ($value == $i) ? 'selected="selected"' : '';
            $retval .= '<option '.$selected.' value="'.$i.'">'.$i."</option>\n";
        }
        $retval .= "</select>\n";
        return $retval;
    }

    function add_supplier() {

        require_capability('qc:writeqcprocesses');
        $data['job_id'] = $this->job_model->add(array('supplier_id' => $this->input->post('supplier_id'), 'project_id' => $this->input->post('project_id'), 'category_id' => $this->input->post('category_id')));
        echo json_encode($data);
    }

    function update_job_data() {

        $this->load->helper('date');

        $field = $this->input->post('field');
        $value = $this->input->post('value');
        $job_id = $this->input->post('job_id');

        require_capability('qc:editqcprocesses');

        $job = $this->job_model->get($job_id);

        // In case of a date field, we must convert from d/m/Y to unix timestamp first
        if (preg_match('/(report|inspection)_date/', $field)) {
            $value = human_to_unix($value);
        }

        $this->job_model->edit($job_id, array($field => $value));

        if ($field == 'result') {
            $this->project_model->edit($job->project_id, array('result' => $this->project_model->get_acceptance_status($job->project_id)));
        }

        $data['message'] = "The $field was successfully updated";
        $data['type'] = 'success';
        echo json_encode(stripslashes_deep($data));
        die();
    }

    function update_spec_data() {

        $field = $this->input->post('field');
        $value = $this->input->post('value');
        $job_id = $this->input->post('job_id');
        $spec_id = $this->input->post('spec_id');

        require_capability('qc:editqcprocesses');

        $spec = $this->spec_model->get($spec_id);
        $project = $this->project_model->get_details($spec->project_id);

        if (!($specresult = $this->specresult_model->get(array('specs_id' => $spec_id), true))) { // one-to-one relationship between spec and specresult
            $specresult_id = $this->specresult_model->add(array('specs_id' => $spec_id));
        } else {
            $specresult_id = $specresult->id;
        }

        if (!empty($value) && $field != 'defects') {
            $value = 1;
        } else if ($field != 'defects') {
            $value = 0;
        }

        $this->specresult_model->edit($specresult_id, array($field => $value));

        $specresult->{$field} = $value;

        switch($spec->importance) {
            case QC_SPEC_IMPORTANCE_CRITICAL:
                $permittedPercent = $project['defectcriticallimit'];
                break;
            case QC_SPEC_IMPORTANCE_MAJOR:
                $permittedPercent = $project['defectmajorlimit'];
                break;
            case QC_SPEC_IMPORTANCE_MINOR:
                $permittedPercent = $project['defectminorlimit'];
                break;
        }
        $permitted = $project['samplesize'] * ($permittedPercent / 100);

        // Update category status
        $job_result = $this->job_model->update_acceptance_status($spec->project_id, $spec->category_id);

        // Update project status
        $this->project_model->update_acceptance_status($spec->project_id);

        $data['message'] = "The $field was successfully updated";
        $data['type'] = 'success';
        $data['check_result'] = ($specresult->checked and ($specresult->defects <= $permitted)) ? 'pass' : 'fail';
        $data['job_result'] = $job_result;
        echo json_encode(stripslashes_deep($data));

        die();
    }

    function get_suppliers() {

        $this->load->model('company_model');

        $project_id = $this->input->post('project_id');
        $category_id = $this->input->post('category_id');
        $job_id = $this->input->post('job_id');

        require_capability('qc:viewqcprocesses');
        // Get two separate lists of suppliers: those that are available, minus those that are already assigned
        $suppliers = array('assigned' => array(), 'available' => array());

        // Jobs that have this projectid/categoryid combination
        $currentjob = null;
        if (!empty($job_id)) {
            $currentjob = $this->job_model->get($job_id);
        }

        if ($jobs = $this->job_model->get(array('category_id' => $category_id, 'project_id' => $project_id))) {
            foreach ($jobs as $job) {

                if ($job->supplier_id != 0) {
                    $company = (array) $this->company_model->get($job->supplier_id);

                    // If we have a jobid and its supplier_id matches this one, mark it as "selected"
                    if (!empty($currentjob->supplier_id) && $currentjob->supplier_id == $job->supplier_id) {
                        $company['selected'] = 1;
                    }
                    $suppliers['assigned'][$job->supplier_id] = $company;
                }
            }
        }

        $this->db->where('email IS NOT NULL', null, false);

        if ($companies = $this->company_model->get(array('role' => COMPANY_ROLE_SUPPLIER, 'status' => 'Active'), false, 'name')) {
            foreach ($companies as $company) {
                if (!array_key_exists($company->id, $suppliers['assigned'])) {
                    $suppliers['available'][$company->id] = (array) $company;
                }
            }
        }

        echo json_encode(stripslashes_deep($suppliers));
        die();
    }

    function get_supplier() {

        $this->load->model('company_model');

        $job = $this->job_model->get($this->input->post('job_id'));

        if ($job->supplier_id != 0) {
            echo json_encode((array) $this->company_model->get($job->supplier_id));
        }
        die();
    }
    /**
     * AJAX method used to add and update procedure files
     */
    public function edit_file($type='file') {

        $params = array('description' => $this->input->post('description'),
                        'job_id' => $this->input->post('job_id'),
                        'id' => $this->input->post('file_id'));

        $data = new stdClass();

        $id = $params['id'];
        unset($params['id']);
        if ($this->jobfile_model->edit($id, $params)) {
            $data->message = "This job $type has been successfully updated";
            $data->type = 'success';
        } else {
            $data->message = "This job $type could not be updated";
            $data->type = 'error';
        }

        echo json_encode($data);
    }

    public function delete_file() {

        $file_id = $this->input->post('id');
        $data = new stdClass();

        if ($this->jobfile_model->delete($file_id)) {
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
        require_capability('qc:viewjobs');
        $this->load->helper('download');
        $this->load->model('qc/jobfile_model');
        $file = $this->jobfile_model->get($file_id);

        $data = file_get_contents($this->config->item('files_path') . 'qc/pdf/qc_jobs/'.$file->job_id.'/'.$file->hash);
        force_download($file->file, $data);
    }

}
