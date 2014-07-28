<?php
/**
 * Contains the Enquiry_Staff Model class
 * @package models
 */

/**
 * Enquiry_Staff Model class
 * @package models
 */
class Enquiry_Staff_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'enquiries_enquiry_staff';


    /**
     * Returns whether or not the given user is assigned to the given enquiry
     * @param int $user_id
     * @param in $enquiry_id
     * @return bool
     */
    public function is_assigned($user_id, $enquiry_id) {

        $result = $this->get(array('user_id' => $user_id, 'enquiry_id' => $enquiry_id));
        return !empty($result);
    }
}
