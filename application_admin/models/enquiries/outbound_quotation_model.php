<?php
/**
 * Contains the Enquiry_Outbound_Quotation Model class
 * @package models
 */

/**
 * Enquiry_Outbound_Quotation Model class
 * @package models
 */
class Outbound_Quotation_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'enquiries_outbound_quotations';

    /**
     * Returns an array of all the values needed to fill an outbound quotation form for a given outbound quotation
     * @param int $outbound_id
     * @return array $values;
     */
    function get_values($outbound_id) {

        $this->load->model('company_model');
        $this->load->model('enquiries/enquiry_product_model');
        $this->load->model('enquiries/outbound_quotation_model');
        $this->load->model('enquiries/enquiry_model');

        $this->db->from('enquiries_outbound_quotations eoq')->where('eoq.id', $outbound_id);
        $this->db->join('enquiries e', 'e.id = eoq.enquiry_id');
        $this->db->join('users u', 'e.user_id = u.id');
        $this->db->join('companies c', 'u.company_id = c.id');
        $this->db->join('company_addresses ca', 'ca.company_id = c.id');
        $this->db->join('enquiries_enquiry_products eep', 'eep.id = IF(eoq.product_id > 0 AND eoq.product_id <> e.enquiry_product_id, eoq.product_id, e.enquiry_product_id)');

        $aliases = array(
            'u.id' => 'customer_id',
            'u.first_name' => 'customer_first_name',
            'u.surname' => 'customer_surname',
            'u.salutation' => 'customer_salutation',
            'c.name' => 'company_name',
            'ca.address1' => 'address_address1',
            'ca.address2' => 'address_address2',
            'ca.city' => 'address_city',
            'ca.state' => 'address_state',
            'ca.postcode' => 'address_postcode',
            'ca.country_id' => 'address_country_id',
            'eoq.id' => 'quotation_id',
            'eoq.enquiry_id' => 'quotation_enquiry_id',
            'eoq.creation_date' => 'quotation_creation_date',
            'eoq.product_lead_time' => 'quotation_product_lead_time',
            'eoq.tool_lead_time' => 'quotation_tool_lead_time',
            'eoq.tool_cost' => 'quotation_tool_cost',
            'eoq.notes' => 'quotation_notes',
            'eoq.price' => 'quotation_price',
            'eoq.unit' => 'quotation_unit',
            'eoq.currency' => 'quotation_currency',
            'eoq.min_qty' => 'quotation_min_qty',
            'eoq.freight' => 'quotation_freight',
            'eoq.delivery_terms' => 'quotation_delivery_terms',
            'eoq.delivery_point' => 'quotation_delivery_point',
            'eoq.country_id' => 'quotation_country_id',
            'eoq.payment_terms' => 'quotation_payment_terms',
            'eoq.tool_cost_payment_terms' => 'quotation_tool_cost_payment_terms',
            'eoq.sample_cost' => 'quotation_sample_cost',
            'eoq.sample_time' => 'quotation_sample_time',
            'eoq.sample_payment_terms' => 'quotation_sample_payment_terms',
            'eoq.staff_id' => 'quotation_staff_id',
            'eep.title' => 'enquiry_product_title',
            'eep.description' => 'enquiry_product_description',
            'eep.materials' => 'enquiry_product_materials',
            'eep.man_process' => 'enquiry_product_man_process',
            'eep.size' => 'enquiry_product_size',
            'eep.weight' => 'enquiry_product_weight',
            'eep.colour' => 'enquiry_product_colour',
            'eep.packaging' => 'enquiry_product_packaging');

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

    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {

        $this->load->helper('date');

        $this->dbfields = array($this->dbfield->get_field('enquiries_outbound_quotations.id', 'enquiries_outbound_quotations_id', 'Ref'),
                                $this->dbfield->get_field('enquiries_outbound_quotations.enquiry_id', 'enquiries_outbound_quotations_enquiry_id', 'Enquiry ID'),
                                $this->dbfield->get_field('companies.name', 'company', 'Enquirer'),
                                $this->dbfield->get_field('enquiries_enquiry_products.title', 'product', 'Product'),
                                $this->dbfield->get_field('enquiries_outbound_quotations.creation_date', 'enquiries_outbound_quotations_creation_date', 'Date'),
                                $this->dbfield->get_field('CONCAT(s.salutation,\' \',s.first_name,\' \',s.surname)', 'staff', 'Staff'),
                                );

        // Unique Linked fields
        $this->db->join('enquiries', 'enquiries.id = enquiries_outbound_quotations.enquiry_id');
        $this->db->join('users u', 'enquiries.user_id = u.id');
        $this->db->join('users s', 'enquiries_outbound_quotations.staff_id = s.id');
        $this->db->join('companies', 'u.company_id = companies.id');
        $this->db->join('company_addresses', 'company_addresses.company_id = companies.id');
        $this->db->join('enquiries_enquiry_products', 'enquiries_enquiry_products.id =
                IF(enquiries_outbound_quotations.product_id > 0 AND enquiries_outbound_quotations.product_id <> enquiries.enquiry_product_id, enquiries_outbound_quotations.product_id, enquiries.enquiry_product_id)
               ');

        // Add users.id for filtering
        parent::apply_db_selects();

        $this->db->select('enquiries_outbound_quotations.staff_id', 'staff_id');
        $this->db->group_by('enquiries_outbound_quotations_id');

        $numrows = $this->filter($params, $filters);

        if ($limit) {
            $this->db->limit($limit);
        }

        // For table headings
        $table_data = array('headings' => parent::get_table_headings(),
                            'rows' => array(),
                            'numrows' => $numrows);

        $query = parent::get_with_aliased_columns();

        foreach ($query->result() as $row) {
            $table_data['rows'][] = parent::get_table_row_from_db_record($row);
        }

        return $table_data;
    }

    /**
     * Returns an associative array of all staff associated with all outbound quotations. Used mostly for filtering list of quotations
     * @return array
     */
    public function get_staff_list() {

        $query = $this->db->select('id, CONCAT(salutation,\' \',first_name,\' \',surname) as staff', false)
                        ->from('users')
                        ->where('id IN (SELECT staff_id FROM enquiries_outbound_quotations)', null, false)
                        ->order_by('staff', 'ASC')
                        ->get();
        $staff_list = array();
        if ($query->num_rows > 0) {
            foreach ($query->result() as $row) {
                $staff_list[$row->id] = $row->staff;
            }
        }
        return $staff_list;
    }

    /**
     * Returns an array of all outbound quotations, with primary key as array key and key+date as value.
     * This is then used by Quickform as a select element.
     *
     * @return array Array of outbound quotations' ids
     */
    function get_dropdown() {

        $this->load->helper('date');
        $query = $this->db->from($this->table)->select('id, creation_date')->order_by('id DESC')->get();

        $outbound_ids = array('' => '-- Select an Outbound Quotation --');

        foreach ($query->result() as $row) {
            $outbound_ids[$row->id] = $row->id . ' (' . unix_to_human($row->creation_date) . ')';
        }

        return $outbound_ids;
    }
}
