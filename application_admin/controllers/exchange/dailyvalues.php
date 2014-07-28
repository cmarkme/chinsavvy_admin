<?php
/**
 * Contains the Dailyvalues Controller class
 * @package controllers
 */

/**
 * Dailyvalues Controller Class
 * @package controllers
 */
class Dailyvalues extends MY_Controller {

	function __construct() {
		parent::__construct();
        $this->load->model('exchange/commodity_model');
        $this->load->model('exchange/market_model');
        $this->load->model('exchange/dailyvalue_model');
        $this->load->model('exchange/currencyrate_model');
        $this->load->helper('dailyvalues');
        $this->config->set_item('replacer', array('metals' => 'Daily metal values', 'plastics' => 'Daily plastic values', 'add' => 'Add daily commodity values'));
        $this->config->set_item('exclude', array('browse', 'exchange'));
    }

    function index($category=EXCHANGE_COMMODITY_CATEGORY_METALS) {

        $this->load->library('flotgraph');

        $filter_markets = ($this->input->post('filter_markets')) ? $this->input->post('filter_markets') : 'all';
        $filter_commodities = ($this->input->post('filter_commodities')) ? $this->input->post('filter_commodities') : 'all';
        $graphs = ($this->input->post('graphs')) ? $this->input->post('graphs') : false;
        $market_names = array();

        // @TODO Filter logic and selects
        // Market Filters
        $selected_string = 'selected="selected"';
        $markets_selected = array('all' => null);
        $commodities_selected = array('all' => null);

        if ($filter_markets != 'all') {
            $markets_selected[$filter_markets] = $selected_string;
        } else {
            $markets_selected['all'] = $selected_string;
        }

        // Commodity Filters
        if ($filter_commodities != 'all' && $filter_markets != 'all') { // Specific Commodity, specific market
            $commodities_selected[$filter_commodities] = $selected_string;
            $this->commodity_model->query_by_market($filter_markets);
        } elseif ($filter_commodities != 'all') { // Specific Commodity, all markets
            $commodities_selected[$filter_commodities] = $selected_string;
        } elseif ($filter_markets != 'all') { // All commodities, specific market
            $this->commodity_model->query_by_market($filter_markets);
        } else { // All commodities, all markets
            $commodities_selected['all'] = $selected_string;
        }

        $commodities_array = $this->commodity_model->get(array('category' => $category));
        $markets_array = $this->market_model->get_by_category($category);

        // @TODO add a date range filter

        // get array of all dailyvalues for the present category
        $dailyvalues_array = $this->dailyvalue_model->get_by_category($category, $filter_markets, $filter_commodities);
        $currency_ids = array();

        // For conversion purposes, Obtain list of currency exchange rates
        $timestamps = array('oldest' => 0, 'newest' => time());
        if (!empty($dailyvalues_array)) {
            foreach ($dailyvalues_array as $market_id => $commodities) {
                foreach ($commodities as $commodity_id => $dailyvalues) {
                    foreach ($dailyvalues as $dailyvalue_id => $dailyvalue) {
                        if (empty($market_names[$market_id])) {
                            $market_names[$market_id] = $dailyvalue['market'];
                        }

                        if (empty($timestamps['oldest']) || $dailyvalue['timestamp'] < $timestamps['oldest']) {
                            $timestamps['oldest'] = $dailyvalue['timestamp'];
                        }

                        if ($dailyvalue['timestamp'] > $timestamps['newest']) {
                            $timestamps['newest'] = $dailyvalue['timestamp'];
                        }

                        if (!in_array($dailyvalue['currency_id'], $currency_ids)) {
                            $currency_ids[] = $dailyvalue['currency_id'];
                        }
                    }
                }
            }

            // Get all currency rates for the period covered by the above dailyvalues
            $this->db->where("creation_date BETWEEN {$timestamps['oldest']} AND {$timestamps['newest']}", null, false);
            $this->db->where_in('currency_id', $currency_ids);
            $this->db->order_by('creation_date DESC');
            $exchangerates = $this->currencyrate_model->get();

            // Add conversion rate info to each dailyvalue record
            foreach ($dailyvalues_array as $market_id => $commodities) {
                foreach ($commodities as $commodity_id => $dailyvalues) {
                    foreach ($dailyvalues as $dailyvalue_id => $dailyvalue) {
                        $dailyvalues_array[$market_id][$commodity_id][$dailyvalue_id]['conversion_rate'] =
                                $this->currencyrate_model->find_matching_rate($dailyvalue['timestamp'], $dailyvalue['currency_id'], $exchangerates)->value;
                    }
                }
            }

            // Prepare graph data
            $graph_data = array();
            foreach ($dailyvalues_array as $market_id => $commodities) {
                if (empty($graph_data[$market_id])) {
                    $graph_data[$market_id] = array();
                }
                foreach ($commodities as $commodity_id => $dailyvalues) {
                    $first_value = reset($dailyvalues);
                    $commodity_name = $first_value['commodity'];

                    $values = new stdClass();
                    $values->label = $commodity_name;
                    $values->data = array();

                    foreach ($dailyvalues as $dailyvalue) {
                        $values->data[] = array($dailyvalue['timestamp'] * 1000, $dailyvalue['value'] / $dailyvalue['conversion_rate']);
                    }
                    $graph_data[$market_id][] = $values;
                }
            }
        } else { // No daily values for these filters!
            add_message('Your filtered search returned no commodity values, please broaden your search.', 'warning');
            $graph_data = array();
        }

        $title = get_lang_for_constant_value('EXCHANGE_COMMODITY_CATEGORY_', $category) . ' values';

        $title_options = array('title' => $title,
                               'help' => $title,
                               'expand' => $category . '_values',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'exchange/dailyvalues/index',
                             'jstoloadinfooter' => array('numberformat', 'jquery/flot/jquery.flot'),
                             'jstoloadforie' => array('jquery/flot/excanvas'),
                             'category' => $category,
                             'dailyvalues_array' => $dailyvalues_array,
                             'graph_data' => $graph_data,
                             'category_name' => get_lang_for_constant_value('EXCHANGE_COMMODITY_CATEGORY_', $category),
                             'market_names' => $market_names,
                             'commodities_array' => $commodities_array,
                             'markets_array' => $markets_array,
                             'markets_selected' => $markets_selected,
                             'commodities_selected' => $commodities_selected
                             );

        $this->load->view('template/default', $pageDetails);
    }

    function delete($dailyvalue_id, $category=EXCHANGE_COMMODITY_CATEGORY_METALS) {

        $this->dailyvalue_model->delete($dailyvalue_id);
        add_message("The commodity value has been deleted!", 'success');
        redirect('exchange/dailyvalues/'.get_lang_for_constant_value('EXCHANGE_COMMODITY_CATEGORY_', $category));
    }

    function add($market_id=null) {

        $this->load->helper('form_template');

        $title = 'Commodity Exchange values Form';

        $day = time();
        $market_id = ($market_id) ? $market_id : 1;
        $defaults = array('timestamp' => $day, 'market_id' => $market_id);
        $markets = $this->market_model->get_dropdown('name', false, function($market) {
            return $market->name . ' (' . get_lang_for_constant_value('CURRENCY_', $market->currency_id) . ')';
        });

        // Get commodities for that market
        $this->commodity_model->query_by_market($market_id);
        $commodities = $this->commodity_model->get();

        form_element::$default_data = array('market_id' => $market_id);

        $title_options = array('title' => $title,
                               'help' => 'Use this form to enter daily commodity values for various stock exchange markets.',
                               'expand' => 'entry_form',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'section_title' => get_title($title_options),
                             'content_view' => 'exchange/dailyvalues/add',
                             'jstoloadinfooter' => array('numberformat', 'application/exchange/dailyvalues_add'),
                             'markets' => $markets,
                             'commodities' => $commodities
                             );
        $this->load->view('template/default', $pageDetails);
    }

    function process_add() {

        $this->load->library('form_validation');
        $this->load->helper('date');

        $market_id = $this->input->post('market_id');

        foreach ($_POST as $key => $val) {
            $this->form_validation->set_rules($key);
            $this->form_validation->override_field_data($key, $val);
            if (preg_match('/commodity_([0-9]*)/', $key, $matches)) {
                $this->form_validation->set_rules($key, 'Commodity value', 'trim|numeric');
            }
        }

        $this->form_validation->set_rules('market_id', 'Market', 'required');
        $this->form_validation->set_rules('timestamp', 'Date', 'required');

        $redirect_url = 'exchange/dailyvalues/add/'.$market_id;

        if ($this->form_validation->run()) {
            $timestamp = human_to_unix($this->input->post('timestamp'));

            foreach ($_POST as $key => $val) {
                if (!empty($val) && preg_match('/commodity_([0-9]*)/', $key, $matches)) {
                    $commodity_id = $matches[1];
                    $commodity = $this->commodity_model->get($commodity_id);

                    // Check if a daily value for this timestamp and commodity already exists
                    $dailyvalue_params = array('commodity_id' => $commodity_id, 'timestamp' => $timestamp, 'market_id' => $market_id);
                    $duplicate_entry = $dailyvalue = $this->dailyvalue_model->get($dailyvalue_params, true);

                    if ($duplicate_entry && !$this->input->post('override')) {
                        add_message("A value has already been entered for this commodity ($commodity->name), market and day."
                        . "The original value ($duplicate_entry->value) has been preserved. <br />Tick the 'Override existing values' checkbox and re-submit this "
                        . "form to replace the existing value.", 'warning');
                    } else if ($duplicate_entry) {
                        $this->dailyvalue_model->delete($duplicate_entry->id);
                        $dailyvalue_params['value'] = $val;
                        $this->dailyvalue_model->add($dailyvalue_params);
                    } else {
                        $dailyvalue_params['value'] = $val;
                        $this->dailyvalue_model->add($dailyvalue_params);
                    }
                }
            }
            add_message("Commodity values have been recorded for ".date('d F Y', $timestamp), 'success');
        } else {
            return $this->add($market_id);
        }
        redirect($redirect_url);
    }
}
