<?php
function print_form_container_open() {
    echo '<table class="tbl">';
}

function print_static_form_element($label, $html) {
    $ci = get_instance();
    echo '<tr>
    <th class="formlabel">'.$label.'</th>
    <td class="formelement">'.$html.'</td>
</tr>
';
}

function print_dropdown_element($name, $label, $options, $required=false, $extra_html=null) {
    $element = new form_element_dropdown($name, $label, $options, $required, $extra_html);
    echo $element->get_html();
}

function print_multiselect_element($name, $label, $options, $required=false, $extra_html=null) {
    $element = new form_element_multiselect($name, $label, $options, $required, $extra_html);
    echo $element->get_html();
}

function print_input_element($label, $params, $required=false, $extra_html=null) {
    $element = new form_element_input($label, $params, $required, $extra_html);
    echo $element->get_html();
}

function print_textarea_element($label, $params, $required=false, $extra_html=null) {
    $element = new form_element_textarea($label, $params, $required, $extra_html);
    echo $element->get_html();
}

function print_date_element($label, $params, $required=false) {
    if (empty($params['class'])) {
        $params['class'] = 'date_input';
    } else {
        $params['class'] .= ' date_input';
    }

    $element = new form_element_input($label, $params, $required);
    echo $element->get_html();
}

function print_password_element($label, $params, $required=false, $reveal_checkbox=false) {
    $element = new form_element_password($label, $params, $required, $reveal_checkbox);
    echo $element->get_html();
}

function print_hidden_element($name) {
    $element = new form_element_hidden($name);
    echo $element->get_html();
}

function print_file_element($name, $label, $required=false) {
    $element = new form_element_file($name, $label, $required);
    echo $element->get_html();
}

function print_checkbox_element($label, $params, $required=false) {
    if (!is_array($params) && is_string($params)) {
        $params = array('name' => $params, 'value' => 1);
    }
    $element = new form_element_checkbox($label, $params, $required);
    echo $element->get_html();
}

function print_radio_element($label, $params, $required=false) {
    $element = new form_element_radio($label, $params, $required);
    echo $element->get_html();
}

abstract class form_element {
    public $name;
    public $label;
    public $required=false;
    public $error=false;
    public $default_value=null;
    public $html;
    public static $default_data=array();

    public function __construct($name, $label, $required=false) {
        $this->name = $name;
        $this->label = $label;
        $this->required = $required;

        if (validation_errors() !== '') {
            $this->default_value = set_value($this->name);
        } elseif (array_key_exists($this->name, form_element::$default_data)) {
            $this->default_value = form_element::$default_data[$this->name];
        }
        $this->error = form_error($name);
    }

    public static function set_default_data($data)
    {
        static::$default_data = (array) $data;
    }

    public function get_html() {
        $asterisk = ($this->required) ? '<span class="required">*</span>' : '';
        $required_class = ($this->required) ? ' required ' : '';
        $error_class = ($this->error) ? ' error ' : '';
        return '
            <tr class="'.$error_class.'">
                <th class="formlabel">'.$this->label.' '.$asterisk.'</th>
                <td class="formelement'.$required_class.'">'.$this->html.'</td>
            </tr>
            ';
    }

    /**
     * Given a string or an associative array of HTML params, returns a string of HTML params
     * @param mixed $extra_html
     * @return string
     */
    public function process_extra_html($extra_html, $required) {
        $output = $required ? 'required ' : '';
        if (is_array($extra_html)) {
            if ($required) {
                if (array_key_exists('class', $extra_html)) {
                    $extra_html['class'] .= ' required';
                } else {
                    $extra_html['class'] = 'required';
                }
            }
            foreach ($extra_html as $param => $value) {
                $output .= "$param=\"$value\" ";
            }
        } else {
            $output .= $extra_html;
        }

        return $output;
    }
}

class form_element_dropdown extends form_element {
    public function __construct($name, $label, $options, $required=false, $extra_html=null) {
        parent::__construct($name, $label, $required);
        $this->html = form_dropdown($name, $options, $this->default_value, $this->process_extra_html($extra_html, $required));
    }
}

class form_element_multiselect extends form_element {
    public function __construct($name, $label, $options, $required=false, $extra_html=null) {
        parent::__construct($name, $label, $required);
        $this->html = form_multiselect($name, $options, $this->default_value, $this->process_extra_html($extra_html, $required));
    }
}

class form_element_input extends form_element {
    public function __construct($label, $params, $required=false, $extra_html = null) {
        parent::__construct($params['name'], $label, $required);
        $params['value'] = isset($extra_html['value']) ? $extra_html['value'] : $this->default_value;
        $this->html = form_input($params, '', $this->process_extra_html($extra_html, $required));
    }
}

class form_element_textarea extends form_element {
    public function __construct($label, $params, $required=false, $extra_html = null) {
        parent::__construct($params['name'], $label, $required);
        $params['value'] = isset($extra_html['value']) ? $extra_html['value'] : $this->default_value;
        $this->html = form_textarea($params, $params['value'], $this->process_extra_html($extra_html, $required));
    }
}

class form_element_password extends form_element {
    public function __construct($label, $params, $required=false, $reveal_checkbox=false) {
        parent::__construct($params['name'], $label, $required);
        $params['value'] = $this->default_value;
        $this->html = form_password($params);
        if ($reveal_checkbox) {
            $this->html .= form_checkbox(array('name' => 'reveal', 'onclick' => "reveal_password('{$params['name']}', this);")) . form_label('Reveal', 'reveal');
        }
    }
}

class form_element_hidden extends form_element {
    public function __construct($name) {
        parent::__construct($name, null);
    }

    public function get_html() {
        return form_hidden($this->name, $this->default_value);
    }
}

class form_element_file extends form_element {
    public function __construct($name, $label, $required=false) {
        parent::__construct($name, $label, array(), $required);
        $this->html = form_upload($name);
    }
}

class form_element_checkbox extends form_element {
    public function __construct($label, $params, $required=false) {
        parent::__construct($params['name'], $label, $required);
        if ($this->default_value) {
            $params['checked'] = true;
        }
        $this->html = form_checkbox($params);
    }
}

class form_element_radio extends form_element {
    public function __construct($label, $params, $required=false) {
        parent::__construct($params['name'], $label, $required);
        if ($this->default_value) {
            $params['checked'] = true;
        }
        $this->html = form_radio($params);
    }
}

function print_form_container_close($colspan=2) {
    echo '<tr><td colspan="'.$colspan.'"><span class="required">*</span> denotes required field</td></tr></table>';
}

function print_form_section_heading($heading, $columns=2) {
    echo '<tr><td class="subtitle" colspan="'.$columns.'">'.$heading.'</td></tr>';
}

function print_submit_container_open() {
    echo '<tr><td colspan="2" class="submit">';
}

function print_submit_container_close() {
    echo '</td></tr>';
}
?>
