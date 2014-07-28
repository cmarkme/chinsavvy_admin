<?php
/**
 * Contains the Enquiry_Note Model class
 * @package models
 */

/**
 * Enquiry_Note Model class
 * @package models
 */
class Enquiry_Note_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'enquiries_enquiry_notes';

    /**
     * Returns the name of the note's author.
     * @param int $note_id If given, ignores $note
     * @param object $note If given, ignores $note_id
     * @return string
     */
    public function get_author($note_id=null, $note=null) {
        $author = 'Anonymous';
        if (empty($note)) {
            $note = $this->get($note_id);
        }

        if (!empty($note->user_id)) {
            $this->load->model('users/user_model');

            if (!isset($note->user)) {
                $user = $this->user_model->get($note->user_id);
            } else {
                $user = $note->user;
            }

            if (empty($user)) {
                $author = 'Anonymous';
            } else {
                $author = $this->user_model->get_name($user);
            }

        } elseif ($note->type == 'system') {
            $author = 'System';
        }

        return $author;
    }
}
