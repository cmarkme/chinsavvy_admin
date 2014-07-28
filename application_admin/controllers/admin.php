<?php

class Admin extends CI_Controller {
    function print_message() {

        $message = $this->input->post('message');
        $type = $this->input->post('type');
        echo '<div class="message"><span class="'.$type.'">'.$message.'</span></div>';
    }

    function get_lang_for_constant_value($constant_prefix, $value) {
        echo get_lang_for_constant_value($constant_prefix, $value);
    }

    function get_lang_line($index, $language='english') {

        echo $this->lang->line($index);
    }
}
