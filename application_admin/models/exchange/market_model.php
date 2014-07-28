<?php
/**
 * Contains the Market Model class
 * @package models
 */

/**
 * Market Model class
 * @package models
 */
class Market_Model extends MY_Model {
    /**
     * @access public
     * @var string The DB table used by this model
     */
    var $table = 'exchange_markets';

    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {


        $this->dbfields = array($this->dbfield->get_field('exchange_markets.id', 'market_id', 'ID'),
                                $this->dbfield->get_field('exchange_markets.name', 'market_name', 'Name'),
                                $this->dbfield->get_field('exchange_markets.currency_id', 'market_currency_id', 'Currency'));

        parent::apply_db_selects();
        $numrows = $this->filter($params, $filters);

        // For table headings
        $table_data = array('headings' => parent::get_table_headings() + array('commodities' => 'Commodities'),
                            'rows' => array(),
                            'numrows' => $numrows);

        $query = $this->db->get($this->table);

        foreach ($query->result() as $market) {
            $exceptions = array('market_currency_id' => get_lang_for_constant_value('CURRENCY_', $market->market_currency_id));

            $row = parent::get_table_row_from_db_record($market, $exceptions);

            // Get list of commodities for this market
            $this->db->select('ec.id, ec.name');
            $this->db->join('exchange_commodities ec', 'emc.commodity_id = ec.id');
            $this->db->where('emc.market_id', $market->market_id);

            $market_commodities = array();
            if ($market_query = $this->db->get('exchange_market_commodities emc')) {
                if ($market_query->num_rows > 0) {
                    foreach ($market_query->result() as $commodity) {
                        $market_commodities[$commodity->id] = $commodity->name;
                    }
                }
            }
            $row[] = $market_commodities;
            $table_data['rows'][] = $row;
        }

        return $table_data;
    }

    public function add() {
        return $this->edit();
    }

    /**
     * Returns an array of commodities (ID only, for multiselect element) linked to the given market
     * @param int $market_id
     * @return array
     */
    public function get_assigned_commodities($market_id) {

        $this->db->join('exchange_market_commodities emc', 'emc.commodity_id = ec.id');
        $this->db->where('emc.market_id', $market_id);
        $this->db->select('ec.id');
        $query = $this->db->get('exchange_commodities ec');

        $commodities = array();
        if ($query->num_rows > 0) {
            foreach ($query->result() as $row) {
                $commodities[] = $row->id;
            }
        }

        return $commodities;
    }

    /**
     * In addition to normal delete, delete all associations with commodities and dailyvalues
     * @param int $market_id
     * @return bool success|failure
     */
    public function delete($market_id) {

        if (parent::delete($market_id)) {
            $this->db->delete('exchange_market_commodities', array('market_id' => $market_id));
            $this->db->delete('exchange_dailyvalues', array('market_id' => $market_id));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns an array of markets who have at least one commodity of the requested category
     * @param int $category_id
     * @return array
     */
    public function get_by_category($category_id) {


        $this->db->join('exchange_market_commodities emc', 'emc.market_id = em.id');
        $this->db->join('exchange_commodities ec', 'ec.id = emc.commodity_id AND ec.category = ' . $category_id);
        $this->db->select('em.*');
        $this->db->distinct();
        $query = $this->db->get('exchange_markets em');
        $markets = array();
        foreach ($query->result() as $row) {
            $markets[] = $row;
        }
        return $markets;
    }
}
