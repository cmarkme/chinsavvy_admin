<?php
/**
 * Contains the Revision_Model Model class
 * @package models
 */

/**
 * Revision Model class
 * @package models
 */
class Revision_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'qc_revisions';

    /**
     * Compares the json-encoded specification data for a given type, between this revision's data
     * and the data of another. Returns true if different, false if identical.
     * @param int $revision_id
     * @param int $type QC_SPEC_CATEGORY_TYPE_PRODUCT or QC_SPEC_CATEGORY_TYPE_QC
     * @param string $revisiondata json-encoded data of the other revision to check
     * @return bool
     */
    public function has_spec_data_changed($revision_id, $type, $revisiondata) {

        $revision = $this->get($revision_id);

        $mydata = json_decode($revision->data);
        $otherdata = json_decode($revisiondata);
        $result = 0;

        if ($type == QC_SPEC_CATEGORY_TYPE_PRODUCT) {
            $result = strcasecmp(json_encode($mydata->productspecs), json_encode($otherdata->productspecs));

            if (empty($mydata->parts)) {
                $mydata->parts = '';
            }

            if (empty($otherdata->parts)) {
                $otherdata->parts = '';
            }

            if (empty($mydata->files)) {
                $mydata->files = '';
            }

            if (empty($otherdata->files)) {
                $otherdata->files = '';
            }
            $result += strcasecmp(json_encode($mydata->parts), json_encode($otherdata->parts));
            $result += strcasecmp(json_encode($mydata->files), json_encode($otherdata->files));
        } else {
            $result = strcasecmp(json_encode($mydata->qcspecs), json_encode($otherdata->qcspecs));
        }

        return $result != 0;
    }

    /**
     * Given a JSON string of spec data, formats it as if it were the return value of project->getSpecs();
     * @param int $revision_id
     * @param string $type 'product' or 'qc'
     * @param array $data. Optional array of data. If not set, will use this object's $data attribute
     * @param array $categories Array of specifications to print. 0 == files, other integers are qc_spec_categories.id
     * @return array $specs
     */
    public function decode($revision_id, $type=QC_SPEC_CATEGORY_TYPE_PRODUCT, $data=array(), $categories=array()) {

        $this->load->model('qc/speccategory_model');

        $revision = $this->get($revision_id);

        if (empty($data)) {
            $data = $revision->data;
        }

        $revisiondata = json_decode($data);

        if (empty($revisiondata->details)) {
            add_message('This revision number does not exist for this project!', 'error');
            return false;
        }

        $specs = array();
        $spec_times = array();
        $parts = (array) $revisiondata->parts;
        $specs = array();
        $finalspecs = array();

        // It's possible that a whole bunch of specs are created exactly at the same time (through duplication)
        // If this happens, use spec id to further order them
        $type_string = ($type == QC_SPEC_CATEGORY_TYPE_QC) ? 'qc' : 'product';
        foreach ($revisiondata->{$type_string.'specs'} as $spec) {
            $spec_times[$spec->id] = $spec->creation_date . $spec->id;
            $specs[$spec->id] = $spec;
        }

        // Sort spec times to decide the order of spec categories
        asort($spec_times);

        $sorted_specs = array();
        foreach ($spec_times as $spec_id => $spec_time) {
            $sorted_specs[$spec_id] = $specs[$spec_id];
        }
        $specs = $sorted_specs;

        $category_ranks = array();

        foreach ($specs as $spec) {
            $category = $this->speccategory_model->get($spec->category_id);

            if (!in_array($category->name, $category_ranks)) {
                $category_ranks[] = $category->name;
            }

            if (empty($categories) || in_array($spec->category_id, $categories)) {
                if (empty($spec->english_id)) {
                    if (!empty($finalspecs[$category->name][$spec->id])) {
                        $finalspecs[$category->name][$spec->id][$spec->language] = (array) $spec;
                    } else {
                        $finalspecs[$category->name][$spec->id] = array($spec->language => (array) $spec);
                    }
                } else {
                    $finalspecs[$category->name][$spec->english_id][$spec->language] = (array) $spec;
                }
            }
        }

        $sorted_specs = array();

        foreach ($category_ranks as $categoryname) {
            $sorted_specs[$categoryname] = $finalspecs[$categoryname];
        }

        // Remove any chinese specs with no matching english spec
        foreach ($sorted_specs as $categoryname => $specarray) {
            foreach ($specarray as $specid => $langarray) {
                if (empty($langarray[QC_SPEC_LANGUAGE_EN]) && !empty($langarray[QC_SPEC_LANGUAGE_CH])) {
                    unset($sorted_specs[$categoryname][$specid]);
                }
            }
        }

        return array('specs' => $sorted_specs,
                     'details' => (array) $revisiondata->details,
                     'parts' => $revisiondata->parts,
                     'files' => $revisiondata->files,
                     'jobs' => $revisiondata->jobs,
                     'results' => $revisiondata->results);
    }
}
