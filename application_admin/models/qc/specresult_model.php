<?php
/**
 * Contains the Specresult_Model Model class
 * @package models
 */

/**
 * Specresult Model class
 * @package models
 */
class Specresult_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'qc_specs_results';

    public function get_permitted_defects($project_details, $spec)
    {
    	switch($spec->importance) {
            case QC_SPEC_IMPORTANCE_CRITICAL:
                return floor($project_details['samplesize'] * ($project_details['defectcriticallimit'] / 100));
            case QC_SPEC_IMPORTANCE_MAJOR:
                return floor($project_details['samplesize'] * ($project_details['defectmajorlimit'] / 100));
            case QC_SPEC_IMPORTANCE_MINOR:
                return floor($project_details['samplesize'] * ($project_details['defectminorlimit'] / 100));
        }
    }

}
