<?php
/**
 * Contains the User model
 * @package models
 */

/**
 * User_Model class
 * @package models
 */
class User_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    public $table = 'users';


    /**
     * In addition to deleting the user record, this also deletes associated contacts, options, role assignments and addresses
     * @param int $user_id
     * @return bool
     */
    public function delete($user_id) {


        $this->load->model('users/user_address_model');
        $user = $this->get($user_id);
        $result = parent::delete($user_id);

        if ($result) {
            $contacts = $this->user_contact_model->get(array('user_id' => $user_id));
            $options = $this->user_option_model->get(array('user_id' => $user_id));
            $addresses = $this->user_address_model->get(array('user_id' => $user_id));
            $roles = $this->get_roles($user_id);

            if (!empty($contacts)) {
                foreach ($contacts as $contact) {
                    $this->user_contact_model->delete($contact->id);
                }
            }
            if (!empty($options)) {
                foreach ($options as $option) {
                    $this->user_option_model->delete($option->id);
                }
            }
            if (!empty($addresses)) {
                foreach ($addresses as $address) {
                    $this->user_address_model->delete($address->id);
                }
            }
            if (!empty($roles)) {
                foreach ($roles as $role) {
                    $this->unassign_role($user_id, $role->id);
                }
            }
        }

        return $result;
    }

    /**
     * Get a single user by id, username or email (all unique identifiers)
     * @param mixed $id Integer, username, email or associative array of fields. When using array, expect array of users if more than 1 are found
     * @return mixed user or array of users
     */
    public function get_unique($id=false) {


        if (!empty($id)) {

            if (is_array($id)) {
                $this->db->from('users');
                $this->db->where($id);
            } else {
                if (!$this->select_by_unique_id($id, $ci)) {
                    return false;
                }
            }

            $query = $this->db->get();

            if ($query->num_rows() == 1) {
                $record = $query->result();
                return $record[0];

            } else if ($query->num_rows() > 1) {
                return $query->result();

            } else {
                return null;
            }

        } else {
            return false;
        }
    }

    public function select_by_unique_id($id, &$ci) {
        if (preg_match('/^[0-9]*$/', $id)) {
            $this->db->from('users');
            $this->db->where('id', $id);

        } else if (valid_email($id)) {

            $email = $this->user_contact_model->get(array('contact' => $id), true);

            if (!empty($email) && !empty($email->user_id)) {
                $this->db->from('users');
                $this->db->where('id', $email->user_id);
            } else {
                return false;
            }

        } else { // search by username
            $this->db->from('users');
            $this->db->where('username', $id);
        }

        return true;
    }

    /**
     * Returns a concatenated string of the user's name.
     *
     * @param string $format A format in which to return the string.
     *                - s = salutation
     *                - f = first name
     *                - l = last name
     * @return string
     */
    function get_name($user, $format = 's f l') {
        if (empty($user)) {
            return null;
        }

        if (!is_object($user)) {
            $user = $this->get($user);
        }

        if (!isset($user->first_name) && isset($user->id)) {
            $user = $this->get($user->id);
        }

        if (empty($user)) {
            return 'Deleted user';
        }

        $vars[strpos($format, 's')] = $user->salutation;
        $vars[strpos($format, 'f')] = $user->first_name;
        $vars[strpos($format, 'l')] = $user->surname;

        ksort($vars);

        $name = '';

        foreach ($vars as $value) {
            $name .= $value . ' ';
        }

        return rtrim($name);
    }

    /**
     * Returns the capabilities of this user as a function of his/her roles.
     *
     * @todo Cache capabilities. We only need to clear the cache when roles and capabilities are altered, which is very rare.
     * @param int $user_id
     * @return array
     */
    public function get_capabilities($user_id, $all_caps=array()) {
        $caps = array();


        $this->benchmark->mark('get_capabilities_query_start');
        $this->db->select('capabilities.*')->from('capabilities')->join('roles_capabilities', 'capabilities.id = capability_id')->join('roles', 'roles.id = role_id');
        $this->db->join('users_roles', 'users_roles.role_id = roles.id');
        $this->db->where('users_roles.user_id', $user_id);
        $query = $this->db->get();
        $this->benchmark->mark('get_capabilities_query_end');

        $this->benchmark->mark('get_dependents_start');
        if (empty($all_caps)) {
            $all_caps = $this->capability_model->get();
        }
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $caps[$row->id] = $row;
                $caps_to_check = $caps;
                $this->capability_model->get_dependents($row->id, $caps, $caps_to_check, true, $all_caps);
            }
        }
        $this->benchmark->mark('get_dependents_end');

        return $caps;
    }

     // FILTERS
     //
    /**
     * Restricts the user search to those with a given role
     *
     * @param int $role_id role id
     * @param ci_instance $ci This instance contains the db object being configured for the query
     * @return void
     */
    function filter_by_role($role_id, &$ci) {
        if (empty($role_id)) {
            return false;
        }

        $this->db->join('users_roles', 'users.id = users_roles.user_id');
        $this->db->where('users_roles.role_id', $role_id);
    }

    /**
     * Returns all the roles assigned to this user
     * @access public
     * @param int $user_id
     * @return array
     */
    function get_roles($user_id) {

        $this->load->model('users/role_model');
        $this->db->join('users_roles', 'users_roles.role_id = roles.id');
        $this->db->where('users_roles.user_id', $user_id);
        $this->db->select('roles.id', 'id');
        $this->db->select('roles.name', 'name');
        $this->db->select('roles.description', 'description');
        $roles = $this->role_model->get();
        return $roles;
    }

    /**
     * Assigns a role to a user
     * @access public
     * @param int $user_id The id of the user who will receive the role
     * @param int $role_id The id of the role being assigned
     * @return boolean Failure or success
     */
    function assign_role($user_id, $role_id) {

        $role_assignment = array();
        $role_assignment['user_id'] = $user_id;
        $role_assignment['role_id'] = $role_id;

        // Check whether this assignment already exists or not
        $result = $this->db->from('users_roles')->where($role_assignment)->get();

        if ($result->num_rows == 0) {
            return $this->db->insert('users_roles', $role_assignment);
        } else {
            return false;
        }
    }

    /**
     * Un-assigns a user from a role
     * @param int $user_id
     * @param int $role_id
     * @return bool
     */
    function unassign_role($user_id, $role_id) {

        $result = $this->db->get_where('users_roles', array('user_id' => $user_id, 'role_id' => $role_id));

        if ($result->num_rows == 0) {
            return false;
        } else {
            $array = $result->result();
            return $this->db->delete('users_roles', array('id' => $array[0]->id));
        }
    }

    /**
     * Checks whether a given user has the given roles
     * @access public
     * @param int $user_id The id of the user
     * @param array $role_ids An array of role ids to check
     * @param string $operator AND|OR
     * @return boolean
     */
    function has_roles($user_id, $role_ids, $operator='AND') {

        $this->load->model('users/role_model');

        $result = $this->db->from('users_roles')->where('user_id', $user_id)->where_in('role_id', $role_ids)->get();

        if ($operator == 'OR' && $result->num_rows > 0) {
            return true;
        } else if ($operator == 'AND' && $result->num_rows == count($role_ids)){
            return true;
        }

        return false;
    }

    /**
     * Returns an array of users who have a given capability.
     *
     * This takes capability dependencies into consideration, so
     * for example if a user has site:doanything, any capname requested here will include that user.
     * @access public
     * @param string $capname The name of the capability (e.g. enquiries:editenquiries)
     * @param array $blacklist An array of capability names which the lsit of users must not have
     * @return array
     */
    function get_users_by_capability($capname, $blacklist=array(), $where_conditions=array()) {

        $this->load->model('users/capability_model');
        $this->load->model('users/role_model');

        $capability = $this->capability_model->get(array('name' => $capname), true);

        if (!$capability) {
            show_error("Capability '$capname' doesn't exist!");
            return false;
        }

        $included_caps = array($capability->id);
        $loopcap = clone($capability);
        while (!empty($loopcap->dependson)) {
            $parentcap = $this->capability_model->get($loopcap->dependson);
            $loopcap = clone($parentcap);
            $included_caps[] = $loopcap->id;
        }

        $this->db->distinct();
        $this->db->select('users.*');
        $this->db->join('users_roles', 'users.id = users_roles.user_id');
        $this->db->join('roles_capabilities', 'roles_capabilities.role_id = users_roles.role_id');
        $this->db->join('capabilities', 'capabilities.id = roles_capabilities.capability_id');
        $this->db->where_in('capabilities.id', $included_caps);
        $this->db->order_by('surname');

        if (!empty($where_conditions)) {
            $this->db->where($where_conditions);
        }

        if (!empty($blacklist)) {
            $this->db->where_not_in('capabilities.name', $blacklist);
        }

        return $this->user_model->get();
    }

    /**
     * Returns an array of all roles not yet assigned to a given user
     * @access public
     * @param int $user_id
     * @return array
     */
    function get_available_roles($user_id) {

        $this->load->model('users/role_model');
        // Get roles assigned to this user
        $query = $this->db->from('roles')->where("id NOT IN (SELECT role_id FROM users_roles WHERE user_id = $user_id)", null, false)->get();
        $available_roles = array();
        foreach ($query->result() as $row) {
            $available_roles[] = $row;
        }

        return $available_roles;
    }

    /**
     * Returns an associative array of data about a user, including contact details, company and company address.
     * This is used primarily for populating forms.
     * @param int $user_id
     * @return array
     */
    function get_values($user_id) {

        $this->load->model('company_model');
        $this->load->model('company_address_model');
        $this->load->model('users/user_model');
        $this->load->model('users/user_contact_model');
        $this->load->model('users/user_option_model');

        $this->db->select("(SELECT contact FROM user_contacts WHERE user_id = $user_id AND type = ".USERS_CONTACT_TYPE_EMAIL." ORDER BY default_choice DESC LIMIT 1) AS email", false);
        $this->db->select("(SELECT contact FROM user_contacts WHERE user_id = $user_id AND type = ".USERS_CONTACT_TYPE_EMAIL." AND default_choice <> 1 LIMIT 1) AS email2", false);
        $this->db->select("(SELECT contact FROM user_contacts WHERE user_id = $user_id AND type = ".USERS_CONTACT_TYPE_PHONE." ORDER BY default_choice DESC LIMIT 1) AS phone", false);
        $this->db->select("(SELECT contact FROM user_contacts WHERE user_id = $user_id AND type = ".USERS_CONTACT_TYPE_PHONE." AND default_choice <> 1 LIMIT 1) AS phone2", false);
        $this->db->select("(SELECT contact FROM user_contacts WHERE user_id = $user_id AND type = ".USERS_CONTACT_TYPE_FAX.") AS fax", false);
        $this->db->select("(SELECT contact FROM user_contacts WHERE user_id = $user_id AND type = ".USERS_CONTACT_TYPE_MOBILE.") AS mobile", false);
        $this->db->select("(SELECT value FROM user_options WHERE user_id = $user_id AND name = 'first_name_ch') AS first_name_ch", false);
        $this->db->select("(SELECT value FROM user_options WHERE user_id = $user_id AND name = 'surname_ch') AS surname_ch", false);
        $this->db->select("(SELECT COUNT(*) FROM enquiries WHERE user_id = $user_id) AS enquiries_count", false);
        $this->db->select('users.company_id as company_id, users.salutation AS salutation, users.first_name AS first_name, users.surname AS surname');

        $user = $this->get($user_id);

        // Get company info
        if (!empty($user->company_id)) {
            $company = $this->company_model->get($user->company_id);
            $address = $this->company_address_model->get(array('company_id' => $company->id, 'default_address' => 1), true);
            $address_ch = $this->company_address_model->get(array('company_id' => $company->id, 'type' => COMPANY_ADDRESS_TYPE_CH), true);
        } else {
            $company = null;
            $address = null;
            $address_ch = null;
        }

        $arrays = array('company', 'user', 'address', 'address_ch');
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

        return array_merge($user_array, $company_array, $address_array, $address_ch_array);
    }

    /**
     * Returns false if the user is not yet registered with the given email address, or returns the user's user_id.
     */
    function already_exists($email, $default_choice = 1) {

        $this->db->from('user_contacts')->where('type', USERS_CONTACT_TYPE_EMAIL)->where('contact', $email);
        if ($default_choice) {
            $this->db->where('default_choice', 1);
        }
        $count_db = clone($this->db);
        $count = $count_db->count_all_results();

        if ($count > 1) {
            add_message("$count users have the same email address ($email)!!! This is not permitted in this system, aborting current procedure!", 'error');
            return 999999999; // An impossible user id
        }

        $query = $this->db->select('user_id')->get();
        if ($query->num_rows == 1) {
            $array = $query->result();
            return $array[0]->user_id;
        } else {
            return false;
        }
    }

    /**
     * When the user's contact details are given all at once, this function
     * is used to create the right DB records.
     *
     * @param int $user_id
     * @param string $prefix An optional prefix to the keys in the array
     * @param array $data An optional array of key => value pairs. If not set, user $_POST
     * @return void
     */
    public function assign_contact_data($user_id, $prefix = null, $data = array()) {

        $this->load->model('users/user_contact_model');

        // If no data is given explicitely, look in $_POST
        if (empty($data)) {
            $data = $_POST;
            if (empty($data)) {
                return false;
            }
        }

        foreach ($data as $key => $value) {
            if (!is_array($value)) {

                $default_choice = 1;

                // If the number 2 (as in email2 or phone2) is given, turn off default choice
                if (strpos($key, '2') > 0) {
                    $default_choice = 0;
                }

                // Strip keys of their prefix if one is given
                if (!empty($prefix) && preg_match('/'.$prefix.'(.*)/', $key, $matches)) {
                    $key = $matches[1];
                }

                $contact_types = array('email' => USERS_CONTACT_TYPE_EMAIL,
                                       'phone' => USERS_CONTACT_TYPE_PHONE,
                                       'mobile' => USERS_CONTACT_TYPE_MOBILE,
                                       'fax' => USERS_CONTACT_TYPE_FAX);

                // Check that the field corresponds to a contact entry
                if (array_key_exists(rtrim($key, '2'), $contact_types)) {
                    // Update the contact if it already exists
                    $contact_data = array('user_id' => $user_id, 'type' => $contact_types[rtrim($key, '2')], 'default_choice' => $default_choice);
                    $user_contact = $this->user_contact_model->get($contact_data, true);

                    if ($user_contact) {
                        if (strlen($value) == 0) {
                            $this->user_contact_model->delete($user_contact->id);
                        } elseif ($value != $user_contact->contact) {
                            $this->user_contact_model->edit($user_contact->id, array('contact' => $value));
                        }
                    } elseif (strlen($value) > 0) {
                        $contact_data['contact'] = $value;
                        $this->user_contact_model->add($contact_data);
                    }
                }
            }
        }
    }

    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {


        $this->dbfields = array($this->dbfield->get_field('users.id', 'user_id', 'ID'),
                                $this->dbfield->get_field('CONCAT(users.surname," ",users.first_name)', 'user_name', 'Name'),
                                $this->dbfield->get_field('user_contacts.contact', 'user_email', 'Email'));

        // We use LEFT OUTER joins because some users may not have an email address or an assigned role
        $this->db->join('user_contacts', 'user_contacts.user_id = users.id AND user_contacts.type = ' . USERS_CONTACT_TYPE_EMAIL, 'LEFT OUTER');
        $this->db->join('users_roles', 'users_roles.user_id = users.id', 'LEFT OUTER');
        $this->db->join('roles', 'users_roles.role_id = roles.id', 'LEFT OUTER');

        $this->db->select('users.first_name AS user_first_name');
        $this->db->select('users.surname AS user_surname');
        $this->db->select('roles.id AS role_id');

        $this->db->distinct();

        parent::apply_db_selects();
        $this->db->group_by('user_id');
        $numrows = $this->filter($params, $filters);

        if ($limit) {
            $this->db->limit($limit);
        }

        // For table headings
        $table_data = array('headings' => parent::get_table_headings() + array('user_roles' => 'Roles'),
                            'rows' => array(),
                            'numrows' => $numrows);

        $users = array();
        $user_ids = array();
        $roles = array();
        $user_roles = array();

        $query = parent::get_with_aliased_columns();

        foreach ($query->result() as $row) {
            $user_ids[] = $row->user_id;
            $users[$row->user_id] = $row;
            $roles[$row->user_id] = '';
            $user_roles[$row->user_id] = '';
        }

        if ($query->num_rows > 0) {
            // List of roles
            $this->load->model('users/role_model');
            $this->db->select("users.id AS user_id, roles.name AS role_name", false);
            $this->db->join('users_roles AS ur', 'ur.role_id = roles.id');
            $this->db->join('users', 'users.id = ur.user_id');
            $this->db->where_in('users.id', $user_ids);

            $roles_list = $this->role_model->get();

            foreach ($roles_list as $role) {
                if (!empty($role)) {
                    $user_roles[$role->user_id] .= $role->role_name . '<br />';
                }
            }
        }

        foreach ($users as $user_id => $user) {
            $row = parent::get_table_row_from_db_record($user);
            $row[] = $user_roles[$user_id];
            $table_data['rows'][] = $row;
        }

        return $table_data;

    }

    /**
     * Function used only for JSON.
     * @TODO add more parameter to make this function more useful
     */
    public function get_by_country_id($country_id) {

        $this->db->join('companies', 'users.company_id = companies.id');
        $this->db->join('company_addresses', 'companies.id = company_addresses.company_id');
        $this->db->join('user_contacts', 'user_contacts.user_id = users.id');
        $this->db->select('users.id');
        $this->db->select('users.first_name');
        $this->db->select('users.surname');
        $this->db->select('users.salutation');
        $this->db->select('user_contacts.contact AS email');
        $this->db->order_by('surname');
        $this->db->distinct();
        $users = $this->get(array('country_id' => $country_id, 'user_contacts.type' => USERS_CONTACT_TYPE_EMAIL));

        $return_array = array();
        foreach ($users as $user) {
            $return_array[$user->id] = array('email' => $user->email, 'name' => $this->user_model->get_name($user, 'l f'), 'user_id' => $user->id);
        }
        return $return_array;
    }

    function get_for_csv($user_id) {

        $this->db->join('companies', 'users.company_id = companies.id');
        $this->db->join('company_addresses', 'companies.id = company_addresses.company_id');
        $this->db->join('user_contacts', 'user_contacts.user_id = users.id');
        $this->db->join('countries', 'company_addresses.country_id = countries.id');
        $this->db->select('users.id');
        $this->db->select('users.first_name');
        $this->db->select('users.surname');
        $this->db->select('user_contacts.contact AS email');
        $this->db->select('countries.country');
        $this->db->select('companies.name AS company_name');
        $user = $this->get(array('users.id' => $user_id, 'user_contacts.type' => USERS_CONTACT_TYPE_EMAIL), true);
        return $user;
    }
}
?>
