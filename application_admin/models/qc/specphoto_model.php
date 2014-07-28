<?php
/**
 * Contains the Specphoto_Model Model class
 * @package models
 */

/**
 * Specphoto Model class
 * @package models
 */
class Specphoto_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'qc_spec_photos';

    /**
     * In addition to deleting the DB record, delete the file on disk if it isn't used by any other spec
     */
    public function delete($specphoto_id, $project_id=null) {


        $specphoto = $this->get($specphoto_id);

        if (is_null($project_id)) {
            $spec = $this->spec_model->get($specphoto->spec_id);
            $project_id = $spec->project_id;
        }

        if ($result = parent::delete($specphoto_id)) {
            $this->db->where('id <>', $specphoto_id);
            $otherspecphoto = $this->specphoto_model->get(array('hash' => $specphoto->hash), true);
            $qc_photos_dir = ROOTPATH.'/files/qc/photos';

            if (!empty($otherspecphoto)) {
                if (!unlink("$qc_photos_dir/$project_id/$otherjobphoto->hash")) {
                    add_message('The photo record has been deleted from the database, but the file could not be deleted from the disk. You may safely ignore this.', 'warning');
                }
                unlink("$qc_photos_dir/$project_id/small/$otherjobphoto->hash");
                unlink("$qc_photos_dir/$project_id/thumb/$otherjobphoto->hash");
            }
            return true;
        } else {
            return false;
        }
    }
}
