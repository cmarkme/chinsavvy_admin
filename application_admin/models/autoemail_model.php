<?php
/**
 * Contains the Autoemail Model class
 * @package models
 */

/**
 * Autoemail Model class
 * @package models
 */

class Autoemail_Model extends MY_Model {
    public $table = 'autoemails';
    public function get_emails($autoemail_id) {
        $autoemail = $this->get($autoemail_id);

        if (preg_match_all('/\[([A-Z_]*)\]/', $autoemail->conditions, $matches)) {
            foreach ($matches[1] as $match) {
                $autoemail->conditions = str_replace("[$match]", constant($match), $autoemail->conditions);
            }
        }

        $this->db->from('enquiries');
        $this->db->select('users.id as user_id');
        $this->db->select('users.first_name');
        $this->db->select('users.surname');
        $this->db->select('enquiries.id as enquiry_id');
        $this->db->select('enquiries.creation_date as enquiry_date');
        $this->db->select('enquiries_enquiry_products.title as product_title');
        $this->db->select('user_contacts.contact AS email');
        $this->db->select('companies.name AS enquirer');
        $this->db->join('users', 'enquiries.user_id = users.id');
        $this->db->join('companies', 'companies.id = users.company_id');
        $this->db->join('enquiries_outbound_quotations', 'enquiries.id = enquiries_outbound_quotations.enquiry_id', 'LEFT OUTER');
        $this->db->join('enquiries_enquiry_products', 'enquiries_enquiry_products.id = enquiries.enquiry_product_id');
        $this->db->join('user_contacts', 'users.id = user_contacts.user_id AND user_contacts.type = ' . USERS_CONTACT_TYPE_EMAIL);
        $this->db->where($autoemail->conditions);
        $this->db->group_by('enquiry_date');
        return $this->get();
    }
    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {


        $this->load->helper('date');

        $this->dbfields = array($this->dbfield->get_field('id', 'id', 'ID'),
                                $this->dbfield->get_field('name', 'name', 'Name'),
                                $this->dbfield->get_field('description', 'description', 'Description'),
                                $this->dbfield->get_field('status', 'status', 'Status'),
                                $this->dbfield->get_field('creation_date', 'creation_date', 'Date')
                                );

        parent::apply_db_selects();

        $numrows = parent::filter($params, $filters);

        if ($limit) {
            $this->db->limit($limit);
        }

        // For table headings
        $table_data = array('headings' => parent::get_table_headings(),
                            'rows' => array(),
                            'numrows' => $numrows);

        $query = $this->db->get($this->table);

        foreach ($query->result() as $row) {
            $table_data['rows'][] = parent::get_table_row_from_db_record($row);
        }

        return $table_data;
    }
}
?>
