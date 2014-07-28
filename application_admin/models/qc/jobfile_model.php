<?php
/**
 * Contains the Jobfile_Model Model class
 * @package models
 */

/**
 * Jobfile Model class
 * @package models
 */
class Jobfile_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    public $table = 'qc_job_files';

    /**
     * In addition to deleting the DB record, delete the file on disk if it isn't used by any other job
     */
    public function delete($jobfile_id, $type = 'file') {

        $jobfile = $this->get($jobfile_id);

        $files_dir = ROOTPATH . '/files/qc/pdf/qc_jobs';

        if ($result = parent::delete($jobfile_id)) {
            // Check if this file is associated with another job
            $this->db->select('id');
            $this->db->where('id <>', $jobfile_id);
            $otherfile = $this->get(array('hash' => $jobfile->hash), true);

            if (empty($otherfile)) {
                if (!unlink("$files_dir/$jobfile->job_id/$jobfile->hash")) {
                    add_message("The $type has been deleted from the database, but could not be deleted from the disk. You may safely ignore this.", 'warning');
                }
            }

            return true;
        } else {
            return false;
        }
    }

}
