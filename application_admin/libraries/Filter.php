<?php
/**
 * Contains the filter library
 * @package libraries
 */

/**
 * Filter class
 * @package libraries
 */
class Filter {
    var $title;
    var $id;
    var $name;
    var $default;
    var $sql_condition_format = "%s = '%s'";
    var $filters = array();

    public function add_filter($type) {
        $args = array(null, null, null, null, null, null);
        foreach (func_get_args() as $key => $argument) {
            $args[$key] = $argument;
        }
        $classname = "filter_$type";
        if (class_exists($classname)) {
            $newfilter = new $classname($args[1], $args[2], $args[3], $args[4], $args[5]);
            $this->filters[] = $newfilter;
            return $newfilter;
        }
        return false;
    }

    public function initialise($title, $name, $default='') {

        $this->title = "Filter by $title";
        $this->id = $name . 'filter';
        $this->name = $name;
        $this->default = $default;
    }

    public function get_open_html() {
        return '<td>'.form_label($this->title, $this->id);
    }

    public function get_close_html() {
        return '</td>';
    }

    public function get_post() {
        $ci = get_instance();
        return $ci->input->post($this->name);
    }

    public function get_sql_condition() {
        $value = $this->get_post();
        if (!empty($value)) {
            return sprintf($this->sql_condition_format, $this->name, $value);
        } else {
            return false;
        }
    }

    /**
     * Given an array of filter objects, builds a HTML table with the filters, ready for use by jQuery's sortable tables
     * @param array $filters
     * @param string $data_name This is the type of data being filtered (e.g. enquiries, projects, users etc.)
     * @return string HTML code
     */
    function get_filter_table($data_name='') {
        $ci = get_instance();
        $ci->load->helper('title');

        $title_options = array('title' => 'Filters',
                               'help' => 'Use this box to filter the list of '.$data_name,
                               'expand' => 'filters');

        $html = get_title($title_options);
        $html .= '<div id="filters"><table class="tbl"><tr>';

        foreach ($this->filters as $filter) {
            $html .= $filter->get_html();
        }

        $html .= '</tr></table></div>';

        return $html;
    }
}

/**
 * A single checkbox applying a boolean filter to the SQL query. This is a "Show" filter, meaning that it is active when the checkbox is unticked,
 * leading to a smaller result set. When you tick the box, the filter is removed.
 */
class filter_checkbox extends filter {
    var $value;
    var $field_name;
    var $sql_condition_format = "%s <> %s";

    /**
     * Constructor
     * @param string $value The value of the field being used as a filter. For example, if the field is "status", the value might be FIELD_APPROVED.
     * @param string $value_label If the value is an integer, a string representation needs to be given as a label for that value.
     * @see filter::filter() for doc of other fields
     * @param string $field_name The actual DB field name (the $name variable must be unique, so it doesn't usually match the DB field when several filters are in place for the same field)
     * @param boolean $default If set to true, will be checked and the
     */
    public function __construct($value, $value_label='', $name, $field_name, $default=false) {
        if (empty($value_label)) {
            $value_label = $value;
        }

        $this->field_name = $field_name;

        parent::initialise('', $name, $default);

        $this->value = $value;
        $this->title = "Show $value_label";

    }

    public function get_html() {
        if (empty($this->default)) {
            $this->default = 0;
        }

        return $this->get_open_html() .
            form_checkbox(array('name' => $this->name, 'id' => $this->id, 'value' => $this->value, 'checked' => $this->default)) .
            $this->get_close_html();
    }

    public function get_sql_condition() {
        $value = $this->get_post();
        if (empty($value)) {
            if ($this->value == 'NULL') {
                $this->sql_condition_format = '%s IS NULL';
            }
            return sprintf($this->sql_condition_format, $this->field_name, $this->value);
        } else {
            return false;
        }
    }
}

/**
 * A text field applying a LIKE filter to the SQL query
 */
class filter_text extends filter {
    var $sql_condition_format = "%s LIKE '%%%s%%'";
    var $constant_prefix = null;
    var $field_name;

    public function __construct($title, $jsname, $field_name=null, $default=null, $constant_prefix=null) {
        if (empty($field_name)) {
            $field_name = $jsname;
        }

        parent::initialise($title, $jsname, $default);
        $this->constant_prefix = $constant_prefix;
        $this->field_name = $field_name;
    }

    public function get_html() {
        return $this->get_open_html() .
            form_input(array('name' => $this->name, 'id' => $this->id, 'value' => $this->default)) .
            $this->get_close_html();
    }

    public function get_sql_condition() {
        $value = $this->get_post();
        if (!empty($this->constant_prefix) && !empty($value)) {
            $matching_values = search_constants_by_label($this->constant_prefix, $value);

            if (!empty($matching_values)) {
                $sql_condition = $this->field_name . ' IN (';

                foreach ($matching_values as $matching_value) {
                    $sql_condition .= "$matching_value,";
                }
                $sql_condition = substr($sql_condition, 0, -1) . ')';
                return $sql_condition;

            } else {
                return $this->field_name . " = 1 AND 1 = 0 "; // Impossible value to represent that the search returned no matches
            }
        }

        if (!empty($value)) {
            return sprintf($this->sql_condition_format, $this->field_name, $value);
        } else {
            return false;
        }
    }
}

/**
 * A select element applying an exact filter to the SQL query (can be multiple select)
 */
class filter_dropdown extends filter {
    var $options=array();

    public function filter_dropdown($options, $title, $name, $default) {
        parent::initialise($title, $name, $default);
        $this->options = $options;
    }

    public function get_html() {
        return $this->get_open_html() .
            form_dropdown($this->name, $this->options, $this->default, 'id="'.$this->id.'"') .
            $this->get_close_html();

    }
}

/**
 * A filter composed of 3 elements: a dropdown of available fields to filter by, an operator dropdown and a value text field.
 * This is the most versatile but the most complex of the filters
 */
class filter_combo extends filter {
    var $fields = array();
    var $operators = array('contains' => 'contains',
                           'is exactly' => 'is exactly',
                           'is not equal to' => 'is not equal to',
                           'does not contain' => 'does not contain',
                           'is greater than' => 'is greater than',
                           'is lower than' => 'is lower than'
                           );
    var $constant_prefixes = array();
    /**
     * Constructor
     * @param string $name
     * @param array $fields An associative array of db fields => labels.
     *                      The label can optionally be appended with a pipe character and a constant prefix to trigger the search of the constant label instead of its value
     */
    public function __construct($name, $fields) {
        parent::initialise('', $name);
        foreach ($fields as $dbfield => $label) {
            if (preg_match('/^([^\|].*)\|(.*)$/', $dbfield, $matches)) {
                $this->fields[$matches[1]] = $label;
                $this->constant_prefixes[$matches[1]] = $matches[2];
            } else {
                $this->fields[$dbfield] = $label;
            }
        }
    }

    public function get_post() {
        $ci = get_instance();
        $post = array();
        $post['search_field'] = $ci->input->post('search_field');
        $post['operator'] = $ci->input->post('operator');
        $post['search_value'] = $ci->input->post('search_value');
        return $post;
    }

    public function get_html() {
        $html = '<td>' . form_dropdown('search_field', $this->fields, reset($this->fields), 'id="search_field"');
        $html .= form_dropdown('operator', $this->operators, 'contains', 'id="operator"');
        $html .= form_input(array('name' => 'search_value', 'id' => 'combofilter'));
        return $html . '</td>';
    }

    public function get_sql_condition() {
        $post = $this->get_post();
        $wild = '';

        switch (trim($post['operator'])) {
            case 'contains' :
                $operator = ' LIKE ';
                $wild = '%';
                break;
            case 'does not contain' :
                $operator = ' NOT LIKE ';
                $wild = '%';
                break;
            case 'is exactly' :
                $operator = ' = ';
                break;
            case 'is greater than' :
                $operator = ' > ';
                break;
            case 'is lower than' :
                $operator = ' < ';
                break;
            case 'is not equal to' :
                $operator = ' <> ';
                break;
            default :
                $operator = ' LIKE ';
                $wild = '%';
                break;
        }

        if (empty($post['search_field']) || empty($post['operator']) || empty($post['search_value'])) {
            return false;
        }

        if (!empty($this->constant_prefixes[$post['search_field']]) && !empty($post['search_value'])) {
            $matching_values = search_constants_by_label($this->constant_prefixes[$post['search_field']], $post['search_value'], $post['operator']);

            if (!empty($matching_values)) {
                $sql_condition = $post['search_field'] . ' IN (';

                foreach ($matching_values as $matching_value) {
                    $sql_condition .= "$matching_value,";
                }
                $sql_condition = substr($sql_condition, 0, -1) . ')';
                return $sql_condition;

            } else {
                return $post['search_field'] . " = 1 AND 1 = 0 "; // Impossible value to represent that the search returned no matches
            }
        }

        // Handle date fields: recorded date will probably not fall on an exact day timestamp, so we must use a BETWEEN clause for "is exactly" and "contains" operators
        if (preg_match('/_date/', $post['search_field'])) {
            $ci = get_instance();
            $ci->load->helper('date');
            $post['search_value'] = human_to_unix($post['search_value']);
        }

        // If "Yes" or "No" is given, convert to boolean: these are probably not being used for name or description fields
        if (stristr($post['search_value'], 'yes') && strlen($post['search_value']) == 3) {
            $post['search_value'] = 1;
        } else if (stristr($post['search_value'], 'no') && strlen($post['search_value']) == 2) {
            $post['search_value'] = 0;
        }

        $sql_condition = "{$post['search_field']} $operator '$wild{$post['search_value']}$wild'";

        if (preg_match('/_date/', $post['search_field']) && ($post['operator'] == 'is exactly' || $post['operator'] == 'contains')) {
            $last_second = $post['search_value'] + 86399;
            $sql_condition = "{$post['search_field']} BETWEEN {$post['search_value']} AND $last_second";
        }

        return $sql_condition;
    }
}
