<?php
function nl2br_revert($string) {
    return str_replace(
        array("<br>\n",   "<br>\r",   "<br />\n", "<br />\r"),
        array("<br />\n", "<br />\r",       "\n",       "\r"),
        $string
    );
}

/**
 * Given a comma-separate list of email addresses (with option name <address> syntax),
 * parses it and returns an array of arrays (array('name' => $name, 'address' => $address))
 */
function process_email_list($string) {
    $addresses = explode(',', $string);
    $return_addresses = array();

    foreach ($addresses as $address) {
        $address_array = array();
        if (preg_match('/(.*)\w?<([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})>/i', $address, $matches)) {
            $address_array['name'] = trim($matches[1]);
            $address_array['address'] = trim($matches[2]);
        } else {
            $address_array['name'] = null;
            $address_array['address'] = trim($address);
        }

        if (!empty($address_array['address'])) {
            $return_addresses[] = $address_array;
        }
    }

    return $return_addresses;
}
?>
