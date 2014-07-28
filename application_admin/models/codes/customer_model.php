<?php
/**
 * Contains the Customer Model class
 * @package models
 */

/**
 * Customer Model class
 * A "Customer" is actually an association between a Company and 2 users,
 * namely the technical contact and the corporate contact.
 *
 * These three entities may already exist as part of the Enquiry system, so great care must be taken to
 * avoid duplication and overwriting of existing data.
 *
 * Because this model doesn't map to a single database table, all basic CRUD functions are overloaded here.
 *
 * @package models
 */
class Customer_Model extends MY_Model {
    /**
     * @var string The DB table used by this model
     */
    public $table = 'companies';

    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {


        $this->dbfields = array($this->dbfield->get_field('companies.id', 'company_id', 'ID'),
                                $this->dbfield->get_field('companies.name', 'company_name', 'Name'),
                                $this->dbfield->get_field('countries.country', 'country', 'Country'),
                                $this->dbfield->get_field('companies.code', 'code', 'Code'),
                                $this->dbfield->get_field('companies.role', 'role', 'Role'),
                                $this->dbfield->get_field('companies.company_type', 'company_type', 'Role'),
                                );

        $this->db->join('company_addresses', 'company_addresses.company_id = companies.id');
        $this->db->join('countries', 'company_addresses.country_id = countries.id');
        $this->db->group_by('company_id');

        $absolute_params['code IS NOT '] = 'NULL' ;
        $absolute_params['role IN '] = '("'.COMPANY_ROLE_ENQUIRER.'","'.COMPANY_ROLE_CUSTOMER.'")';

        parent::apply_db_selects();

        $numrows = $this->filter($params, $filters, true, $absolute_params);

        // For table headings
        $headings = parent::get_table_headings();
        unset($headings['role']);
        unset($headings['company_type']);
        $table_data = array('headings' => $headings,
                            'rows' => array(),
                            'numrows' => $numrows);

        $this->db->where($absolute_params, null, false);
        $query = parent::get_with_aliased_columns();

        foreach ($query->result() as $row) {
            $row = parent::get_table_row_from_db_record($row);
            unset($row[4]);
            unset($row[5]);
            $table_data['rows'][] = $row;
        }

        return $table_data;
    }

    /**
     * Returns an array of all the values needed to fill a customer form for a given customer
     * @param int $company_id
     * @return array $values;
     */
    function get_values($company_id) {

        $this->load->model('company_address_model');
        $this->load->model('country_model');
        $this->load->model('users/user_contact_model');

        $this->db->from('companies c')->where('c.id', $company_id);
        $this->db->join('users u', 'u.company_id = c.id', 'LEFT OUTER');
        $this->db->join('company_addresses ca_billing', 'ca_billing.company_id = c.id AND ca_billing.type = ' . COMPANY_ADDRESS_TYPE_BILLING, 'LEFT OUTER');
        $this->db->join('company_addresses ca_shipping', 'ca_shipping.company_id = c.id AND ca_shipping.type = ' . COMPANY_ADDRESS_TYPE_SHIPPING, 'LEFT OUTER');
        $this->db->distinct();

        $aliases = array(
                'c.id' => 'company_id',
                'c.name' => 'company_name',
                'c.name_ch' => 'company_name_ch',
                'c.role' => 'company_role',
                'c.company_type' => 'company_type',
                'c.code' => 'company_code',
                'c.url' => 'company_url',
                'c.phone' => 'company_phone',
                'c.fax' => 'company_fax',
                'c.email' => 'company_email',
                'c.email2' => 'company_email2',
                'c.notes' => 'company_notes',
                'c.revision_date' => 'company_revision_date',
                'ca_billing.id' => 'billing_address_id',
                'ca_billing.country_id' => 'billing_address_country_id',
                'ca_billing.city' => 'billing_address_city',
                'ca_billing.state' => 'billing_address_state',
                'ca_billing.postcode' => 'billing_address_postcode',
                'ca_billing.province' => 'billing_address_province',
                'ca_billing.address1' => 'billing_address_address1',
                'ca_billing.address2' => 'billing_address_address2',
                'ca_billing.default_address' => 'billing_address_default_address',
                'ca_shipping.id' => 'shipping_address_id',
                'ca_shipping.country_id' => 'shipping_address_country_id',
                'ca_shipping.city' => 'shipping_address_city',
                'ca_shipping.state' => 'shipping_address_state',
                'ca_shipping.postcode' => 'shipping_address_postcode',
                'ca_shipping.province' => 'shipping_address_province',
                'ca_shipping.address1' => 'shipping_address_address1',
                'ca_shipping.address2' => 'shipping_address_address2',
                'ca_shipping.default_address' => 'shipping_address_default_address'
                );

        foreach ($aliases as $field => $alias) {
            $this->db->select("$field AS $alias");
        }

        if ($query = $this->db->get()) {
            $result = $query->result();
            return (array) $result[0];
        } else {
            return false;
        }
    }
}
