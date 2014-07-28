<?php
/**
 * Contains the Document Model class
 * @package models
 */

/**
 * Document Model class
 * @package models
 */
class Document_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    public $table = 'vault_files';

    /**
     * Returns an array of files recorded in the DB as living in the given folder. Also checks that the files exist, and
     * retrieves information about versioning
     */
    public function get_by_folder($folder) {
        if (empty($folder)) {
            $folder = '';
        }

        $this->db->join('vault_file_versions', 'vault_file_versions.vault_file_id = vault_files.id');
        $this->db->join('users AS staff', 'staff.id = vault_file_versions.staff_id', 'LEFT OUTER');
        $this->db->join('users AS customer', 'customer.id = vault_files.customer_id', 'LEFT OUTER');

        $this->db->select('vault_files.id AS file_id,
                          vault_files.enquiry_id,
                          vault_files.customer_id,
                          vault_files.part_code_id,
                          vault_files.customer_part_code,
                          vault_files.type,
                          vault_files.identity,
                          vault_files.folder,
                          vault_file_versions.id AS file_version_id,
                          vault_file_versions.staff_id,
                          vault_file_versions.original_name,
                          vault_file_versions.new_name,
                          vault_file_versions.description,
                          vault_file_versions.file_size,
                          vault_file_versions.creation_date,
                          vault_file_versions.revision_date,
                          vault_file_versions.version,
                          customer.first_name AS customer_first_name,
                          customer.surname AS customer_surname,
                          staff.first_name AS staff_first_name,
                          staff.surname AS staff_surname');

        $this->db->order_by('version DESC');

        $files = $this->get(array('vault_files.folder' => $folder));

        $files_array = array();

        foreach ($files as $version) {
            $file_location = $this->config->item('files_path') . 'vault/' . $version->folder . '/' . $version->new_name;
            $file_location = str_replace('//', '/', $file_location);

            if (!file_exists($file_location)) {
                continue;
            }

            if (empty($files_array[$version->file_id])) {
                $files_array[$version->file_id] = array('versions' => array(),
                    'file_id' => $version->file_id,
                    'enquiry_id' => $version->enquiry_id,
                    'customer_id' => $version->customer_id,
                    'part_code_id' => $version->part_code_id,
                    'type' => $version->type,
                    'identity' => $version->identity,
                    'folder' => $version->folder,
                    'customer_id' => $version->customer_id,
                    'customer_first_name' => $version->customer_first_name,
                    'customer_surname' => $version->customer_surname);
            }

            $files_array[$version->file_id]['versions'][] = array(
                'file_version_id' => $version->file_version_id,
                'original_name' => $version->original_name,
                'new_name' => $version->new_name,
                'description' => $version->description,
                'file_size' => $version->file_size,
                'creation_date' => $version->creation_date,
                'revision_date' => $version->revision_date,
                'staff_id' => $version->staff_id,
                'staff_first_name' => $version->staff_first_name,
                'staff_surname' => $version->staff_surname,
                'version' => $version->version);
        }

        // Remove any file that has no valid version (if files are missing from disk)
        foreach ($files_array as $key => $file) {
            if (empty($file['versions'])) {
                unset($files_array[$key]);
            }
        }

        return $files_array;
    }
}
