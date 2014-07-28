<?php
/**
 * Contains the Speccategory_Model Model class
 * @package models
 */

/**
 * Speccategory Model class
 * @package models
 */
class Speccategory_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    public $table = 'qc_spec_categories';

    public static $files_id = 0;

    /**
     * The Files category is a special category that is unique and used by many projects.
     * We use a static variable that only needs to obtained from the DB once per script execution.
     * @return int
     */
    public function get_files_id() {

        if (empty(Speccategory_Model::$files_id)) {
            $files_category = $this->get(array('name' => 'Files'), true);
            Speccategory_Model::$files_id = $files_category->id;
        }

        return Speccategory_Model::$files_id;
    }

    // Must call the spec's delete function too, so that photos are deleted correctly
    public function delete($category_id) {

        $this->db->select('id');
        if ($specs = $this->spec_model->get(array('category_id' => $category_id))) {
            $specs_to_delete = array();
            foreach ($specs as $spec) {
                $specs_to_delete[] = $spec->id;
            }
            $this->db->where_in('id', $specs_to_delete);
            $spec_count = count($specs_to_delete);
            log_user_action("is now deleting $spec_count qc_specs linked to spec category #$category_id (MODEL)");
            $this->db->delete('qc_specs');
        }
        return parent::delete($category_id);
    }

    public function get_specs($category_id, $project_id=null) {

        $specsarray = array();
        $spec_params = array('category_id' => $category_id, 'job_id' => 0);

        if (!empty($project_id)) {
            $spec_params['project_id'] = $project_id;
        }

        if ($specs = $this->spec_model->get($spec_params, false, 'type, creation_date')) {
            foreach ($specs as $spec) {
                $specsarray[$spec->id] = $spec;
            }
        }
        return $specsarray;
    }
}
