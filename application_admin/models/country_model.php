<?php
/**
 * Contains the Country Model class
 * @package models
 */

/**
 * Country Model class
 * @package models
 */
class Country_Model extends MY_Model {
    /**
     * @var string The DB table used by this model
     */
    public $table = 'countries';

    /**
     * @var array $common_countries A list of countries that will appear first in country dropdowns
     */
    public $common_countries = array('US', 'GB', 'AU');

    public function get_name($country_id) {

        if ($query = $this->db->select('country')->from('countries')->where('id', $country_id)->get()) {
            $result = $query->result();
            return $result[0]->country;
        } else {
            return null;
        }
    }

    public function get_dropdown($null_option = true, $optgroups=true) {

        $countries = array();

        if ($null_option) {
            $countries[null] = '-- Select One --';
        }

        $countries['Common countries'] = array();
        $countries['All countries'] = array();

        if ($optgroups && ($query = $this->db->select('id, country')->from('countries')->where_in('country_iso', $this->common_countries)->get())) {
            foreach ($query->result() as $row) {
                $countries['Common countries'][$row->id] = $row->country;
            }
        }

        $allcountries = array();
        if (!$optgroups && $null_option) {
            $allcountries[null] = '-- Select One --';
        }

        $nonindexed_countries = $this->get();
        foreach ($nonindexed_countries as $country) {
            $allcountries[$country->id] = $country->country;
        }

        if ($optgroups) {
            $countries['All countries'] = $allcountries;
            return $countries;
        } else {
            return $allcountries;
        }
    }
}

