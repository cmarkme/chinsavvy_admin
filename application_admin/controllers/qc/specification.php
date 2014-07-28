<?php
/**
 * Contains the Specification Controller class
 * @package controllers
 */

/**
 * Specification Controller class
 * @package controllers
 */
class Specification extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('qc/project_model');
        $this->load->model('qc/spec_model');
        $this->load->model('qc/specphoto_model');
        $this->load->model('qc/specrevision_model');
        $this->load->model('qc/speccategory_model');
        $this->load->model('qc/job_model');
        $this->load->model('qc/revision_model');
        $this->load->model('qc/projectrelated_model');
        $this->load->model('qc/projectfile_model');
        $this->load->model('qc/projectpart_model');
        $this->load->model('qc/procedure_model');
    }

    function delete_part($part_id) {

        $this->db->select('project_id')->select('name');

        if (!($part = $this->projectpart_model->get($part_id))) {
            $data['message'] = "This part has already been deleted.".$this->db->last_query();
            $data['type'] = 'warning';
            echo json_encode(stripslashes_deep($data));
            die();
        }

        $this->projectpart_model->delete($part_id);

        $data['message'] = "The part $part->name has been successfully deleted";
        $data['type'] = 'success';
        echo json_encode(stripslashes_deep($data));
        $this->project_model->flag_as_changed($part->project_id);
        die();
    }

    function delete_file($file_id) {

        $this->db->select('project_id')->select('file');
        $file = $this->projectfile_model->get($file_id);
        $this->projectfile_model->delete($file_id);

        $data['message'] = "The file $file->file has been successfully deleted";
        $data['type'] = 'success';
        echo json_encode(stripslashes_deep($data));
        $this->project_model->flag_as_changed($file->project_id);
        die();
    }

    function new_part($project_id) {

        $value = $this->input->post('value');
        $this->part_model->add(array('project_id' => $project_id, 'name' => $value));
        echo stripslashes(nl2br($value));
        $this->project_model->flag_as_changed($project_id);
        die();
    }

    function edit_value($project_id) {

        $value = $this->input->post('value');
        $field = $this->input->post('field');

        if ($field == 'newpart') {
            $part_id = $this->projectpart_model->add(array('name' => $value, 'project_id' => $project_id));
            $part = $this->projectpart_model->get($part_id);
            $this->project_model->flag_as_changed($project_id);
        } else {
            $this->db->select('project_id');
            preg_match('/edit_([a-z]*)_([0-9]*)/', $field, $matches);
            $part = $this->projectpart_model->get($matches[2]);
            $this->projectpart_model->edit($matches[2], array($matches[1] => $value));
            $this->project_model->flag_as_changed($part->project_id);
        }
        echo stripslashes(nl2br($value));

        die();
    }

    function edit_speccategory($category_id) {

        $value = $this->input->post('value');
        $spec_id = $this->input->post('specid');
        $language = $this->input->post('language');
        $project_id = $this->input->post('projectid');
        $english_id = $this->input->post('englishid');

        if ($spec_id == 'null') {
            $spec_id = null;
        }

        $spec_params = array('category_id' => $category_id);

        if (empty($spec_id) && $language == QC_SPEC_LANGUAGE_EN) { // Dealing with a new english spec
            $spec_params['language'] = QC_SPEC_LANGUAGE_EN;
            $spec_params['data'] = $value;
            $spec_params['project_id'] = $project_id;
            $this->spec_model->add($spec_params);
            $this->project_model->flag_as_changed($project_id);
            echo stripslashes(nl2br($value));
            die();
        } else if (!empty($spec_id)) { // Editing an existing spec
            $spec = $this->spec_model->get($spec_id);
        } else if (!empty($english_id)) { // New Chinese spec, englishid required (So we can't add chinese spec before english one is entered?)
            $spec_params['english_id'] = $english_id;
            $spec_params['language'] = $language;

            if (!($spec = $this->spec_model->get($spec_params, true))) {
                // Create the spec
                $englishspec = $this->spec_model->get($english_id);
                $spec_params = array('english_id' => $english_id,
                                     'language' => $language,
                                     'project_id' => $englishspec->project_id,
                                     'category_id' => $englishspec->category_id,
                                     'datatype' => $englishspec->datatype,
                                     'units' => $englishspec->units,
                                     'data' => $value);

                $this->spec_model->add($spec_params);
                $this->project_model->flag_as_changed($project_id);
                echo stripslashes(nl2br($value));
                die();
            }
        } else {
            echo ' ';
            die();
        }

        $original = $spec->data;
        $this->project_model->flag_as_changed($project_id);

        if ($this->spec_model->edit($spec->id, array('data' => $value))) {
            echo stripslashes(nl2br($value));
        } else {
            echo stripslashes(nl2br($original));
        }

        die();
    }

    function add_spec($project_id, $category_id) {

        $value = $this->input->post('value');
        $newspec_params = array('project_id' => $project_id, 'category_id' => $category_id, 'data' => $value, 'language' => QC_SPEC_LANGUAGE_EN);

        $this->project_model->flag_as_changed($project_id);

        if ($id = $this->spec_model->add($newspec_params)) {
            $result = $this->project_model->get_acceptance_status($project_id);
            $this->project_model->edit($project_id, array('result' => $result));
            echo "~$id~" . stripslashes(nl2br($value));
        }
        die();
    }

    function delete_spec($spec_id) {

        log_user_action("is now attempting to delete QC spec #$spec_id");
        $this->db->select('project_id');
        $spec = $this->spec_model->get($spec_id);
        $this->spec_model->delete($spec_id);
        $data['message'] = "The specification was successfully deleted in all languages.";
        $data['type'] = 'success';
        $this->project_model->flag_as_changed($spec->project_id);
        echo json_encode(stripslashes_deep($data));
        die();
    }

    function delete_speccategory($category_id, $project_id) {

        $this->db->select('id');
        log_user_action("is now attempting to delete QC spec category #$category_id from the QC project #$project_id");

        if ($specs = $this->spec_model->get(array('category_id' => $category_id, 'project_id' => $project_id))) {
            foreach ($specs as $spec) {
                $this->spec_model->delete($spec->id);
            }
        }

        $this->project_model->flag_as_changed($project_id);

        $result = $this->project_model->get_acceptance_status($project_id);
        $this->project_model->edit($project_id, array('result' => $result));

        $data['message'] = "The specification category was successfully deleted along with all its specifications (for this project only).";
        $data['type'] = 'success';
        echo json_encode(stripslashes_deep($data));
        die();

    }

    function create_category($type, $value) {

        // Clean up the value, as it comes from a GET URL
        $value = urldecode($value);
        $category_params = array('name' => $value, 'type' => $type);
        $data = array();

        if (!($category = $this->speccategory_model->get($category_params, true))) {
            $id = $this->speccategory_model->add($category_params);
            $data['message'] = "The specification category '$value' was successfully created.";
            $data['type'] = 'success';
            $data['categoryid'] = $id;
        }

        echo json_encode(stripslashes_deep($data));
        die();
    }

    function save_revision($project_id) {

        $this->project_model->save_revision($project_id);
        $project = $this->project_model->get($project_id);
        $data['revisionnumber'] = $project->revision_string;
        $data['message'] = "Revision #{$data['revisionnumber']} has been successfully saved";
        $data['type'] = 'success';
        echo json_encode(stripslashes_deep($data));
        die();
    }

    function update_importance($spec_id, $value) {

        $this->db->select('project_id');
        $spec = $this->spec_model->get($spec_id);
        $this->spec_model->edit($spec_id, array('importance' => $value));

        $data['message'] = "The importance level of spec #$spec_id has been set to " . get_lang_for_constant_value('QC_SPEC_IMPORTANCE_',$value);
        $data['type'] = 'success';
        $this->project_model->flag_as_changed($spec->project_id);
        echo json_encode(stripslashes_deep($data));
        die();
    }

    function get_json_data($project_id) {


        $data['additionalspecs'] = $this->project_model->get_specs($project_id, false, true, QC_SPEC_TYPE_ADDITIONAL);
        $data['observationsspecs'] = $this->project_model->get_specs($project_id, false, true, QC_SPEC_TYPE_OBSERVATION);
        $data['productspecs'] = $this->project_model->get_specs($project_id, QC_SPEC_CATEGORY_TYPE_PRODUCT);
        $data['qcspecs'] = $this->project_model->get_specs($project_id, QC_SPEC_CATEGORY_TYPE_QC);
        $data['parts'] = $this->project_model->get_parts($project_id);
        $data['jobs'] = $this->project_model->get_jobs($project_id);
        $data['suppliers'] = $this->project_model->get_suppliers($project_id, $data['jobs']);
        $data['files'] = $this->project_model->get_files($project_id);

        echo json_encode(stripslashes_deep($data));
    }

    function get_categories($type) {

        $term = $this->input->post('term');
        $type = ($type == 'qc') ? QC_SPEC_CATEGORY_TYPE_QC : QC_SPEC_CATEGORY_TYPE_PRODUCT;
        $this->db->where("name LIKE '$term%'");
        $this->db->select('id as value', false);
        $this->db->select('name as label', false);
        $cats = $this->speccategory_model->get(array('type' => $type), false, 'name ASC');
        echo json_encode($cats);
    }

    function view_file($file_id) {

        if (!$file = $this->projectfile_model->get($file_id)) {
            add_message('The requested file could not be found in the database!', 'error');
            redirect("qc/project/edit/$file->project_id");
        }

        $fullpath = $this->config->item('files_path') . "qc/$file->project_id/$file->hash";
        if (!file_exists($fullpath)) {
            $fullpath = $this->config->item('files_path') . "qc/$file->project_id/$file->file";
        }

        if (!file_exists($fullpath)) {
            add_message('The requested file could not be found on disk!', 'error');
            redirect("qc/project/edit/$file->project_id");
        }

        $this->load->helper('file');
        if ($mimetype = get_mime_by_extension($fullpath)) {
            header("Content-type:$mimetype");
        } else {
            header("Content-type:octet-stream");
        }

        header('Content-Disposition:attachment;filename="'.$file->file.'"');
        readfile($fullpath);

    }

    public function assign_procedure($spec_id, $procedure_id) {

        $result = $this->procedure_model->assign_procedure_to_spec($procedure_id, $spec_id);

        $data = array();

        if ($result === true) {
            $data['message'] = "This procedure has been successfully associated with the QA spec";
            $data['type'] = 'success';
        } else {
            $data['message'] = $result;
            $data['type'] = 'error';
        }

        echo json_encode(stripslashes_deep($data));
        die();
    }

    function delete_spec_procedure($spec_id, $procedure_id) {

        $this->db->delete('qc_specs_procedures', array('spec_id' => $spec_id, 'procedure_id' => $procedure_id));
        $data['message'] = "The procedure was successfully disassociated from this spec.";
        $data['type'] = 'success';
        echo json_encode(stripslashes_deep($data));
        die();
    }
}
