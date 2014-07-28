<?php
/**
 * Contains the Enquiry Model class
 * @package models
 */

/**
 * Enquiry Model class
 * @package models
 */
class Enquiry_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'enquiries';

    /**
     * Returns an array of enquiry_notes for a given enquiry, ordered by ascending date of creation.
     * @access private
     * @param int $enquiry_id
     * @return array
     */
    function _get_notes_array($enquiry_id) {

        $notes = array();
        $query = $this->db->from('enquiries_enquiry_notes')->where('enquiry_id', $enquiry_id)->order_by('creation_date', 'asc')->get();
        foreach ($query->result() as $row) {
            $notes[] = $row;
        }
        return $notes;
    }

    /**
     * Returns a string of enquiry notes, formatted for display on screen
     * @param int $enquiry_id
     * @param array $enquiry_notes If given, ignores $enquiry_id and formats this array of notes
     * @param bool $text_only If true, will not use any HTML tags, just plain text
     * @return string
     */
    public function get_formatted_notes($enquiry_id=null, $enquiry_notes=array(), $text_only=false) {


        if (empty($enquiry_id)) {
            $notes_array = $enquiry_notes;
        } else {
            $notes_array = $this->_get_notes_array($enquiry_id);
        }

        $formatted_notes = '';

        $this->load->helper('date');
        $this->load->model('enquiries/enquiry_note_model');

        foreach ($notes_array as $note) {
            $usercolor = "#336611";
            $systemcolor = "#335599";
            $anonymouscolor = "#995533";

            if (empty($note->user)) {
                $author = $this->enquiry_note_model->get_author($note->id);
            } else {
                $author = $this->enquiry_note_model->get_author(null, $note);
            }

            if ($author == 'Anonymous') {
                $authorcolor = $anonymouscolor;
            } elseif ($author == 'System') {
                $authorcolor = $systemcolor;
            } else {
                $authorcolor = $usercolor;
            }

            if ($text_only) {
                $formatted_notes .= "$author on " . unix_to_human($note->revision_date, '%d/%m/%Y %H:%i:%s') . ":
" . $note->message . " ($note->enquiry_id)
--------------------------------------------------------

";
            } else {
                $formatted_notes .= "<span style=\"color: $authorcolor; font-size: 110%\">$author</span> on " .
                    unix_to_human($note->revision_date, '%d/%m/%Y %H:%i:%s') . ": <br />" .
                    $note->message . " ($note->enquiry_id)<br />
                    ----------------------<br />";
            }
        }

        return $formatted_notes;
    }

    /**
     * Returns a numerically indexed array of notes for each row of the currently selected set of records.
     * If no notes for a record, no entry.
     */
    function get_tooltipstrings($per_page=null, $page=null) {
        // If per_page and page are given, we must first fetch the correct object
        $strings = array();


        $this->db->from('enquiries');

        if (!empty($page)) {
            $this->db->limit($per_page * ($page - 1), $per_page);
        }

        $query = $this->db->get();

        foreach ($query->result() as $row) {
            $strings[] = stripslashes(nl2br($this->get_formatted_notes($row->id)));
        }
        return $strings;
    }

    /**
     * Returns an array of all enquiries, with primary key as array key and key+date as value.
     * This is then used by the form template as a select element.
     *
     * @return array Array of enquiries' ids
     */
    function get_dropdown() {
        $role_for_detail = 'do:anything';

        $this->load->helper('date');

        if (has_capability($role_for_detail))
        {
            $query = $this->db->select('enquiries.id, enquiries.creation_date, companies.name, countries.country')
                ->from($this->table)
                ->join('users', 'enquiries.user_id = users.id')
                ->join('companies', 'users.company_id = companies.id')
                ->join('company_addresses', 'company_addresses.company_id = companies.id')
                ->join('countries', 'company_addresses.country_id = countries.id')
                ->order_by('enquiries.id DESC')
                ->get();
        }
        else
        {
            $query = $this->db->from($this->table)->select('id, creation_date')->order_by('id DESC')->get();
        }

        $enquiry_ids = array('' => '-- Select an Enquiry --');

        foreach ($query->result() as $row)
        {
            $enquiry_ids[$row->id] = $row->id . ' | ' . unix_to_human($row->creation_date);

            if (has_capability($role_for_detail)) //TODO define role more specifically
            {
                $enquiry_ids[$row->id] .= ' | ' . $row->name . ' (' . $row->country . ')';
            }
        }

        return $enquiry_ids;
    }

    /**
     * Returns an array of all the values needed to fill an enquiry form for a given enquiry
     * @param int $enquiry_id
     * @return array $values;
     */
    function get_values($enquiry_id) {

        $this->load->model('enquiries/enquiry_product_model');
        $this->load->model('company_model');
        $this->load->model('company_address_model');
        $this->load->model('users/user_model');
        $this->load->model('users/user_contact_model');
        $this->load->model('users/user_option_model');

        $enquiry = $this->enquiry_model->get($enquiry_id);
        $enquiry_product = $this->enquiry_product_model->get($enquiry->enquiry_product_id);
        $user = $this->user_model->get($enquiry->user_id);

        $phones = $this->user_contact_model->get_by_user_id($user->id, USERS_CONTACT_TYPE_PHONE, false);
        $contacts['user_phone'] = (empty($phones[0])) ? null :$phones[0]->contact;
        $contacts['user_phone2'] = (empty($phones[1])) ? null : $phones[1]->contact;

        $emails = $this->user_contact_model->get_by_user_id($user->id, USERS_CONTACT_TYPE_EMAIL, false);

        $contacts['user_email'] = (empty($emails[0])) ? null : $emails[0]->contact;
        $contacts['user_email2'] = (empty($emails[1])) ? null : $emails[1]->contact;

        $fax = $this->user_contact_model->get_by_user_id($user->id, USERS_CONTACT_TYPE_FAX, true);
        $contacts['user_fax'] = null;
        if ($fax) {
            $contacts['user_fax'] = $fax->contact;
        }

        $mobile = $this->user_contact_model->get_by_user_id($user->id, USERS_CONTACT_TYPE_MOBILE, true);
        $contacts['user_mobile'] = null;
        if ($mobile) {
            $contacts['user_mobile'] = $mobile->contact;
        }

        // Get user other options
        $first_name_ch = $this->user_option_model->get(array('user_id' => $user->id, 'name' => 'first_name_ch'), true);
        $surname_ch = $this->user_option_model->get(array('user_id' => $user->id, 'name' => 'surname_ch'), true);
        $options = array('user_first_name_ch' => null, 'user_surname_ch' => null);
        if ($first_name_ch) {
            $options['user_first_name_ch'] = $first_name_ch->value;
        }
        if ($surname_ch) {
            $options['user_surname_ch'] = $surname_ch->value;
        }

        // Get company info
        if (!empty($user->company_id)) {
            if ($company = $this->company_model->get($user->company_id)) {
                $address = $this->company_address_model->get(array('company_id' => $company->id, 'default_address' => 1), true);
                $address_ch = $this->company_address_model->get(array('company_id' => $company->id, 'type' => COMPANY_ADDRESS_TYPE_CH), true);
            } else {
                $company = null;
                $address = null;
                $address_ch = null;
            }
        } else {
            $company = null;
            $address = null;
            $address_ch = null;
        }

        $arrays = array('company', 'enquiry', 'user', 'address', 'address_ch', 'enquiry_product');
        foreach ($arrays as $type) {
            if (!empty(${$type.'_array'})) {
                continue;
            }
            ${$type.'_array'} = array();
            if (is_object(${$type})) {
                foreach (${$type} as $k => $v) {
                    ${$type.'_array'}[$type.'_'.$k] = $v;
                }
            }
        }

        if (empty($company_array)) {
            $company_array = array('company_name' => '', 'company_type' => '', 'company_url' => '');
            add_message('No company info for this enquiry!', 'warning');
        }
        if (empty($address_array)) {
            $address_array = array('address_address1' => '', 'address_address2' => '', 'address_city' => '', 'address_state' => '', 'address_postcode' => '');
            add_message('No address info for this company!', 'warning');
        }

        return array_merge($enquiry_array,
                           $user_array,
                           $contacts,
                           $options,
                           $company_array,
                           $address_array,
                           $address_ch_array,
                           $enquiry_product_array);
    }

    /**
     * Returns a paginated, ordered and filtered array of records
     * @param array $params Parameters used by parent class to apply pagination limits
     * @param array $filters Array of Filter objects used by parent class to apply SQL WHERE clauses
     * @param int $limit Hard-coded limit
     * @param bool $notes Whether or not to return notes as well
     * @param int $assigned_user_id If given, will filter the enquiries to show only those assigned to the user_id
     * @param text $outputtype Used here in case CSV is requested, in which case we add more columns
     * @return array
     */
    function get_data_for_listing($params=array(), $filters=array(), $limit=null, $notes=true, $assigned_user_id=null, $outputtype='html') {


        $this->load->helper('date');

        $this->dbfields = array($this->dbfield->get_field('enquiries.id', 'enquiries_id', 'Ref'),
                                $this->dbfield->get_field('enquiries.status', 'enquiries_status', 'Status'),
                                $this->dbfield->get_field('enquiries.priority', 'enquiries_priority', 'Priority'),
                                $this->dbfield->get_field('countries.country', 'country', 'Country'),
                                $this->dbfield->get_field('enquiries.creation_date', 'enquiries_creation_date', 'Date created'),
                                $this->dbfield->get_field('enquiries.due_date', 'enquiries_due_date', 'Date due'),
                                $this->dbfield->get_field('companies.name', 'company', 'Enquirer'),
                                $this->dbfield->get_field('enquiries_enquiry_products.title', 'product', 'Product')
                                );

        // Unique Linked fields
        $this->db->join('users', 'enquiries.user_id = users.id');
        $this->db->join('companies', 'users.company_id = companies.id');
        $this->db->join('company_addresses', 'company_addresses.company_id = companies.id');
        $this->db->join('countries', 'company_addresses.country_id = countries.id');
        $this->db->join('enquiries_enquiry_products', 'enquiries.enquiry_product_id = enquiries_enquiry_products.id');

        if ($outputtype == 'csv') {
            $this->db->join('user_contacts as phone', 'phone.user_id = users.id AND phone.type = ' . USERS_CONTACT_TYPE_PHONE, 'LEFT OUTER');
            $this->db->join('user_contacts as mobile', 'mobile.user_id = users.id AND mobile.type = ' . USERS_CONTACT_TYPE_MOBILE, 'LEFT OUTER');
            $this->db->join('user_contacts as email', 'email.user_id = users.id AND email.type = ' . USERS_CONTACT_TYPE_EMAIL, 'LEFT OUTER');
            $this->dbfields[] = $this->dbfield->get_field('users.first_name', 'first_name', 'Contact first name');
            $this->dbfields[] = $this->dbfield->get_field('users.surname', 'surname', 'Contact last name');
            $this->dbfields[] = $this->dbfield->get_field('phone.contact', 'phone', 'Contact phone');
            $this->dbfields[] = $this->dbfield->get_field('mobile.contact', 'mobile', 'Contact mobile');
            $this->dbfields[] = $this->dbfield->get_field('email.contact', 'email', 'Contact email');
            $this->dbfields[] = $this->dbfield->get_field('enquiries_enquiry_products.title', 'product_title', 'Product title');
            $this->dbfields[] = $this->dbfield->get_field('enquiries_enquiry_products.description', 'product_description', 'Product description');
        }

        $absolute_params = null;
        // Assigned_user_id can be requested directly through the URL
        if (empty($assigned_user_id)) {
            $assigned_user_id = $this->input->post('staff_id');
        }

        if (!empty($assigned_user_id)) {
            $absolute_params['enquiries_enquiry_staff.user_id'] = $assigned_user_id;
            $this->db->join('enquiries_enquiry_staff', 'enquiries_enquiry_staff.enquiry_id = enquiries.id', 'LEFT OUTER');
            $this->dbfields[] = $this->dbfield->get_field('enquiries_enquiry_staff.user_id', 'staff_id', "Staff Id");
        }

        parent::apply_db_selects();

        $this->db->group_by('enquiries_id');

        $numrows = parent::filter($params, $filters, true, $absolute_params);

        if ($limit) {
            $this->db->limit($limit);
        }

        // For table headings
        $table_headings = parent::get_table_headings();
        if (!is_null($assigned_user_id)) {
            unset($table_headings['staff_id']);
        }
        $table_headings['stafflist'] = 'Staff list';
        $table_headings['notes'] = 'Notes';

        $table_data = array('headings' => $table_headings,
                            'rows' => array(),
                            'numrows' => $numrows);

        $enquiry_ids = array();
        $enquiries = array();
        $enquiry_staff = array();
        $formatted_notes = array();
        $query = parent::get_with_aliased_columns($absolute_params);

        foreach ($query->result() as $row) {
            $enquiry_ids[] = $row->enquiries_id;
            $enquiries[$row->enquiries_id] = $row;
            $enquiry_staff[$row->enquiries_id] = '';
            $formatted_notes[$row->enquiries_id] = '';
        }

        if ($query->num_rows > 0) {
            // List of staff
            $this->db->select("enquiries.id AS enquiry_id, CONCAT(users.surname, ' ', users.first_name, ' [', users.id, ']') AS name", false);
            $this->db->join('enquiries_enquiry_staff AS ees', 'ees.user_id = users.id');
            $this->db->join('enquiries', 'enquiries.id = ees.enquiry_id');
            $this->db->where_in('enquiries.id', $enquiry_ids);

            $staff_list = $this->user_model->get();
            if (!empty($staff_list)) {
                foreach ($staff_list as $staff) {
                    if (!empty($staff)) {
                        $enquiry_staff[$staff->enquiry_id] .= $staff->name . '<br />';
                    }
                }
            }

            // Get notes
            if ($notes) {
                $enquiry_notes = array();
                $this->db->from('enquiries_enquiry_notes een')->where_in('enquiry_id', $enquiry_ids)->order_by('creation_date', 'asc');
                $this->db->join('users', 'users.id = een.user_id');
                $this->db->select('een.*, users.salutation, users.first_name, users.surname');
                $query = $this->db->get();

                foreach ($query->result() as $row) {
                    if (empty($enquiry_notes[$row->enquiry_id])) {
                        $enquiry_notes[$row->enquiry_id] = array();
                    }
                    $row->user = new stdClass();
                    $row->user->salutation = $row->salutation;
                    $row->user->first_name = $row->first_name;
                    $row->user->surname = $row->surname;

                    $enquiry_notes[$row->enquiry_id][] = $row;
                }

                foreach ($enquiry_notes as $enquiry_id => $notes) {
                    $formatted_notes[$enquiry_id] = stripslashes(nl2br($this->enquiry_model->get_formatted_notes(null, $notes)));
                }
            }
        }

        foreach ($enquiries as $enquiry_id => $enquiry) {
            $exceptions = array('enquiries_status' => get_lang_for_constant_value('ENQUIRIES_ENQUIRY_STATUS_', $enquiry->enquiries_status),
                                'enquiries_priority' => get_lang_for_constant_value('ENQUIRIES_ENQUIRY_PRIORITY_', $enquiry->enquiries_priority));

            $row = parent::get_table_row_from_db_record($enquiry, $exceptions);

            if ($outputtype != 'csv') {
                if (!is_null($assigned_user_id)) {
                    // remove staff_id from row
                    unset($row[8]);
                }

                $row[8] = $enquiry_staff[$enquiry_id];

                if ($notes) {
                    $row[9] = $formatted_notes[$enquiry_id];
                }
            }

            $table_data['rows'][] = $row;
        }

        return $table_data;
    }

    /**
     * Given a user_id, returns a numerical array of all enquiry_ids assigned to that user
     * @param int $user_id
     * @return array
     */
    function get_assigned_enquiries($user_id) {


        $this->db->select('enquiries.id');
        $this->db->from('enquiries');
        $this->db->join('enquiries_enquiry_staff ees', 'enquiries.id = ees.enquiry_id');
        $this->db->join('users', 'users.id = ees.user_id');
        $this->db->where('ees.user_id', $user_id);

        $enquiry_list = array();
        $query = $this->db->get();

        if ($query->num_rows > 0) {
            foreach ($query->result() as $row) {
                $enquiry_list[] = $row->id;
            }
        }

        return $enquiry_list;
    }

    /**
     * Given an enquiry_id, returns a numerical array of all user_ids assigned to that enquiry
     * @param int $enquiry_id
     * @return array
     */
    function get_assigned_staff($enquiry_id) {


        $this->db->select('users.first_name, users.surname, users.salutation, users.id');
        $this->db->from('users');
        $this->db->join('enquiries_enquiry_staff ees', 'users.id = ees.user_id');
        $this->db->join('enquiries', 'enquiries.id = ees.enquiry_id');
        $this->db->where('ees.enquiry_id', $enquiry_id);

        $stafflist = array();
        $query = $this->db->get();

        if ($query->num_rows > 0) {
            foreach ($query->result() as $row) {
                $stafflist[] = $row->id;
            }
        }

        return $stafflist;
    }

    /**
     * Determines which enquiry number is adjacent to the origin enquiry_id
     * @param int $origin_id
     * @param string $direction next|previous
     * @return int
     */
    function get_neighbour_enquiry($origin_id, $direction='next') {


        if (!is_int($origin_id)) {
            return false;
        }

        $orderdir = ($direction == 'next') ? 'DESC' : 'ASC';
        $operator = ($direction == 'next') ? '<' : '>';

        $this->db->select('id')->from('enquiries');
        $this->db->where_not_in('status', array(ENQUIRIES_ENQUIRY_STATUS_ARCHIVED, ENQUIRIES_ENQUIRY_STATUS_ORDERED, ENQUIRIES_ENQUIRY_STATUS_DECLINED));
        $this->db->where("id $operator $origin_id");
        $this->db->order_by("id $orderdir");
        $this->db->limit(1);

        if ($query = $this->db->get()) {
            if ($query->num_rows == 1) {
                $result = $query->result();
                return $result[0]->id;
            }
        }

        return false;
    }

    /**
     * Returns an associative array of users who can be selected as enquirers, ordered by company name
     * @param bool $optgroups If true, will use first letter as optgroup, to make it easier to find company names
     * @return array
     */
    function get_potential_enquirers($optgroups=true) {

        $enquirers = array('' => '-- Select a Company --');

        $this->db->select('users.id, users.first_name, users.surname, users.salutation, companies.name');
        $this->db->from('users');
        $this->db->join('companies', 'users.company_id = companies.id');
        $this->db->where('companies.role <>', COMPANY_ROLE_SUPPLIER);
        $this->db->where('users.company_id >', 0);
        $this->db->order_by('companies.name');

        if ($query = $this->db->get()) {
            if ($query->num_rows > 0) {
                foreach ($query->result() as $row) {
                    $value = htmlentities($row->name) . ' (' . $this->user_model->get_name($row) . ')';
                    $first_letter = (!empty($row->name[0]) && preg_match('/[a-z]/i', $row->name[0])) ? ucfirst($row->name[0]) : 'non-alpha';
                    if ($optgroups) {
                        if (empty($enquirers[$first_letter])) {
                            $enquirers[$first_letter] = array();
                        }
                        $enquirers[$first_letter][$row->id] = $value;
                    } else {
                        $enquirers[$row->id] = $value;
                    }
                }
            }
        }

        return $enquirers;
    }

    public function get_overdue($order_field, $order_direction) {
        $interval_limit=432;// 5 days default
        $this->db->select('countries.country');
        $this->db->select('company_addresses.state');
        $this->db->select('enquiries.id AS enquiry_id');
        $this->db->select('enquiries.creation_date AS enquiry_creation_date');
        $this->db->select('companies.name AS enquirer');
        $this->db->select('users.first_name');
        $this->db->select('users.surname');
        $this->db->select('enquiries_enquiry_products.title AS product_title');
        $this->db->select('email.contact AS email');
        $this->db->select('phone.contact AS phone');
        $this->db->select('mobile.contact AS mobile');

        $this->db->join('users', 'enquiries.user_id = users.id');
        $this->db->join('user_contacts email', 'users.id = email.user_id AND email.type = ' . USERS_CONTACT_TYPE_EMAIL);
        $this->db->join('user_contacts phone', 'users.id = phone.user_id AND phone.type = ' . USERS_CONTACT_TYPE_PHONE);
        $this->db->join('user_contacts mobile', 'users.id = mobile.user_id AND mobile.type = ' . USERS_CONTACT_TYPE_MOBILE);
        $this->db->join('companies', 'users.company_id = companies.id');
        $this->db->join('company_addresses', 'company_addresses.company_id = companies.id');
        $this->db->join('countries', 'company_addresses.country_id = countries.id');
        $this->db->join('enquiries_enquiry_products', 'enquiries_enquiry_products.id = enquiries.enquiry_product_id');

        // Sanitize $order_field and $order_direction
        $this->db->order_by("$order_field $order_direction");

        $this->db->where('UNIX_TIMESTAMP() - enquiries.creation_date > ' . (int) $interval_limit . ' AND
            enquiries.id NOT IN (SELECT enquiry_id FROM enquiries_outbound_quotations) AND
            enquiries.status IN ('.ENQUIRIES_ENQUIRY_STATUS_PENDING.', '.ENQUIRIES_ENQUIRY_STATUS_STARTED.')', null, false);

        return $this->get();
    }

    public function get_pending($type, $order_field, $order_direction) {
        $interval_limit=432000;// 5 days default
        $this->db->select('countries.country');
        $this->db->select('company_addresses.state');
        $this->db->select('enquiries.id AS enquiry_id');
        $this->db->select('enquiries.creation_date AS enquiry_creation_date');
        $this->db->select('companies.name AS enquirer');
        $this->db->select('users.first_name');
        $this->db->select('users.surname');
        $this->db->select('enquiries_enquiry_products.title AS product_title');
        $this->db->select('email.contact AS email');
        $this->db->select('phone.contact AS phone');
        $this->db->select('mobile.contact AS mobile');

        $this->db->join('users', 'enquiries.user_id = users.id');
        $this->db->join('user_contacts email', 'users.id = email.user_id AND email.type = ' . USERS_CONTACT_TYPE_EMAIL);
        $this->db->join('user_contacts phone', 'users.id = phone.user_id AND phone.type = ' . USERS_CONTACT_TYPE_PHONE);
        $this->db->join('user_contacts mobile', 'users.id = mobile.user_id AND mobile.type = ' . USERS_CONTACT_TYPE_MOBILE);
        $this->db->join('companies', 'users.company_id = companies.id');
        $this->db->join('company_addresses', 'company_addresses.company_id = companies.id');
        $this->db->join('countries', 'company_addresses.country_id = countries.id');
        $this->db->join('enquiries_enquiry_products', 'enquiries_enquiry_products.id = enquiries.enquiry_product_id');

        $this->db->order_by("$order_field $order_direction");

        $interval = '';
        if ($type == ENQUIRIES_REPORT_PENDING_30) {
            $interval = ' < ' . 60 * 60 * 24 * 30;
        } else if ($type == ENQUIRIES_REPORT_PENDING_90) {
            $interval = ' BETWEEN ' . 1 + 60 * 60 * 24 * 30 . ' AND ' . 60 * 60 * 24 * 90;
        } else if ($type == ENQUIRIES_REPORT_PENDING_180) {
            $interval = ' BETWEEN ' . 1 + 60 * 60 * 24 * 90 . ' AND ' . 60 * 60 * 24 * 180;
        }

        $this->db->where('UNIX_TIMESTAMP() - enquiries.creation_date > ' . (int) $interval_limit . ' AND
            enquiries.status = '.ENQUIRIES_ENQUIRY_STATUS_QUOTED, null, false);

        return $this->get();
    }
}
