<?php
/**
 * @package helpers
 */
function encode_passwords() {
    $ci = get_instance();

    $ci->load->library('encrypt');
    $query = $ci->db->get('users');

    foreach ($query->result() as $row) {
        $ci2 = get_instance();
        $ci2->db->where('id', $row->id);
        $ci2->db->update('users', array('password' => $ci->encrypt->encode($row->password)));
    }
}
