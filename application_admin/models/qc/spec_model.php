<?php
/**
 * Contains the Spec_Model Model class
 * @package models
 */

/**
 * Spec Model class
 * @package models
 */
class Spec_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    public $table = 'qc_specs';

    public function get_with_cat_data($params, $first_only=false, $order_by=null, $type=QC_SPEC_CATEGORY_TYPE_PRODUCT) {
        $this->db->join('qc_spec_categories', 'qc_specs.category_id = qc_spec_categories.id');
        if (!empty($type)) {
            $this->db->where('qc_spec_categories.type', $type);
        }

        $spec_fields = $this->spec_model->get_formatted_column_names('spec_%s');
        $spec_cat_fields = $this->speccategory_model->get_formatted_column_names('speccategory_%s');

        foreach ($spec_fields as $original => $field) {
            $this->db->select("$original AS $field");
        }
        foreach ($spec_cat_fields as $original => $field) {
            $this->db->select("$original AS $field");
        }

        return $this->get($params, $first_only, $order_by);
    }

    public function get_with_result_data($params, $first_only=false, $order_by=null) {
        $this->db->join('qc_specs_results', 'qc_specs.id = qc_specs_results.specs_id');

        return $this->get($params, $first_only, $order_by);
    }

    /**
     * Must call the spec photo's delete method to make sure the files and chinese specs are deleted properly
     */
    public function delete($spec_id) {

        $this->db->select('id');

        if (!$this->session->userdata('last_qc_spec_deleted')) {
            $this->session->set_userdata('last_qc_spec_deleted', time());
        }

        $current_time = time();
        $diff = floor($current_time - $this->session->userdata('last_qc_spec_deleted'));
        $recent_qc_deletions = $this->session->userdata('recent_qc_deletions');

        if ($diff < 1 && !$recent_qc_deletions) {
            $this->session->set_userdata('recent_qc_deletions', 2);
        } else if ($diff < 1 && $recent_qc_deletions) {
            $this->session->set_userdata('recent_qc_deletions', ++$recent_qc_deletions);
        } else if ($diff >= 1) {
            $this->session->set_userdata('recent_qc_deletions', null);
        }

        if ($diff < 1 && $recent_qc_deletions > 200) { // Unlikely to be more than 200 specs in one QC project
            log_user_action("has triggered flood control for deletions of qc specs, $diff ms, $recent_qc_deletions deletions");
            return false;
        }

        $this->load->model('qc/specphoto_model');
        if ($specphotos = $this->specphoto_model->get(array('spec_id' => $spec_id))) {
            foreach ($specphotos as $specphoto) {
                $this->specphoto_model->delete($specphoto->id);
            }
        }

        $this->db->select('id');
        if ($chinese_specs = $this->spec_model->get(array('english_id' => $spec_id))) {
            foreach ($chinese_specs as $chinese_spec) {
                $this->spec_model->delete($chinese_spec->id);
            }
        }

        $this->session->set_userdata('last_qc_spec_deleted', time());
        return parent::delete($spec_id);
    }

    public function get_photos_count($spec_id, $jobid=false) {

        $this->load->model('qc/jobphoto_model');

        $photo_params = array('spec_id' => $spec_id);

        if ($jobid) {
            $photo_params['job_id'] = $jobid;
        }

        $this->db->select('id');
        $photos = $this->jobphoto_model->get($photo_params);

        $count = count($photos);
        $photoscount = '';

        if ($count) {
            $photoscount = '('.$count.')';
        }

        return $photoscount;
    }

    public function get_procedures($spec_id) {

        $this->load->model('qc/procedure_model');
        $this->db->select('qc_procedures.id, number, title')->from('qc_procedures')->join('qc_specs_procedures', "qc_procedures.id = qc_specs_procedures.procedure_id")->where('spec_id', $spec_id);
        $procedures = array();
        $query = $this->db->get();
        foreach ($query->result() as $row) {
            $procedures[$row->id] = $row->number . ': ' . $row->title;
        }
        return $procedures;
    }
}
