<?php
/**
 * Contains the Currencyrate Model class
 * @package models
 */

/**
 * Currencyrate Model class
 * @package models
 */
class Currencyrate_Model extends MY_Model {
    /**
     * @access public
     * @var string The DB table used by this model
     */
    var $table = 'exchange_currency_dailyrates';

    /**
     * Given a UNIX timestamp and an array of exchangerate records, fetches the record with the closest previous
     * timestamp (first second of each day).
     */
    public function find_matching_rate($timestamp, $currency_id, $exchangerates=array()) {
        // Get the year, month and day of the timestamp
        $date = date('d-m-Y', $timestamp);
        $date_parts = explode('-', $date);

        // Get the timestamp for second 1 of this day
        $first_second = mktime(0,0,0,$date_parts[1], $date_parts[0], $date_parts[2]);

        // Get timestamp for last second of this day
        $last_second = mktime(23,59,59,$date_parts[1], $date_parts[0], $date_parts[2]);

        foreach ($exchangerates as $er) {
            if ($er->currency_id == $currency_id && $er->creation_date < $last_second) {
                return $er;
            }
        }

        $exchangerates_reversed = array_reverse($exchangerates);
        foreach ($exchangerates_reversed as $er) {
            if ($er->currency_id == $currency_id && $er->creation_date >= $first_second) {
                return $er;
            }
        }

        return false;
    }
}

