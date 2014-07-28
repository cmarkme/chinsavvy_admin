<?php
/**
 * Contains the Procedurefile_Model Model class
 * @package models
 */

/**
 * Procedurefile Model class
 * @package models
 */
class Procedurefile_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    public $table = 'qc_procedure_files';

    /**
     * In addition to deleting the DB record, delete the file on disk if it isn't used by any other procedure
     * In addition to normal delete, update the QC projects that use the linked Procedure
     */
    public function delete($procedurefile_id, $type = 'file') {

        $procedurefile = $this->get($procedurefile_id);

        $files_dir = ROOTPATH . '/files/qc/procedures';

        if ($result = parent::delete($procedurefile_id)) {
            // Check if this file is associated with another procedure
            $this->db->select('id');
            $this->db->where('id <>', $procedurefile_id);
            $otherfile = $this->get(array('hash' => $procedurefile->hash), true);

            if (empty($otherfile)) {
                if (!unlink("$files_dir/$procedurefile->procedure_id/$type"."s/$procedurefile->hash")) {
                    add_message("The $type has been deleted from the database, but could not be deleted from the disk. You may safely ignore this.", 'warning');
                }
            }

            $this->load->model('qc/procedure_model');
            $projects_affected = $this->procedure_model->notify_projects($procedurefile->procedure_id);
            // Could use add_message here to notify of how many projects were flagged

            return true;
        } else {
            return false;
        }
    }

    /**
     * In addition to normal insert, flagged associated QC projects
     */
    public function add($params) {

        $this->load->model('qc/procedure_model');

        if ($id = parent::add($params)) {
            $projects_affected = $this->procedure_model->notify_projects($params['procedure_id']);
            // Could use add_message here to notify of how many projects were flagged
            return true;
        } else {
            return false;
        }
    }
}
