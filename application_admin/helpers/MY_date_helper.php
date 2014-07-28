<?php

/**
 * Replaces the human_to_unix function of the core Date helper, using the format dd/mm/yyyy instead of yyyy-mm-dd
 * @param string $humandate
 * @return int Unix timestamp
 */
function human_to_unix($humandate) {
    if (empty($humandate)) {
        return null;
    }

    $humandate = str_replace(' ', '', $humandate);
    $humandate = str_replace('-', '', $humandate);
    $humandate = str_replace(':', '', $humandate);
    $humandate = str_replace('/', '', $humandate);
    $year  = substr($humandate, '4', '4');
    $month = substr($humandate, '2', '2');
    $day   = substr($humandate, '0', '2');

    $timestamp = mktime(0,0,0, $month, $day, $year);
    // If an invalid format was passed, return the current time
    if ($timestamp == -1) {
        return mktime();
    } else {
        return $timestamp;
    }
}

/**
 * Shortcut to formatting a UNIX timestamp to dd/mm/YYYY format, useful for jquery ui calendar widgets
 */
function unix_to_human($timestamp, $format='%d/%m/%Y') {
    if (empty($timestamp)) {
        return null;
    }
    return mdate($format, $timestamp, false, 'eu');
}
