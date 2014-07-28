<?php
/**
 * Given an array of language string indices, retrieves both the English and Chinese version of these strings and arranges them in an array ready to use in PDF documents for either single- or combined-language use.
 */
function prepare_lang_strings($strings=array()) {
    $ci = get_instance();
    $lang_strings = array();

    $english_strings = $ci->lang->load('qc', 'english', true);
    $chinese_strings = $ci->lang->load('qc', 'ch', true);

    foreach ($strings as $string) {
        $lang_strings[$string] = array(QC_SPEC_LANGUAGE_EN => $english_strings[$string],
                                       QC_SPEC_LANGUAGE_CH => '<font face="chinese">' . $chinese_strings[$string].'</font>',
                                       QC_SPEC_LANGUAGE_COMBINED => $english_strings[$string] .
            '<font face="chinese">(' . $chinese_strings[$string].')</font>');
    }

    return $lang_strings;
}
