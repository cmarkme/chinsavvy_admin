<?php
/**
 * File containing the MY_Model class
 * @package models
 */

/**
 * MY_Model class
 * This class takes care of common SQL operations like get, delete, add and edit, and takes care of creation and revision timestamps
 * Assumptions:
 *   - All tables have the following fields:
 *      * id smallint(5) primary key autoincrement
 *      * creation_date int(10) NOT NULL
 *      * revision_date int(10) NOT NULL
 *      * status varchar(32) NOT NULL DEFAULT 'Active'
 * @package models
 */
class MY_Model extends CI_Model {
    /**
     * @var string $table This MUST be set in the child classes
     */
    public $table=null;
    public $cache_keys=array();
    public $dbfields = array();

    function count_all_results() {

        $result = $this->db->count_all_results($this->table);
        return $result;
    }

    function get($id_or_fields=null, $first_only=false, $order_by=null, $select_fields=array()) {

        $this->db->from($this->table);
        if (!empty($select_fields)) {
            $this->db->select($select_fields);
        }

        if (!empty($id_or_fields)) {
            if (is_array($id_or_fields)) {
                $this->db->where($id_or_fields);
            } else {
                $this->db->where($this->table.'.id', $id_or_fields);
                //var_dump($this->db->where());
            }
        }

        if (!is_null($order_by)) {
            $this->db->order_by($order_by);
        }

        $query = $this->db->get();
        $return_value = null;

        if ($query->num_rows == 0) {
            xdebug_break();if (is_array($id_or_fields) && !$first_only) {
                return array();
            } else {
                return null;
            }
        } else if (!is_array($id_or_fields) && !is_null($id_or_fields)) {
            $row = $query->result();
            $return_value = $row[0];
        } else if ($query->num_rows == 1) {
            $row = $query->result();
            $return_value = array($row[0]);
        } else if ($query->num_rows > 0) {
            $results = array();
            foreach ($query->result() as $row) {
                $results[] = $row;
            }
            $return_value = $results;
        }

        if (!is_null($return_value) && $first_only && is_array($id_or_fields) && !is_null($id_or_fields)) {
            return reset($return_value);
        } else {
            return $return_value;
        }
    }

    function delete($id) {

        log_user_action("is now deleting record #$id from the $this->table table (MODEL)");
        $this->delete_cache_keys();
        $this->db->where($this->table.'.id', $id);
        return $this->db->delete($this->table);
    }

    /**
     * @param mixed $fields Array of stdclass
     * @return insert_id
     */
    function add($fields) {
        $fields = (array) $fields;
        $this->delete_cache_keys();


        if (isset($fields['creation_date'])) {
            $fields['revision_date'] = $fields['creation_date'];
        } else {
            $fields['creation_date'] = time();
            $fields['revision_date'] = time();
        }

        $this->db->insert($this->table, $fields);
        return $this->db->insert_id();
    }

    function delete_cache_keys() {

        $this->load->driver('cache');
        foreach ($this->cache_keys as $key) {
            if ($key == '*') {
                $this->cache->apc->clean();
            } else {
                $this->cache->apc->delete($key);
            }
        }
    }

    function add_cache_key($key) {
        if (!in_array($key, $this->cache_keys)) {
            $this->cache_keys[] = $key;
        }
    }

    function edit($id, $fields) {

        $this->delete_cache_keys();
        $fields['revision_date'] = time();
        $this->db->where($this->table.'.id', $id);
        return $this->db->update($this->table, $fields);
    }

    function limit($limit, $offset=null) {

        $this->db->limit($limit, $offset);
    }

    function order_by($field, $direction='ASC') {

        $this->db->order_by($field, $direction);
    }

    /**
     * This filters (for paging only) and sorts the database object using params provided by jQuery.dataTables.js
     * $this->dbfields must be defined in child classes
     * @param array $params associative array of DB field->value used to restrict the query
     * @param array $filters Array of Filter objects
     * @param boolean $apply_pagination
     * @param array $absolute_params Array of DB field->value applied before all other filtering, used to reduce overall num_rows
     * @return int Number of rows returned after filtering but before paging limits are applied
     */
    function filter($params=array(), $filters=array(), $apply_pagination=true, $absolute_params=array()) {


        // Parse filters and apply them to SQL query
        foreach ($filters as $filter) {
            $sql_condition = $filter->get_sql_condition();

            if (!empty($sql_condition)) {
                $this->db->where($sql_condition);
            }
        }

        // Get a count of filtered results before pagination limits are applied. Because of possible group by (which messes up the count), we run the query manually to get num_rows()
        // The following intricate code replaces the normal SELECT statement with the building of a custom table that can contain aliased columns, which can be used in the WHERE clause
        $newdb = clone($this->db);
        $newdb->from($this->table);
        $where_clause = $newdb->ar_where;
        $newdb->ar_where = array();
        $newdb->_escape_char = '';
        $select_clause = $newdb->_compile_select();

        // Adding custom WHERE clauses
        if (!empty($absolute_params)) {
            $absolute_clause = " WHERE ";
            foreach ($absolute_params as $param => $value) {
                if (preg_match('/[a-z0-9\-\_ ]*(IS|IN|!=|<|>|<>)/', $param)) {
                    $absolute_clause .= "$param $value AND ";
                } else {
                    $absolute_clause .= "$param = '$value' AND ";
                }
            }
            $absolute_clause = substr($absolute_clause, 0, -4);

            // This must be inserted before any GROUP BY clause
            if (strpos($select_clause, 'GROUP BY')) {
                $group_pos = strpos($select_clause, 'GROUP BY');
                $select_clause = substr($select_clause, 0, $group_pos) . $absolute_clause . substr($select_clause, $group_pos);
            } else {
                $select_clause .= $absolute_clause;
            }
        }
        $newfrom = '('.$select_clause.') '.$this->table;
        $newdb->ar_select = array();
        $newdb->ar_join = array();
        $newdb->ar_from = array();
        $newdb->from($newfrom);

        foreach ($where_clause as $key => $val) {
            if (preg_match('/(.*)`([a-z_]*)`(.*)/', $val, $matches) && !strstr($val, $this->table)) {
                $where_clause[$key] = $matches[1] . $this->table . '.' . $matches[2] . $matches[3];
            }
        }

        $newdb->ar_where = $where_clause;
        $newdb->cache_off();
        $newdb->count_all_results();

        $sql = $newdb->last_query();
        $query = $newdb->query($sql);

        // If the count(*) query returned more than one row, it means there is a group_by clause. Each row represents a count of 1
        if ($query->num_rows() > 1) {
            $count = $query->num_rows();
        } else {
            if ($rows = $query->result()) {
                $count = $rows[0]->numrows;
            } else {
                $count = 0;
            }
        }
        if (isset($params['sortcol_0']) && !is_null($params['sortcol_0'])) {
            $orderby = '';
            for ($i = 0; $i < $params['sortingcols']; $i++) {
                if ($this->dbfields[$i]->alias == 'roles') {
                    $orderby = '';
                } else {
                    $orderby .= $this->dbfields[$params["sortcol_$i"]]->alias . ' ' . $params["sortdir_$i"] . ', ';
                }
            }

            if (!empty($orderby)) {
                $orderby = substr_replace($orderby, "", -2);
                $this->db->order_by($orderby);
            }
        }

        if ($apply_pagination && isset($params['perpage']) && isset($params['start'])) {
            $this->db->limit($params['perpage'], $params['start']);
        }

        return $count;
    }

    /**
     * Abstracted function that depends on $dbfields being set in the child class. Given a result record from the DB query, this matches the field aliases from the dbfields with the data of the record
     * @param object $dbrecord
     * @param array $exceptions The child class may need to do custom formatting on the fields, in which case these should be passed in this array, with alias as key
     * @return array
     */
    public function get_table_row_from_db_record($dbrecord, $exceptions=array()) {
        if (empty($this->dbfields) || empty($dbrecord)) {
            return false;
        }

        $row = array();
        $this->load->helper('date');

        foreach ($this->dbfields as $dbfield) {
            if (!empty($exceptions[$dbfield->alias])) {
                $row[] = $exceptions[$dbfield->alias];
            } else {
                if (preg_match('/_date/', $dbfield->alias)) {
                    $row[] = unix_to_human($dbrecord->{$dbfield->alias});
                } else {
                    $row[] = $dbrecord->{$dbfield->alias};
                }
            }
        }

        return $row;
    }

    public function get_table_headings() {
        if (empty($this->dbfields)) {
            return false;
        }


        $table_headings = array();
        foreach ($this->dbfields as $dbfield) {
            $table_headings[$dbfield->alias] = $dbfield->label;
        }
        return $table_headings;
    }

    public function apply_db_selects() {
        if (empty($this->dbfields)) {
            return false;
        }


        foreach ($this->dbfields as $dbfield) {
            $this->db->select("$dbfield->dbselect AS $dbfield->alias", false);
        }
    }

    /**
     * When called by children models, returns the next autoincrement value. $this->table MUST be set first.
     * @return int
     */
    public function get_next_id() {

        $query = $this->db->select_max('id')->from($this->table)->get();
        $result_array = $query->result();
        return $result_array[0]->id + 1;
    }

    /**
     * Retrieves data from $_POST and attempts to insert a record in this model's DB table. Optional prefix is used to avoid field name collisions
     * @param string $prefix
     * @param array $data Additional fields not set in $_POST
     * @return int If successful, returns the PK value of the new record
     */
    public function add_from_post($prefix=null, $data=array()) {

        $object = array();
        foreach ($_POST as $key => $val) {
            if (preg_match('/'.$prefix.'(.*)/i', $key, $matches) && $this->db->field_exists($matches[1], $this->table)) {
                $object[$matches[1]] = $this->input->post($key);
            }
        }

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $object[$key] = $val;
            }
        }

        return $this->add($object);
    }

    /**
     * Retrieves data from $_POST and attempts to update a record in this model's DB table. Optional prefix is used to avoid field name collisions
     * @param int $id Primary Key value
     * @param array $data Additional fields not set in $_POST
     * @param string $prefix
     * @return bool
     */
    public function edit_from_post($id, $prefix=null, $data=array()) {

        $object = array();
        foreach ($_POST as $key => $val) {
            if (preg_match('/'.$prefix.'(.*)/i', $key, $matches) && $matches[1] != 'id' && $this->db->field_exists($matches[1], $this->table)) {
                $object[$matches[1]] = $this->input->post($key);
            }
        }

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $object[$key] = $val;
            }
        }

        return $this->edit($id, $object);
    }

    /**
     * Returns an array for the current model indexed by ID
     * @param string $name_field The field that will be used as the option labels
     * @param string $null_option If false or null, no null option. Otherwise uses the given string for null index
     * @param closure $label_function An optional function that can perform additional formatting on the label
     * @param string $optgroups If false, optgroups not used. If a string is given, the matching field from the DB table will be used to group items by category, ready to be used in HTML optgroups
     * @param string $optgroup_constant_prefix If the grouping variable is an int represented by a constant, this prefix will obtain the matching lang string
     * @return array
     */
    public function get_dropdown($name_field, $null_option=true, $label_function=false, $optgroups=false, $optgroup_constant_prefix=null, $null_value = null) {

        $options = array();

        if (!empty($null_option)) {
            if ($null_option === true) {
                $null_option = '-- Select One --';
            }
            $options[$null_value] = $null_option;
        }

        $objects = $this->get();
        if (is_array($objects)) {
            foreach ($objects as $object) {
                $label = ($label_function) ? $label_function($object) : $object->$name_field;
                if ($optgroups) {
                    $optgroup_label = (empty($optgroup_constant_prefix)) ? $object->$optgroups : ucfirst(get_lang_for_constant_value($optgroup_constant_prefix, $object->$optgroups));
                    if (empty($options[$optgroup_label])) {
                        $options[$optgroup_label] = array();
                    }
                    $options[$optgroup_label][$object->id] = $label;
                } else {
                    $options[$object->id] = $label;
                }
            }
        }

        return $options;
    }

    /**
     * If you are trying to filter the results by an aliased column (for example, one that is constructed from data from different tables), use this instead of the normal $this->db->get()
     */
    public function get_with_aliased_columns($absolute_params=array()) {

        $where_clause = $this->db->ar_where;
        $this->db->ar_where = array();

        $orderby = $this->db->ar_orderby;
        $this->db->ar_orderby = array();

        $limit = $this->db->ar_limit;
        $this->db->ar_limit = array();

        $this->db->from($this->table);
        $select_clause = $this->db->_compile_select();

        // Adding custom WHERE clauses
        if (!empty($absolute_params)) {
            $absolute_clause = " WHERE ";
            foreach ($absolute_params as $param => $value) {
                if (preg_match('/[a-z0-9\-\_ ]*(IS|IN|!=|<|>|<>)/', $param)) {
                    $absolute_clause .= "$param $value AND ";
                } else {
                    $absolute_clause .= "$param = '$value' AND ";
                }
            }
            $absolute_clause = substr($absolute_clause, 0, -4);

            // This must be inserted before any GROUP BY clause
            if (strpos($select_clause, 'GROUP BY')) {
                $group_pos = strpos($select_clause, 'GROUP BY');
                $select_clause = substr($select_clause, 0, $group_pos) . $absolute_clause . substr($select_clause, $group_pos);
            } else {
                $select_clause .= $absolute_clause;
            }
        }
        $newfrom = '('.$select_clause.') '.$this->table;
        $this->db->_escape_char = '';
        $this->db->ar_select = array();
        $this->db->ar_join = array();
        $this->db->ar_from = array();
        $this->db->from($newfrom);
        $this->db->ar_limit = $limit;
        $this->db->ar_orderby = $orderby;

        foreach ($where_clause as $key => $val) {
            if (preg_match('/(.*)`([a-z_]*)`(.*)/', $val, $matches)) {
                $where_clause[$key] = $matches[1] . $this->table . '.' . $matches[2] . $matches[3];
            }
        }
        $this->db->ar_where = $where_clause;
        return $this->db->get();
    }

    /**
     * Replaces the names of the selected columns using the given format
     * @param string $format
     * @param array $fields_to_format If empty, will return all the fields
     * @return array An array of the fields
     */
    public function get_formatted_column_names($format, $fields_to_format=array()) {

        $table_fields = $this->db->list_fields($this->table);
        $formatted_fields = array();

        if (empty($fields_to_format)) {
            $fields_to_format = $table_fields;
        }

        foreach ($table_fields as $field) {
            if (in_array($field, $fields_to_format)) {
                $formatted_fields[$this->table.'.'.$field] = sprintf($format, $field);
            }
        }

        return $formatted_fields;
    }
}
?>
