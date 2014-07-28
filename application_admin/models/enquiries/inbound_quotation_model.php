<?php
/**
 * Contains the Enquiry_Inbound_Quotation Model class
 * @TODO probably get rid of this thing, it's never been used by Chinasavvy at all!
 * @package models
 */

/**
 * Enquiry_Inbound_Quotation Model class
 * @package models
 */
class Enquiry_Inbound_Quotation_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'enquiries_inbound_quotations';

    /**
     * @var array $sortcolumns These are the columns used by jquery.datatables for sorting. They must be in the same order as the table headings, and refer to specific DB fields
     */
    var $sortcolumns = array('enquiries_inbound_quotations.id',
                             'enquiries_inbound_quotations.enquiry_id',
                             'enquiries_inbound_quotations.creation_date',
                             'users.surname',
                             'companies.name',
                             'enquiries_supplier_products.title');
    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {

        $this->load->helper('date');

        // Unique Linked fields
        $this->db->join('enquiries', 'enquiries.id = enquiries_outbound_quotations.enquiry_id');
        $this->db->join('users u', 'enquiries.user_id = u.id');
        $this->db->join('users s', 'enquiries_outbound_quotations.staff_id = s.id');
        $this->db->join('companies', 'u.company_id = companies.id');
        $this->db->join('company_addresses', 'company_addresses.company_id = companies.id');
        $this->db->join('enquiries_enquiry_products', 'enquiries.enquiry_product_id = enquiries_enquiry_products.id');
        $this->db->select('enquiries_outbound_quotations.id, enquiries_outbound_quotations.enquiry_id,
                        enquiries_outbound_quotations.creation_date,
                        companies.name AS company,
                        enquiries_enquiry_products.title AS product,
                        CONCAT(s.salutation,\' \',s.first_name,\' \',s.surname) AS staff', false);
        $this->db->group_by('enquiries_outbound_quotations.id');
        $numrows = $this->filter($params, $filters);

        if ($limit) {
            $this->db->limit($limit);
        }

        // For table headings
        $table_data = array('headings' => array ('id' => 'Ref',
                                                 'enquiry_id' => 'Enquiry ID',
                                                 'company' => 'Enquirer',
                                                 'product' => 'Product',
                                                 'creation_date' => 'Date',
                                                 'staff' => 'Staff'),
                            'rows' => array(),
                            'numrows' => $numrows);

        $query = $this->db->get($this->table);

        foreach ($query->result() as $row) {
            $table_data['rows'][] = array($row->id,
                                          $row->enquiry_id,
                                          $row->company,
                                          $row->product,
                                          unix_to_human($row->creation_date),
                                          $row->staff);
        }

        return $table_data;
    }
}
