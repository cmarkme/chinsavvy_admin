<?php
/**
 * Contains the Setting Model class
 * @package models
 */

/**
 * Setting Model class
 * @package models
 */

class Setting_Model extends MY_Model {
    public $table = 'settings';

    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {

        $this->dbfields = array($this->dbfield->get_field('settings.id', 'setting_id', 'ID'),
                                $this->dbfield->get_field('settings.name', 'setting_name', 'Name'),
                                $this->dbfield->get_field('settings.value', 'setting_value', 'Value')
                                );

        parent::apply_db_selects();
        $numrows = $this->filter($params, $filters);

        // For table headings
        $table_headings = parent::get_table_headings();
        $table_data = array('headings' => $table_headings,
                            'rows' => array(),
                            'numrows' => $numrows);

        $query = parent::get_with_aliased_columns();

        // Get list of users per setting
        $setting_ids = array();
        $settings = array();

        foreach ($query->result() as $row) {
            $setting_ids[] = $row->setting_id;
            $settings[$row->setting_id] = $row;
        }
        foreach ($settings as $setting) {
            $row = parent::get_table_row_from_db_record($setting);
            $table_data['rows'][] = $row;
        }
        return $table_data;

        if ($limit) {
            $this->db->limit($limit);
        }
    }
}
?>
