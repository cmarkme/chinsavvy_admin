<?php
/**
 * Contains the Procedureitem_Model Model class
 * @package models
 */

/**
 * Procedureitem Model class
 * @package models
 */
class Procedureitem_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    public $table = 'qc_procedure_items';

    /**
     * In addition to normal delete, update the QC projects that use the linked Procedure
     */
    public function delete($procedureitem_id) {

        $this->load->model('qc/procedure_model');
        $procedureitem = $this->get($procedureitem_id);

        if (parent::delete($procedureitem_id)) {
            $projects_affected = $this->procedure_model->notify_projects($procedureitem->procedure_id);
            // Could use add_message here to notify of how many projects were flagged
            return true;
        } else {
            return false;
        }
    }

    /**
     * In addition to normal insert, flagged associated QC projects
     */
    public function add($params) {

        $this->load->model('qc/procedure_model');

        if ($id = parent::add($params)) {
            $projects_affected = $this->procedure_model->notify_projects($params['procedure_id']);
            // Could use add_message here to notify of how many projects were flagged
            return $id;
        } else {
            return false;
        }
    }
}
