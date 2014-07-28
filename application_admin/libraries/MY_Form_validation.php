<?php
/**
 * File containing the MY_Form_validation class
 * @package models
 */

/**
 * MY_Form_validation class
 */
class MY_Form_validation extends CI_Form_validation {
    public function override_field_data($key, $value) {
        $this->_field_data[$key]['postdata'] = $value;
    }
}
?>
