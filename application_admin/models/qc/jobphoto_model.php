<?php
/**
 * Contains the Jobphoto_Model Model class
 * @package models
 */

/**
 * Jobphoto Model class
 * @package models
 */
class Jobphoto_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'qc_job_photos';

    /**
     * In addition to deleting the DB record, delete the file on disk if it isn't used by any other spec
     */
    public function delete($jobphoto_id, $project_id=null) {

        $this->load->model('qc/spec_model');
        $this->load->model('qc/specphoto_model');

        $jobphoto = $this->get($jobphoto_id);

        if (is_null($project_id)) {
            $spec = $this->spec_model->get($jobphoto->spec_id);
            $project_id = $spec->project_id;
        }

        if ($result = parent::delete($jobphoto_id)) {
            $specphoto = $this->specphoto_model->get(array('hash' => $jobphoto->hash), true);
            $qc_photos_dir = ROOTPATH."/files/qc/$project_id/process/$jobphoto->job_id";

            if (!empty($specphoto)) {
                if (!unlink("$qc_photos_dir/$jobphoto->hash")) {
                    add_message('The photo record has been deleted from the database, but the file could not be deleted from the disk. You may safely ignore this.', 'warning');
                }
                unlink("$qc_photos_dir/small/$jobphoto->hash");
                unlink("$qc_photos_dir/thumb/$jobphoto->hash");
            }
            return true;
        } else {
            return false;
        }
    }
}
