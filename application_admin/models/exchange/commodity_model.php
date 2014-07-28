<?php
/**
 * Contains the Commodity Model class
 * @package models
 */

/**
 * Commodity Model class
 * @package models
 */
class Commodity_Model extends MY_Model {
    /**
     * @access public
     * @var string The DB table used by this model
     */
    var $table = 'exchange_commodities';

    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {


        $this->dbfields = array($this->dbfield->get_field('exchange_commodities.id', 'commodity_id', 'ID'),
                                $this->dbfield->get_field('exchange_commodities.name', 'commodity_name', 'Name'),
                                $this->dbfield->get_field('exchange_commodities.category', 'commodity_category', 'Category'));

        parent::apply_db_selects();
        $numrows = $this->filter($params, $filters);

        // For table headings
        $table_data = array('headings' => parent::get_table_headings(),
                            'rows' => array(),
                            'numrows' => $numrows);

        $query = $this->db->get($this->table);

        foreach ($query->result() as $commodity) {
            $exceptions = array('commodity_category' => get_lang_for_constant_value('EXCHANGE_COMMODITY_CATEGORY_', $commodity->commodity_category));

            $row = parent::get_table_row_from_db_record($commodity, $exceptions);
            $table_data['rows'][] = $row;
        }

        return $table_data;
    }


    function query_by_market($market_id, $commodity_id=null, $category=null) {

        $this->db->select('exchange_commodities.*')->from(array('exchange_markets', 'exchange_market_commodities'));
        $this->db->where('exchange_market_commodities.commodity_id = exchange_commodities.id AND exchange_market_commodities.market_id = exchange_markets.id');
        $this->db->where('exchange_market_commodities.market_id', $market_id);
        $this->db->distinct();

        if (!empty($commodity_id)) {
          $this->db->where('exchange_commodities.id', $commodity_id);
        }

        if (!empty($category)) {
          $this->db->where('exchange_commodities.category', $category);
        }
    }
}
