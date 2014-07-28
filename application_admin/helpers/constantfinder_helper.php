<?php
/**
 * Given a constant prefix and a value, this function returns the matching constant name if it exists and has been defined
 * @param string $prefix Upper-case, underscore-separated format
 * @param int $value Value assigned to the constant
 * @return string
 */
function find_constant($prefix, $value) {
    $allconstants = get_defined_constants(true);
    $myconstants = $allconstants['user'];
    foreach ($myconstants as $constant => $thevalue) {
        if (preg_match('/^'.$prefix.'.*$/', $constant) && $thevalue == $value) {
            return $constant;
        }
    }
    return false;
}

/**
 * Given a constant prefix and a search string, returns an array of all the constant values that match the search string
 * @param string $prefix
 * @param string $search_text
 * @return array
 */
function search_constants_by_label($prefix, $search_text, $operator='contains') {
    $allconstants = get_defined_constants(true);
    $myconstants = $allconstants['user'];
    $ci = get_instance();

    $matching_values = array();

    foreach ($myconstants as $constant => $value) {
        if (preg_match('/^'.$prefix.'.*$/', $constant)) {
            switch ($operator) {
                case 'contains' :
                    if (stristr($ci->lang->line($constant), $search_text)) {
                        $matching_values[] = $value;
                    }
                    break;
                case 'is exactly':
                    if ($ci->lang->line($constant) == $search_text) {
                        $matching_values[] = $value;
                    }
                    break;
                case 'is not equal to':
                    if ($ci->lang->line($constant) != $search_text) {
                        $matching_values[] = $value;
                    }
                    break;
                case 'does not contain':
                    if (!stristr($ci->lang->line($constant), $search_text)) {
                        $matching_values[] = $value;
                    }
                    break;
                default: // We don't handle < and > with strings, it's meaningless
                    break;
            }
        }
    }
    return $matching_values;
}

function get_lang_for_constant_value($constant_prefix, $value) {
    static $lang_cache = array();
    $ci = get_instance();
    if (empty($lang_cache[$constant_prefix])) {
        $lang_cache[$constant_prefix] = array();
    }

    if (empty($lang_cache[$constant_prefix][$value])) {
        $lang_cache[$constant_prefix][$value] = $ci->lang->line(find_constant($constant_prefix, $value));
    }

    return $lang_cache[$constant_prefix][$value];
}

/**
 * Given a constant prefix, finds all its values and labels and creates an array ready for use as a dropdown menu
 * @param string $prefix
 * @param bool $null_option If true, will add a "-- Select One --" null option at the top
 * @param bool $optgroups If true, will look for the ~ separator in the option values and use that to build a multi-dimensional array
 * @return array
 */
function get_constant_dropdown($prefix, $null_option=true, $optgroups=false) {
    $ci = get_instance();
    $allconstants = get_defined_constants(true);
    $myconstants = $allconstants['user'];
    $options = array();

    if ($null_option) {
        $options[null] = '-- Select One --';
    }

    foreach ($myconstants as $constant => $thevalue) {
        if (preg_match('/^'.$prefix.'.*$/', $constant)) {
            $value = $ci->lang->line($constant);
            if ($optgroups && preg_match('/~/', $value)) {
                $parts = explode('~', $value);
                $options[$parts[0]][$thevalue] = $parts[1];
            } else {
                $options[$thevalue] = $value;
            }
        }
    }

    return $options;
}
