<?php
/**
 * Contains the Dailyvalue Model class
 * @package models
 */

/**
 * Dailyvalue Model class
 * @package models
 */
class Dailyvalue_Model extends MY_Model {
    /**
     * @access public
     * @var string The DB table used by this model
     */
    var $table = 'exchange_dailyvalues';

    public function get_by_category($category=EXCHANGE_COMMODITY_CATEGORY_METALS, $filter_markets='all', $filter_commodities='all') {

        $this->db->select('em.id AS market_id,
                         em.name AS market_name,
                         em.currency_id AS currency_id,
                         ec.id AS commodity_id,
                         ec.name AS commodity_name,
                         ed.id AS dailyvalue_id,
                         ed.value AS dailyvalue_value,
                         ed.timestamp AS dailyvalue_timestamp', false);
        $this->db->join('exchange_market_commodities emc', 'emc.market_id = em.id');
        $this->db->join('exchange_commodities ec', 'ec.id = emc.commodity_id');
        $this->db->join('exchange_dailyvalues ed', 'ec.id = ed.commodity_id AND ed.market_id = em.id');
        $this->db->where('ec.category', $category);
        $this->db->order_by('ed.timestamp');

        if ($filter_markets != 'all') {
            $this->db->where_in('em.id', $filter_markets);
        }

        if ($filter_commodities != 'all') {
            $this->db->where_in('ec.id', $filter_commodities);
        }

        $dailyvalues = array();

        $query = $this->db->get('exchange_markets em');

        if ($query->num_rows > 0) {
            foreach ($query->result() as $row) {
                if (empty($dailyvalues[$row->market_id])) {
                    $dailyvalues[$row->market_id] = array();
                }

                if (empty($dailyvalues[$row->market_id][$row->commodity_id])) {
                    $dailyvalues[$row->market_id][$row->commodity_id] = array();
                }
                $dailyvalues[$row->market_id][$row->commodity_id][$row->dailyvalue_id] = array(
                        'value' => $row->dailyvalue_value,
                        'timestamp' => $row->dailyvalue_timestamp,
                        'currency_id' => $row->currency_id,
                        'market' => $row->market_name,
                        'commodity' => $row->commodity_name);
            }
        }

        return $dailyvalues;
    }

}
