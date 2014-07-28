<?php
/**
 * Contains the Company Model class
 * @package models
 */

/**
 * Company Model class
 * @package models
 */

class Supplier_Model extends MY_Model {
    /**
     * @var string The DB table used by this model
     */
    public $table = 'companies';

    /**
     * Returns an array of companies for populating a select element.
     *
     * @param string $role Can also be an array of values
     * @return array
     */
    public function get_dropdown_data($role=COMPANY_ROLE_SUPPLIER) {
        $company_ids = array(null => '-- Select One --');

        $this->db->select('id, name, code')->order_by('code, name ASC');

        if (is_array($role)) {
            $this->db->where_in('role', $role);
        } else {
            $this->db->where('role', $role);
        }

        if (is_null($companies = $this->get())) {
            return false;
        }

        if (!is_array($companies)) {
            $companies = array($companies);
        }

        if (!empty($companies)) {
            foreach ($companies as $company) {
                $name = $company->name;

                if (!empty($company->code)) {
                    $name = "$company->code - $company->name";
                }

                $company_ids[$company->id] = $name;
            }
        }

        return $company_ids;
    }

    /**
     * Returns this company's address of the requested type.
     * @param int $company_id
     * @param int $type
     * @return company_address
     */
    function get_address($company_id, $type=COMPANY_ADDRESS_TYPE_BILLING) {

        $this->load->model('company_address_model');
        return $this->company_address_model->get(array('type' => $type, 'company_id' => $company_id), true);
    }

    /**
     * Returns true if the address(es) in the $data array (typically from $_POST) match the address(es) of the given company
     * @param int $company_id
     * @param array $data
     * @return bool
     */
    function is_same_address($company_id, $data) {
        // Start with normal address
        $is_same_address = true;
        $my_address = $this->get_address($company_id);
        if ($my_address->country_id != $data['address_country_id'] || $my_address->postcode != $data['address_postcode']) {
            $is_same_address = false;
        }

        // Now do the chinese address
        if (!empty($data['address_ch_address1_ch'])) {
            $my_address = $this->get_address($company_id, COMPANY_ADDRESS_TYPE_CH);
            $is_same_address = $is_same_address && ($my_address->country_id == $data['address_country_id'] && $my_address->postcode == $data['address_postcode']);
        }

        return $is_same_address;
    }

    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {


        $this->dbfields = array($this->dbfield->get_field('companies.id', 'company_id', 'ID'),
                                $this->dbfield->get_field('companies.code', 'code', 'Code'),
                                $this->dbfield->get_field('companies.name', 'company_name', 'Name'),
                                $this->dbfield->get_field('companies.role', 'role', 'Role'),
                                $this->dbfield->get_field('companies.company_type', 'company_type', 'Role'),
                                );

        $absolute_params['role'] = COMPANY_ROLE_SUPPLIER;
        $absolute_params['company_type'] = COMPANY_TYPE_MANUFACTURER;

        parent::apply_db_selects();

        $numrows = $this->filter($params, $filters, true, $absolute_params);

        // For table headings
        $headings = parent::get_table_headings();
        unset($headings['role']);
        unset($headings['company_type']);
        $table_data = array('headings' => $headings,
                            'rows' => array(),
                            'numrows' => $numrows);

        $this->db->where($absolute_params);
        $query = parent::get_with_aliased_columns();

        foreach ($query->result() as $row) {
            $row = parent::get_table_row_from_db_record($row);
            unset($row[3]);
            unset($row[4]);
            $table_data['rows'][] = $row;
        }

        return $table_data;
    }

    /**
     * Returns an array of all the values needed to fill a company form for a given company
     * @param int $company_id
     * @return array $values;
     */
    function get_values($company_id) {

        $this->load->model('company_address_model');
        $this->load->model('country_model');

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
                'ca_billing.id' => 'address_billing_id',
                'ca_billing.country_id' => 'address_billing_country_id',
                'ca_billing.city' => 'address_billing_city',
                'ca_billing.state' => 'address_billing_state',
                'ca_billing.postcode' => 'address_billing_postcode',
                'ca_billing.province' => 'address_billing_province',
                'ca_billing.address1' => 'address_billing_address1',
                'ca_billing.address2' => 'address_billing_address2',
                'ca_billing.default_address' => 'address_billing_default_address',
                'ca_shipping.id' => 'address_shipping_id',
                'ca_shipping.country_id' => 'address_shipping_country_id',
                'ca_shipping.city' => 'address_shipping_city',
                'ca_shipping.state' => 'address_shipping_state',
                'ca_shipping.postcode' => 'address_shipping_postcode',
                'ca_shipping.province' => 'address_shipping_province',
                'ca_shipping.address1' => 'address_shipping_address1',
                'ca_shipping.address2' => 'address_shipping_address2',
                'ca_shipping.default_address' => 'address_shipping_default_address'
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

    /**
     * In addition to the normal delete operation, this removes the company_id from the users and codes_projects tables, and deletes entries in the company_addresses table
     * @param int $company_id
     */
    public function delete($company_id) {

        if ($result = parent::delete($company_id)) {
            $this->db->where('company_id', $company_id)->update('users', array('company_id' => null));
            $this->db->where('company_id', $company_id)->update('codes_projects', array('company_id' => null));
            $this->db->delete('company_addresses', array('company_id' => $company_id));
        }
        return $result;
    }
}

