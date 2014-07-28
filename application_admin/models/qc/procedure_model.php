<?php
/**
 * Contains the Procedure_Model Model class
 * @package models
 */

/** * Procedure Model class
 * @package models
 */
class Procedure_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    public $table = 'qc_procedures';

    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {

        $this->load->helper('date');

        $this->dbfields = array($this->dbfield->get_field('qc_procedures.id', 'procedure_id', 'id'),
                                $this->dbfield->get_field('qc_procedures.number', 'procedure_number', 'Number'),
                                $this->dbfield->get_field('qc_procedures.title', 'procedure_title', 'Title'),
                                $this->dbfield->get_field('qc_procedures.version', 'procedure_version', 'Version'),
                                $this->dbfield->get_field('qc_procedures.updated_by', 'procedure_updated_by', 'Last updated by'),
                                $this->dbfield->get_field('qc_procedures.revision_date', 'procedure_revision_date', 'Last update')
                                );

        // Add users.id for filtering
        parent::apply_db_selects();

        $numrows = parent::filter($params, $filters);

        if ($limit) {
            $this->db->limit($limit);
        }

        // For table headings
        $table_data = array('headings' => parent::get_table_headings(),
                            'rows' => array(),
                            'numrows' => $numrows);

        $query = parent::get_with_aliased_columns();

        foreach ($query->result() as $procedure) {
            // TODO substitute update_by by name of user
            $exceptions = array();
            $row = parent::get_table_row_from_db_record($procedure, $exceptions);
            $row[4] = $this->user_model->get_name($row[4], 'f l');
            $table_data['rows'][] = $row;
        }

        return $table_data;
    }

    /**
     * Looks up all the QC projects that have QA specs that use this procedure, and flags them as "has_updated_procedures"
     * @param int $procedure_id
     * @return int The number of flagged projects
     */
    public function notify_projects($procedure_id) {
        $procedure_id = (int) $procedure_id; // protecting against injection attack


        $sql = "UPDATE qc_projects SET has_updated_procedures = 1 WHERE id IN
         (SELECT DISTINCT project_id FROM qc_specs JOIN qc_specs_procedures ON qc_specs.id = qc_specs_procedures.spec_id WHERE procedure_id = $procedure_id)";
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function get_items($procedure_id) {

        $this->load->model('qc/procedureitem_model');
        return $this->procedureitem_model->get(array('procedure_id' => $procedure_id), false, 'number');
    }

    public function get_files($procedure_id) {

        $this->load->model('qc/procedurefile_model');
        return $this->procedurefile_model->get(array('procedure_id' => $procedure_id, 'is_image' => 0));
    }

    public function get_photos($procedure_id) {

        $this->load->model('qc/procedurefile_model');
        return $this->procedurefile_model->get(array('procedure_id' => $procedure_id, 'is_image' => 1));
    }

    public function get_projects($procedure_id) {
        $procedure_id = (int) $procedure_id; // protecting against injection attack

        $projects = array();

        $sql = "SELECT id FROM qc_projects WHERE id IN
         (SELECT DISTINCT project_id FROM qc_specs JOIN qc_specs_procedures ON qc_specs.id = qc_specs_procedures.spec_id WHERE procedure_id = $procedure_id)";
        $query = $this->db->query($sql);

        if ($query->num_rows > 0) {
            foreach ($query->result() as $row) {
                $projects[$row->id] = $this->project_model->get_details($row->id);
            }
        }

        return $projects;
    }

    public function get_next_number() {

        $query = $this->db->select_max('number')->from($this->procedure_model->table)->get();
        $result_array = $query->result();
        $current_number = $result_array[0]->number;

        if ($current_number < 1000) {
            return 1001;
        } else {
            return $current_number + 1;
        }
    }

    public function assign_procedure_to_spec($procedure_id, $spec_id) {

        $params = array('procedure_id' => $procedure_id, 'spec_id' => $spec_id);
        // Check if the combination already exists
        $query = $this->db->from('qc_specs_procedures')->where($params)->get();

        if ($query->num_rows() > 0) {
            return 'This procedure is already associated with this QA spec, please choose another one';
        }

        // Proceed with insertion
        $params += array('creation_date' => mktime(), 'revision_date' => mktime());
        if ($this->db->insert('qc_specs_procedures', $params)) {
            return true;
        } else {
            return 'This procedure could not be associated with this QA spec';
        }
    }
}
