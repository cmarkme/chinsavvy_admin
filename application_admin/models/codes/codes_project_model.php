<?php
/**
 * Contains the Project Model class
 * @package models
 */

/**
 * Project Model class
 * @package models
 */
class Codes_project_Model extends MY_Model {
    /**
     * @var string The DB table used by this model
     */
    public $table = 'codes_projects';

    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {


        $this->load->helper('date');
        $this->dbfields = array($this->dbfield->get_field('codes_projects.id', 'codes_projects_id', 'ID'),
                                $this->dbfield->get_field('CONCAT(codes_divisions.code,DATE_FORMAT(FROM_UNIXTIME(codes_projects.creation_date), "%y"),".",codes_projects.number)', 'project_number', 'Number'),
                                $this->dbfield->get_field('companies.code', 'company_code', 'CUST'),
                                $this->dbfield->get_field('codes_projects.name', 'codes_projects_name', 'Name'),
                                $this->dbfield->get_field('codes_projects.description', 'codes_projects_description', 'Description'),
                                $this->dbfield->get_field('codes_projects.due_date', 'codes_projects_due_date', 'Due date'),
                                $this->dbfield->get_field('codes_projects.creation_date', 'codes_projects_creation_date', 'Date'),
                                $this->dbfield->get_field('codes_projects.completed', 'codes_projects_completed', 'Completed')
                                );

        // Unique Linked fields
        $this->db->join('codes_divisions', 'codes_projects.division_id = codes_divisions.id');
        $this->db->join('companies', 'companies.id = codes_projects.company_id');

        parent::apply_db_selects();

        $this->db->group_by('codes_projects_id');

        $numrows = parent::filter($params, $filters);

        if ($limit) {
            $this->db->limit($limit);
        }

        // For table headings
        $table_data = array('headings' => parent::get_table_headings(),
                            'rows' => array(),
                            'numrows' => $numrows);

        $query = parent::get_with_aliased_columns();

        foreach ($query->result() as $row) {
            $completed = "No";
            if ($row->codes_projects_completed) {
                $completed = "Yes";
            }

            $table_data['rows'][] = parent::get_table_row_from_db_record($row, array('codes_projects_completed' => $completed));
        }

        return $table_data;
    }

    public function get_number($project_id) {

        $this->db->join('codes_divisions', 'codes_projects.division_id = codes_divisions.id');
        $this->db->join('companies', 'companies.id = codes_projects.company_id');
        $this->db->select('CONCAT(codes_divisions.code,DATE_FORMAT(FROM_UNIXTIME(codes_projects.creation_date), "%y"),".",codes_projects.number) AS project_number', false);
        $project = $this->get($project_id);
        if (!empty($project->project_number)) {
            return $project->project_number;
        }
    }

    /**
     * Looks up existing projects and generates a new project number based on what's there
     */
    public function generate_number() {
        // Find if there is another number between 01/01 of the current year and now. If not, return 1000


        $this->db->select('number');
        $this->db->where('creation_date BETWEEN ' . mktime(0,0,1,1,1, date('Y')) . ' AND ' . mktime());

        if (!($project = $this->get(null, true, 'creation_date DESC'))) {
            return 1000;
        } else {
            // Double-check the uniqueness of this number
            $project_number = $project[0]->number;
            $newnumber = $project_number + 1;
            $is_unique = false;

            while (!$is_unique) {
                $this->db->where('CONCAT(DATE_FORMAT(FROM_UNIXTIME(codes_projects.creation_date), "%y"), ".", codes_projects.number) =
                                    "' . date('y') . '.' . $newnumber . '"');

                if (!$this->get()) {
                    $is_unique = true;
                } else {
                    $newnumber++;
                }
            }
            return $newnumber;
        }
    }

    /**
     * In addition to normal update, propagate the due date and the status fields to each associated part.
     */
    public function edit($project_id, $params=array()) {

        $this->load->model('codes/part_model');

        $original_project = $this->get($project_id);

        $parts = $this->part_model->get(array('project_id' => $project_id));
        $edit_params = array();
        $due_date = (isset($params['due_date'])) ? $params['due_date'] : $original_project->due_date;
        $status_date = (isset($params['status_date'])) ? $params['status_date'] : $original_project->status_date;
        $status_text = (isset($params['status_text'])) ? $params['status_text'] : $original_project->status_text;
        $status_description = (isset($params['status_description'])) ? $params['status_description'] : $original_project->status_description;

        if (!empty($parts)) {
            foreach ($parts as $part) {

                $needsupdate = false;
                $edit_params = array();

                if (!empty($due_date) && ($part->overridden & CODES_OVERRIDDEN_DUE_DATE) == 0) {
                    $edit_params['due_date'] = $due_date;
                    $needsupdate = true;
                }

                if (!empty($status_text)) {
                    if (($part->overridden & CODES_OVERRIDDEN_STATUS_TEXT) == 0) {
                        $edit_params['status_text'] = $status_text;
                        $needsupdate = true;
                    }

                    if ($original_project->status_text != $status_text) {
                        $status_date = mktime();
                    }
                }

                if (!empty($status_description)) {
                    if (($part->overridden & CODES_OVERRIDDEN_STATUS_DESCRIPTION) == 0) {
                        $edit_params['status_description'] = $status_description;
                        $needsupdate = true;
                    }

                    if ($original_project->status_description != $status_description) {
                        $status_date = mktime();
                    }
                }

                if (!empty($status_date) && ($part->overridden & CODES_OVERRIDDEN_STATUS_DATE) == 0) {
                    $edit_params['status_date'] = $status_date;
                    $needsupdate = true;
                }

                if ($needsupdate) {
                    $this->part_model->edit($part->id, $edit_params, 'project');
                }
            }
        }

        return parent::edit($project_id, $params);
    }

    /**
     * Duplicates this project along with all its associated parts
     *
     * @access public
     * @return void
     */
    public function duplicate($project_id=null) {

        $this->load->model('codes/part_model');

        if (empty($project_id)) {
            die('You cannot duplicate a project without a project_id number');
        }

        $errors = false;

        $project = $this->codes_project_model->get($project_id);
        $new_project = (array) $project;

        unset($new_project['id']);
        unset($new_project['completed']);
        unset($new_project['revision_date']);
        unset($new_project['creation_date']);

        $new_project['number'] = $this->codes_project_model->generate_number();

        $inserted_parts = array();

        if ($new_project['id'] = $this->codes_project_model->add($new_project)) {
            $parts = $this->part_model->get(array('project_id' => $project_id));

            foreach ($parts as $part) {
                $new_part = (array) $part;
                unset($new_part['id']);
                unset($new_part['completed']);
                unset($new_part['revision_date']);
                unset($new_part['creation_date']);
                $new_part['project_id'] = $new_project['id'];

                if (!($new_part['id'] = $this->part_model->add($new_part))) {
                    $errors = true;
                    break;
                } else {
                    $inserted_parts[] = $new_part;
                }
            }
        } else {
            add_message('Could not duplicate the project. Insertion of the new project failed', 'error');
            return false;
        }

        // If any error occurred, deleted all inserted parts and the inserted project
        if ($errors) {
            $this->codes_project_model->delete($new_project['id']);

            foreach ($inserted_parts as $part) {
                $this->part_model->delete($part['id']);
            }

            add_message('Could not duplicate the project. Insertion of a product failed', 'error');
            return false;

        }
        return true;
    }

    public function get_dropdown() {

        $this->load->helper('date');

        $this->db->join('codes_divisions', 'codes_projects.division_id = codes_divisions.id');
        $this->db->join('companies', 'codes_projects.company_id = companies.id');

        $this->db->from($this->table);
        $this->db->select('codes_projects.id, codes_projects.name, codes_divisions.code AS division_code, companies.code AS company_code, codes_projects.number, codes_projects.creation_date', false);
        $this->db->select("CONCAT(codes_divisions.code, DATE_FORMAT(FROM_UNIXTIME(codes_projects.creation_date), '%y'), '.', number, '.', companies.code) AS project_number", false);
        $this->db->order_by('codes_projects.creation_date DESC');
        $query = $this->db->get();

        $project_ids = array(0 => '-- Select One --');

        foreach ($query->result() as $row) {
            $project_ids[$row->id] = $row->project_number . ' - ' . $row->name;
        }
        return $project_ids;

    }


}
