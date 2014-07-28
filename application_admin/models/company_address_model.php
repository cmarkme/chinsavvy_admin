<?php
/**
 * Contains the Company_Address Model class
 * @package models
 */

/**
 * Company_Address Model class
 * @package models
 */
class Company_Address_Model extends MY_Model {
    /**
     * @var string The DB table used by this model
     */
    public $table = 'company_addresses';


    /**
     * Sets the default_address for this address to 1, and all other addresses for this company to 0
     *
     * @param int $address_id The id of the address
     * @param boolean $soft If true, will only set as default if no other address is already set as default
     * @return boolean True if set as default, false otherwise
     */
    public function set_as_default($address_id, $soft=false) {

        $address = $this->company_address_model->get($address_id);

        if (!$address) {
            return false;
        }

        $this->company_address_model->edit($address_id, array('default_address' => 1));

        $otheraddresses = $this->company_address_model->get(array('company_id' => $address->company_id, 'default_address' => 1));

        // If soft option true and at least one other address is default, cancel action
        if ($soft) {
            foreach ($otheraddresses as $otheraddress) {
                if ($otheraddress->id != $address->id) {
                    $this->company_address_model->edit($address_id, array('default_address' => 0));
                    return false;
                }
            }
        }

        foreach ($otheraddresses as $otheraddress) {
            if ($otheraddress->id != $address->id) {
                $this->company_address_model->edit($otheraddress->id, array('default_address' => 0));
            }
        }

        return true;
    }

     /**
     * Given an array of form data, checks if addresses already exist for the given company
     * If the address already exists and is unchanged, take no action.
     * If the address doesn't exist, insert it.
     * @param array $data
     * @param int $company_id The company to which these addresses must be linked
     * @return string Error message or TRUE if all went well.
     */
    function update_from_formdata($data, $company_id) {
        $errors = false;

        if (empty($data['address_country_id']) && !empty($data['company_country_id'])) {
            $data['address_country_id'] = $data['company_country_id'];
        }

        // Do we have 'address_%s' keys in the $data array?
        if (count(preg_grep('/^address_/', array_keys($data))) > 0) { // There is some address data
            $address_data = array('address1' => $data['address_address1'],
                                  'address2' => $data['address_address2'],
                                  'city' => $data['address_city'],
                                  'state' => $data['address_state'],
                                  'postcode' => $data['address_postcode'],
                                  'country_id' => $data['address_country_id'],
                                  'default_address' => 1
                                  );

            if ($address = $this->get(array('company_id' => $company_id), true)) {
                $result = $this->edit($address->id, $address_data);
            } else {
                $address_data['company_id'] = $company_id;
                $result = $this->add($address_data);
            }

            if (!$result) {
                add_message('Error saving the company address!', 'error');
                return false;
            }
        }

        // Do we have 'address_ch_%s' keys in the $data array?
        if (count(preg_grep('/^address_ch_/', array_keys($data))) > 0) { // There is some Chinese address data
            $address_data = array('address1' => $data['address_ch_address1_ch'],
                                  'address2' => $data['address_ch_address2_ch'],
                                  'city' => $data['address_ch_city_ch'],
                                  'state' => $data['address_state'],
                                  'postcode' => $data['address_postcode'],
                                  'country_id' => $data['address_country_id']
                                  );
            if ($address = $this->get(array('company_id' => $company_id, 'type' => COMPANY_ADDRESS_TYPE_CH), true)) {
                $result = $this->edit($address->id, $address_data);
            } else {
                $address_data['company_id'] = $company_id;
                $result = $this->add($address_data);
            }

            if (!$result) {
                add_message('Error saving the company address!', 'error');
                return false;
            }
        }

        return true;
    }
}

