<?php
/**
 * Contains the Projectfile_Model Model class
 * @package models
 */

/**
 * Projectfile Model class
 * @package models
 */
class Projectfile_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'qc_project_files';

    /**
     * In addition to deleting the DB record, delete the file on disk if it isn't used by any other project
     * In addition to normal delete, update the linked QC spec if it exists
     */
    public function delete($projectfile_id) {

        $projectfile = $this->get($projectfile_id);

        $qc_files_dir = ROOTPATH . '/files/qc';

        if ($result = parent::delete($projectfile_id)) {
            // Check if this file is associated with another project
            $this->db->select('id');
            $this->db->where('id <>', $projectfile_id);
            $otherfile = $this->get(array('hash' => $projectfile->hash), true);

            if (empty($otherfile)) {
                if (!unlink("$qc_files_dir/$projectfile->project_id/$projectfile->hash")) {
                    add_message('The file has been deleted from the database, but could not be deleted from the disk. You may safely ignore this.', 'warning');
                }
            }

            $this->db->select('id');
            $spec_params = array('type' => QC_SPEC_TYPE_NORMAL,
                                 'language' => QC_SPEC_LANGUAGE_EN,
                                 'datatype' => QC_SPEC_DATATYPE_STRING,
                                 'file_id' => $projectfile_id);

            $spec = $this->spec_model->get($spec_params, true);

            if (!empty($spec)) {
                $this->spec_model->delete($spec->id);
                $this->project_model->flag_as_changed($projectfile->project_id);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * In addition to normal insert, create a QC spec unless otherwise specified
     */
    public function add($params, $no_spec=false) {
        if (empty($params['project_id'])) {
            add_message('A project ID is required in order to add a QC project file record!', 'error');
            return false;
        }
        if (empty($params['file'])) {
            add_message('A file name is required in order to add a QC project file record!', 'error');
            return false;
        }


        $file_id = parent::add($params);

        if (!$no_spec) {
            return $file_id;
        }

        $category = $this->speccategory_model->get(array('name' => 'Files'), true);

        if (!empty($category)) {
            $files_category_id = $category->id;
        } else {
            $files_category_id = $this->speccategory_model->add(array('name' => 'Files'));
        }

        $spec_params = array('type' => QC_SPEC_TYPE_NORMAL,
                             'language' => QC_SPEC_LANGUAGE_EN,
                             'datatype' => QC_SPEC_DATATYPE_STRING,
                             'file_id' => $file_id,
                             'project_id' => $params['project_id'],
                             'category_id' => $files_category_id,
                             'data' => $params['file']
                             );

        if (!($spec = $this->spec_model->get($spec_params, true))) {
            $this->spec_model->add($spec_params);
            $this->project_model->flag_as_changed($params['project_id']);
            return true;
        }

        return false;
    }
}
