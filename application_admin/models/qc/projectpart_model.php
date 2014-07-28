<?php
/**
 * Contains the Projectpart_Model Model class
 * @package models
 */

/**
 * Projectpart Model class
 * @package models
 */
class Projectpart_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'qc_project_parts';

     /**
     * In addition to normal insert, create a QC spec unless otherwise specified
     */
    public function add($params, $no_spec=false) {
        if (empty($params['project_id'])) {
            add_message('A project ID is required in order to add a QC project part record!', 'error');
            return false;
        }


        $part_id = parent::add($params);

        if (!$no_spec) {
            return $part_id;
        }

        $category_params = array('name' => 'Dimensions', 'type' => QC_SPEC_CATEGORY_TYPE_QC);
        if ($category = $this->speccategory_model->get($category_params, true)) {
            $part_category_id = $category->id;
        } else {
            $part_category_id = $this->speccategory_model->add($category_params);
        }

        $spec_params = array('type' => QC_SPEC_TYPE_NORMAL,
                             'language' => QC_SPEC_LANGUAGE_EN,
                             'datatype' => QC_SPEC_DATATYPE_STRING,
                             'part_id' => $part_id,
                             'project_id' => $params['project_id'],
                             'category_id' => $part_category_id,
                             'data' => $this->get_dimensions_datastring($part_id)
                             );

        if (!($spec = $this->spec_model->get($spec_params, true))) {
            $this->spec_model->add($spec_params);
            $this->project_model->flag_as_changed($params['project_id']);
        }
    }

    public function get_dimensions_datastring($part_id) {

        $part = $this->get($part_id);
        $data = $part->name . ': ';
        $data .= (!empty($part->length)) ? "length: $part->length mm, " : '';
        $data .= (!empty($part->width)) ? "width: $part->width mm, " : '';
        $data .= (!empty($part->height)) ? "height: $part->height mm, " : '';
        $data .= (!empty($part->diameter)) ? "diameter: $part->diameter mm, " : '';
        $data .= (!empty($part->thickness)) ? "thickness: $part->thickness mm, " : '';
        $data .= (!empty($part->weight)) ? "weight: $part->weight g, " : '';
        $data .= (!empty($part->other)) ? "$part->other." : '.';
        return $data;
    }

    /**
     * In addition to normal update, update the linked QC spec if it exists, or create it
     */
    public function edit($part_id, $params) {

        $part = $this->get($part_id);

        $spec_params = array('type' => QC_SPEC_TYPE_NORMAL,
                             'language' => QC_SPEC_LANGUAGE_EN,
                             'datatype' => QC_SPEC_DATATYPE_STRING,
                             'part_id' => $part_id,
                             'project_id' => $part->project_id
                             );

        if ($spec = $this->spec_model->get($spec_params, true)) {
            $this->spec_model->edit($spec->id, array('data' => $this->get_dimensions_datastring($part_id)));
            $this->project_model->flag_as_changed($part->project_id);
        } else {
            $spec_params['data'] = $this->get_dimensions_datastring($part_id);
            $this->spec_model->add($spec_params);
            $this->project_model->flag_as_changed($part->project_id);
        }
        return parent::edit($part_id, $params);
    }

    /**
     * In addition to normal delete, update the linked QC spec if it exists
     */
    public function delete($part_id) {

        $part = $this->get($part_id);

        $spec_params = array('type' => QC_SPEC_TYPE_NORMAL,
                             'language' => QC_SPEC_LANGUAGE_EN,
                             'datatype' => QC_SPEC_DATATYPE_STRING,
                             'part_id' => $part_id,
                             'project_id' => $part->project_id
                             );

        if ($spec = $this->spec_model->get($spec_params, true)) {
            $this->spec_model->delete($spec->id);
            $this->project_model->flag_as_changed($part->project_id);
        }
        return parent::delete($part_id);
    }
}
