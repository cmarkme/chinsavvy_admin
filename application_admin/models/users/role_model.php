<?php
/**
 * Contains the Role Model class
 * @package models
 */

/**
 * Role Model class
 * @package models
 */
class Role_Model extends MY_Model {
    /**
     * @access public
     * @var string The DB table used by this model
     */
    public $table = 'roles';

    /**
     * Returns all the capabilities associated with a role identified by role_id
     * @access public
     * @param int $role_id The ID of the role
     * @return array Array of capabilities
     */
    public function get_capabilities($role_id) {
        $caps = array();

        $this->db->select('capabilities.*')->from('capabilities')->join('roles_capabilities', 'capabilities.id = capability_id')->join('roles', 'roles.id = role_id')->where('role_id', $role_id);
        $this->db->order_by('capabilities.name');
        $query = $this->db->get();
        $this->load->model('users/capability_model');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $row->label = $this->capability_model->get_label($row);
                $caps[] = $row;
            }
        }

        return $caps;
    }

    /**
     * Returns all the users associated with a role identified by role_id
     * @access public
     * @param int $role_id The ID of the role
     * @return array Array of users
     */
    public function get_users($role_id) {

        $users = array();

        $this->db->select('users.*')->from('users')->join('users_roles', 'users_roles.user_id = users.id')->join('roles', 'users_roles.role_id = roles.id')->where('role_id', $role_id);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $users[] = $row;
            }
        }

        return $users;
    }

    /**
     * Given a capability name (not id!), adds that capability to the role identified by role_id
     * @access public
     * @param int $role_id The ID of the role with which the capability will be associated
     * @param string $capname The name of the capability to associate with the role
     * @return int|error The id of the roles_capabilities association, or a fatal error if the given capability name doesn't exist
     */
    public function add_capability($role_id, $capname) {
        // Find capability first


        if ($cap = $this->capability_model->get(array('name' => $capname), true)) {
            $this->db->from('roles_capabilities')->where(array('role_id' => $role_id, 'capability_id' => $cap->id));

            if ($this->db->count_all_results() == 0) {
                return $this->db->insert('roles_capabilities', array('role_id' => $role_id, 'capability_id' => $cap->id));
            }
        }

        show_error("Capability $capname doesn't exist!");
        return false;
    }

    /**
     * Given a capability name (not id!), removes that capability from the role identified by role_id
     * @access public
     * @param int $role_id The ID of the role from which the capability will be removed
     * @param string $capname The name of the capability to remove from the role
     * @return true|error True if successful, or a fatal error if the given capability name doesn't exist
     */
    public function remove_capability($role_id, $capname) {


        if ($cap = $this->capability_model->get(array('name' => $capname), true)) {
            $this->db->where('capability_id', $cap->id)->where('role_id', $role_id);
            return $this->db->delete('roles_capabilities');
        }

        show_error("Capability $capname doesn't exist!");
        return false;
    }

    /**
     * Duplicates a role, including all its associations with capabilities, and (optionally) its users.
     * @access public
     * @param int $role_id The ID of the role to duplicate
     * @param boolean $copyusers Whether or not to duplicate user associations as well
     * @return stdclass|error A stdClass representing the new role, or a fatal error if the new role could not be inserted in the DB
     */
    public function duplicate($role_id, $copyusers = false) {

        $thisrole = $this->get($role_id);
        $newrole = new stdClass();
        $newrolename = "Copy of $thisrole->name";
        $newrole->name = $newrolename;

        $counter = 0;
        while ($this->get(array('name' => $newrole->name))) {
            $counter++;
            $newrole->name = $newrolename . " ($counter)";
        }

        // Insert, then add same capabilities as original role
        if ($this->db->insert('roles', $newrole)) {
            $newrole->id = $this->db->insert_id();

            $capabilities = $this->role_model->get_capabilities($role_id);

            foreach ($capabilities as $capability) {
                $this->db->insert('roles_capabilities', array('capability_id' => $capability->id, 'role_id' => $newrole->id));
            }

            if ($copyusers) {
                $users = $this->role_model->get_users($role_id);
                foreach ($users as $user) {
                    $this->db->insert('users_roles', array('user_id' => $user->id, 'role_id' => $newrole->id));
                }
            }

            return $newrole;
        } else {
            show_error('Error duplicating the role!');
            return false;
        }
    }

    function get_data_for_listing($params=array(), $filters=array(), $limit=null) {


        $this->dbfields = array($this->dbfield->get_field('roles.id', 'role_id', 'ID'),
                                $this->dbfield->get_field('roles.name', 'role_name', 'Name'),
                                $this->dbfield->get_field('roles.description', 'role_description', 'Description'));

        parent::apply_db_selects();
        $numrows = $this->filter($params, $filters);

        // For table headings
        $table_data = array('headings' => parent::get_table_headings(),
                            'rows' => array(),
                            'numrows' => $numrows);

        $query = $this->db->get($this->table);

        foreach ($query->result() as $row) {
            $table_data['rows'][] = parent::get_table_row_from_db_record($row);
        }

        return $table_data;

        if ($limit) {
            $this->db->limit($limit);
        }
    }

    /**
     * Returns an array of users who can be assigned to the given role (who are not already assigned to it).
     * @param int $role_id
     * @return array
     */
    function get_assignable_users($role_id, $search_term=null) {

        $this->db->distinct()->join('users_roles ur', 'ur.user_id = u.id')->where('ur.role_id <>', $role_id)->select('first_name, salutation, surname, u.id');
        if (!is_null($search_term)) {
            $this->db->where("first_name LIKE '$search_term%'")->or_where("surname LIKE '$search_term%'");
        }
        $query = $this->db->get('users u');

        $users = array();
        foreach ($query->result() as $result) {
            $user = new stdClass();
            $user->value = $result->id;
            $user->label = $this->user_model->get_name($result);
            $users[] = $user;
        }
        return $users;
    }

    public function get_dropdown() {

        $query = $this->db->from($this->table)->select('id, name')->order_by('name')->get();
        $role_ids = array('' => '-- Select a Role --');

        foreach ($query->result() as $row) {
            $role_ids[$row->id] = $row->name;
        }
        return $role_ids;
    }

    /**
     * Returns a list of users who have all of the capabilities (by name) in the $whitelist array, and none of the capabilities in the $blacklist array
     * @param array $whitelist
     * @param array $blacklist Only used to speed up the search for the whitelisted capabilities. Don't use on its own
     * @return array
     */
    public function get_users_by_capabilities($whitelist, $blacklist=array()) {

        $all_caps = $this->capability_model->get();
        $cap_ids = array();

        foreach ($all_caps as $cap) {
            $cap_ids[$cap->name] = $cap->id;
        }

        $blacklist_ids = array();

        if (!empty($blacklist)) {
            foreach ($blacklist as $cap_name) {
                $blacklist_ids[] = $cap_ids[$cap_name];
            }
        }

        $whitelist_ids = array();
        if (!empty($whitelist)) {
            foreach ($whitelist as $cap_name) {
                $whitelist_ids[] = $cap_ids[$cap_name];
            }
        }

        // Begin by restricting the search using the blacklist array, if it has capabilities
        $wheresql = 'id NOT IN (SELECT role_id FROM roles_capabilities WHERE capability_id IN (';
        foreach ($blacklist_ids as $id) {
            $wheresql .= "$id,";
        }
        $wheresql = substr($wheresql, 0, -1) . '))';
        $this->db->select('id, name', false);
        $this->db->where($wheresql, null, false);
        $roles = $this->role_model->get();

        $role_ids = array();
        foreach ($roles as $role) {
            $role_ids[] = $role->id;
        }

        $this->db->where_not_in('capability_id', $blacklist_ids);
        $this->db->where_in('users_roles.role_id', $role_ids);
        $this->db->select('users.id, users.first_name, users.surname, users.salutation');
        $this->db->distinct();
        $this->db->from('users_roles, roles_capabilities');
        $this->db->where('users.id = users_roles.user_id');
        $this->db->where('roles_capabilities.role_id = users_roles.role_id');
        $users = $this->user_model->get();

        if (empty($whitelist_ids)) {
            return $users;
        }

        foreach ($users as $key => $user) {
            $user_caps = $this->user_model->get_capabilities($user->id, $all_caps);

            foreach ($whitelist_ids as $cap_id) {
                if (!isset($user_caps[$cap_id])) {
                    unset($users[$key]);
                    continue 2;
                }
            }
        }

        return $users;
    }
}
?>
