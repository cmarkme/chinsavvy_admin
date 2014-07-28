<?php
/**
 * Contains the Part Model class
 * @package models
 */

/**
 * Part Model class
 * @package models
 */
class Part_Model extends MY_Model {
    /**
     * @var string The DB table used by this model
     */
    public $table = 'codes_parts';

    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {


        $this->load->helper('date');
        $this->dbfields = array($this->dbfield->get_field('codes_parts.id', 'codes_parts_id', 'ID'),
                                $this->dbfield->get_field('CONCAT(codes_divisions.code,DATE_FORMAT(FROM_UNIXTIME(codes_parts.creation_date), "%y"),".",codes_projects.number)', 'product_number', 'Product Number'),
                                $this->dbfield->get_field('codes_parts.number', 'part_number', 'PART'),
                                $this->dbfield->get_field('companies.code', 'company_code', 'CUST'),
                                $this->dbfield->get_field('codes_parts.name', 'codes_parts_name', 'Name'),
                                $this->dbfield->get_field('codes_parts.description', 'codes_parts_description', 'Description'),
                                $this->dbfield->get_field('codes_parts._2d_data', 'codes_parts__2d_data', '2D Data'),
                                $this->dbfield->get_field('codes_parts._2d_data_rev', 'codes_parts__2d_data_rev', 'Rev'),
                                $this->dbfield->get_field('codes_parts._3d_data', 'codes_parts__3d_data', '3D Data'),
                                $this->dbfield->get_field('codes_parts._3d_data_rev', 'codes_parts__3d_data_rev', 'Rev'),
                                $this->dbfield->get_field('codes_parts.other_data', 'codes_parts_other_data', 'Other Data'),
                                $this->dbfield->get_field('codes_parts.other_data_date', 'codes_parts_other_data_date', 'Other Data date'),
                                $this->dbfield->get_field('codes_parts.due_date', 'codes_parts_due_date', 'Due Date'),
                                $this->dbfield->get_field('codes_parts.creation_date', 'codes_parts_creation_date', 'Date'),
                                $this->dbfield->get_field('codes_parts.completed', 'codes_parts_completed', 'Completed'),

                                );

        if ($this->session->userdata('show_completed_parts')) {
            $this->dbfields[] = $this->dbfield->get_field('codes_parts.completed', 'codes_parts_completed', 'Completed');
        }

        // Unique Linked fields
        // $this->setup_for_sql();
        $this->db->join('codes_projects', 'codes_projects.id = codes_parts.project_id');
        $this->db->join('codes_divisions', 'codes_projects.division_id = codes_divisions.id');
        $this->db->join('companies', 'companies.id = codes_projects.company_id');

        parent::apply_db_selects();

        $this->db->group_by('codes_parts_id');

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
            if ($row->codes_parts_completed) {
                $completed = "Yes";
            }

            $table_data['rows'][] = parent::get_table_row_from_db_record($row, array('codes_parts_completed' => $completed));
        }

        return $table_data;
    }

    public function get_part_number($part_id) {

        $this->load->model('codes/codes_part_model');

        $part = $this->part_model->get($part_id);
        return $this->codes_part_model->get_number($part->part_id);
    }

    public function setup_for_sql() {

        $this->db->join('codes_projects', 'codes_projects.id = codes_parts.project_id');
        $this->db->join('codes_divisions', 'codes_projects.division_id = codes_divisions.id', 'LEFT OUTER');
        $this->db->join('companies', 'codes_projects.company_id = companies.id');
        $this->db->select('codes_parts.name', false)->select('codes_parts.description', false)->select('codes_parts.id', false);
        $this->db->select('CONCAT(codes_divisions.code,DATE_FORMAT(FROM_UNIXTIME(codes_parts.creation_date), "%y"),
                                 ".", codes_projects.number,
                                 ".", codes_parts.number,
                                 ".", companies.code) AS product_number', false);
    }

    public function get_number($part_id) {

        $this->part_model->setup_for_sql();

        $part = $this->part_model->get($part_id);

        if (!empty($part)) {
            return $part->product_number;
        }
    }

    /**
     * In addition to normal update, set overridden field according to received data.
     * @param string $source "part" or "project". Change the overridden field accordingly
     */
    function edit($part_id, $params=array(), $source="part") {


        if ($source == "part") {
            $status_date_needs_update = false;

            $current_part = $this->part_model->get($part_id);

            if ($current_part->due_date != $params['due_date'] && ($params['overridden'] & CODES_OVERRIDDEN_DUE_DATE) == 0) {
                $params['overridden'] += CODES_OVERRIDDEN_DUE_DATE;
            }
            if ($current_part->status_text != $params['status_text'] && ($params['overridden'] & CODES_OVERRIDDEN_STATUS_TEXT) == 0) {
                $params['overridden'] += CODES_OVERRIDDEN_STATUS_TEXT;
                $status_date_needs_update = true;
            }
            if ($current_part->status_description != $params['status_description'] && ($params['overridden'] & CODES_OVERRIDDEN_STATUS_DESCRIPTION) == 0) {
                $params['overridden'] += CODES_OVERRIDDEN_STATUS_DESCRIPTION;
                $status_date_needs_update = true;
            }

            if ($status_date_needs_update) {
                $params['status_date'] = mktime();
            }

            if ($current_part->status_date != $params['status_date'] && ($params['overridden'] & CODES_OVERRIDDEN_STATUS_DATE) == 0) {
                $params['overridden'] += CODES_OVERRIDDEN_STATUS_DATE;
            }
        }
        return parent::edit($part_id, $params);
    }

    /**
     * Looks up existing parts and generates a new part number based on what's there
     */
    public function generate_number($project_id=0) {
        // Find if there is another number between 01/01 of the current year and now. If not, return 100


        $this->db->select('number');
        // $this->db->where('creation_date BETWEEN ' . mktime(0,0,1,1,1, date('Y')) . ' AND ' . mktime());

        if (!($part = $this->get(array('project_id' => $project_id), true, 'creation_date DESC'))) {
            return 100;
        } else {
            return $part->number + 1;
        }
    }

    /**
     * Also delete project associations in QC system
     */
    public function delete($part_id) {

        $this->load->model('qc/project_model');
        $this->load->model('qc/projectrelated_model');

        if ($projects = $this->project_model->get(array('part_id' => $part_id))) {

            foreach ($projects as $project) {
                $this->db->where('project_id = '.$project->id.' OR related_id = '.$project->id);

                if ($relateds = $this->projectrelated_model->get()) {
                    foreach ($relateds as $related) {
                        $this->projectrelated_model->delete($related->id);
                    }
                }
            }
        }

        return parent::delete($part_id);
    }

    public function get_suggest_list($term) {

        $sql = "SELECT * FROM ((
                       SELECT codes_parts.name,
                           codes_parts.description,
                           codes_parts.id,
                           codes_parts.status,
                           CONCAT(codes_divisions.code,
                                  DATE_FORMAT(FROM_UNIXTIME(codes_parts.creation_date), '%y'), '.',
                                  codes_projects.number, '.',
                                  codes_parts.number, '.', companies.code)
                                AS product_number
                    FROM (`codes_parts`)
                    JOIN `codes_projects` ON `codes_projects`.`id` = `codes_parts`.`project_id`
                    LEFT OUTER JOIN `codes_divisions` ON `codes_projects`.`division_id` = `codes_divisions`.`id`
                    JOIN `companies` ON `codes_projects`.`company_id` = `companies`.`id`) codes_parts)

                    WHERE codes_parts.name LIKE '%".$this->db->escape_like_str($term)."%'
                    OR codes_parts.product_number LIKE '%".$this->db->escape_like_str($term)."%'
                    ORDER BY `product_number` DESC LIMIT 40";

        $query = $this->db->query($sql);

        $parts = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $parts[] = $row;
            }
        } else {
            return false;
        }

        return $parts;
    }
}
