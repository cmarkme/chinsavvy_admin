<?php
/**
 * Contains the Enquiry_File Model class
 * @package models
 */

/**
 * Enquiry_File Model class
 * @package models
 */
class Enquiry_File_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'enquiries_files';

    public function delete($id)
    {
    	$file = $this->get($id, true);
    	$success = parent::delete($id);

        if ($success)
        {
        	$path = $this->config->item('files_path') . 'enquiries/'. $file->location . $file->filename_new;
            unlink($path);
        }

        return $success;
    }
}
