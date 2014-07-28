<?php
/**
 * Contains the Process Model class
 * @package models
 */

/**
 * Process Model class
 * @package models
 */
class Process_Model extends MY_Model {
    /**
     * @var string The DB table used by this model
     */
    public $table = 'codes_processes';

    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {


        $this->load->helper('date');

        $this->dbfields = array($this->dbfield->get_field('id', 'id', 'ID'),
                                $this->dbfield->get_field('code', 'code', 'Code'),
                                $this->dbfield->get_field('description', 'description', 'Description'),
                                $this->dbfield->get_field('creation_date', 'creation_date', 'Date')
                                );

        parent::apply_db_selects();

        $numrows = parent::filter($params, $filters);

        if ($limit) {
            $this->db->limit($limit);
        }

        // For table headings
        $table_data = array('headings' => parent::get_table_headings(),
                            'rows' => array(),
                            'numrows' => $numrows);

        $query = $this->db->get($this->table);

        foreach ($query->result() as $row) {
            $table_data['rows'][] = parent::get_table_row_from_db_record($row);
        }

        return $table_data;
    }
}
