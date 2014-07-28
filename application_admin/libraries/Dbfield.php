<?php
/**
 * Contains the Dbfield library
 * @package libraries
 */

/**
 * Dbfield class
 * @package libraries
 */
class Dbfield {
    public $dbselect;
    public $alias;
    public $label;

    public function get_field($dbselect, $alias, $label) {
        $this->dbselect = $dbselect;
        $this->alias = $alias;
        $this->label = $label;
        return clone($this);
    }
}
