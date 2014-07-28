<?php
/**
 * Contains the User_Option model
 * @package models
 */

/**
 * User_Option_Model class
 * @package models
 */
class User_Option_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'user_options';


     /**
     * Returns all the option details for a given user. Specific option name can be requested, and defaults only can also be requested.
     * @todo Accept multiple names, currently accepts only none or one
     * @param int $user_id The id of the user
     * @param mixed $name Null if all options of that name are to be retrieved, otherwise integer or array of integers
     * @param bool $return_first_only If true, will only return the first record found
     * @param bool $string_only If true, will only return a string (or array of strings) containing the option details, instead of complete objects
     * @param boolean $defaults_only If true, will only return options that are set as default
     * @return array
     */
    public function get_by_user_id($user_id, $name=null, $return_first_only=true, $string_only=false, $defaults_only=false) {

        $params = array();
        $params['user_id'] = $user_id;

        if ($defaults_only) {
            $params['default_choice'] = 1;
        }

        if ($return_first_only) {
            $this->db->limit(1);
        }

        $this->db->order_by('default_choice DESC');

        if (!is_null($name)) {
            $params['name'] = $name;
        }

        $result = $this->get($params);

        if ($return_first_only) {
            if (empty($result)) {
                return null;
            }
            if ($string_only) {
                return $result[0]->value;
            } else {
                return $result[0];
            }
        } else {
            if (empty($result)) {
                return array();
            } else {
                if ($string_only) {
                    $options = array();
                    foreach ($result as $option) {
                        $options[$option->id] = $option->value;
                    }
                } else {
                    return $result;
                }
            }
        }
    }

    /**
     * In addition to deleting this option, if it is the default_choice, assign default_choice to another option of the same name if it exists
     * @param $option_id ID of the option detail
     * @return boolean true unless something goes majorly wrong in SQL
     */
    public function delete($option_id) {

        $option = $this->user_option_model->get($option_id);
        $result = parent::delete($option_id);

        if (!$result) {
            return false;
        }

        if ($option->default_choice) {
            $otherdefaultoption = $this->user_option_model->get(array('user_id' => $option->user_id,
                                                                       'default_choice' => 1,
                                                                       'name' => $option->name), true);

            // If at least one other option is already default, do nothing else
            if ($otherdefaultoption) {
                return true;
            }

            $othernondefaultoption = $this->user_option_model->get(array('user_id' => $option->user_id,
                                                                          'default_choice' => 0,
                                                                          'name' => $option->name), true);
            if (!empty($othernondefaultoption)) {
                $this->user_option_model->edit($othernondefaultoption->id, array('default_choice' => 1));
            }
        }

        return true;
    }

    /**
     * Sets the default_choice for this option detail to 1, and all other options of this name and for this user to 0
     *
     * @param int $option_id The id of the option detail
     * @param boolean $soft If true, will only set as default if no other option of that name is already set as default
     * @return boolean True if set as default, false otherwise
     */
    public function set_as_default($option_id, $soft=false) {

        $option = $this->user_option_model->get($option_id);

        if (!$option) {
            return false;
        }

        $this->user_option_model->edit($option_id, array('default_choice' => 1));

        $otheroptions = $this->user_option_model->get(array('user_id' => $option->user_id, 'name' => $option->name, 'default_choice' => 1));

        // If soft option true and at least one other option is default, cancel action
        if ($soft) {
            foreach ($otheroptions as $otheroption) {
                if ($otheroption->id != $option->id) {
                    $this->user_option_model->edit($option_id, array('default_choice' => 0));
                    return false;
                }
            }
        }

        foreach ($otheroptions as $otheroption) {
            if ($otheroption->id != $option->id) {
                $this->user_option_model->edit($otheroption->id, array('default_choice' => 0));
            }
        }

        return true;
    }

    /**
     * For a given user, creates or updates the given options.
     * @param int $user_id
     * @param array $options
     * @return bool
     */
    public function update_options($user_id, $options) {

        $query = $this->db->where_in('name', array_keys($options))->where('user_id', $user_id)->get('user_options');
        $existing_options = array();

        if ($query->num_rows > 0) {
            foreach ($query->result() as $row) {
                if (empty($options[$row->name])) {
                    $this->delete($row->id);
                } else {
                    $this->edit($row->id, array('value' => $options[$row->name]));
                }
                unset($options[$row->name]);
            }
        }

        // Any options left over are new
        foreach ($options as $option => $value) {
            $this->add(array('user_id' => $user_id, 'name' => $option, 'value' => $value));
        }

        return true;
    }
}
