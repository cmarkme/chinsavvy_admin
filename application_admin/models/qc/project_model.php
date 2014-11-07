<?php
/**
 * Contains the Project_Model Model class
 * @package models
 */

/**
 * Project Model class
 * @package models
 */
class Project_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    public $table = 'qc_projects';

    function get_data_for_listing($params=array(), $filters=array(), $limit=null, $for_document_table=false) {

        $this->load->helper('date');

        $this->dbfields = array($this->dbfield->get_field('qc_projects.id', 'project_id', 'id'),
                                $this->dbfield->get_field('CONCAT(codes_divisions.code,DATE_FORMAT(FROM_UNIXTIME(codes_projects.creation_date), "%y"),
                                         ".", codes_projects.number,
                                         ".", codes_parts.number,
                                         ".", companies.code)', 'productcode', 'Product code'),
                                $this->dbfield->get_field('codes_parts.name', 'productname', 'Product name'));

        if (!$for_document_table) {
            $this->dbfields[] = $this->dbfield->get_field('qc_projects.result', 'projectstatus', 'Status');
            $this->dbfields[] = $this->dbfield->get_field('qc_projects.revision_string', 'revision', 'Revision');
            $this->dbfields[] = $this->dbfield->get_field('qc_projects.approved_project_admin', 'approved_project_admin', 'Project approved');
            $this->dbfields[] = $this->dbfield->get_field('qc_projects.approved_product_admin', 'approved_product_admin', 'Product specs approved');
            $this->dbfields[] = $this->dbfield->get_field('qc_projects.approved_qc_admin', 'approved_qc_admin', 'QC specs approved');

        }
        $this->dbfields[] = $this->dbfield->get_field('qc_projects.revision_date', 'project_revision_date', 'Last update');
        $this->dbfields[] = $this->dbfield->get_field('qc_projects.creation_date', 'project_creation_date', 'Creation date');

        // Unique Linked fields
        $this->db->join('codes_parts', 'qc_projects.part_id = codes_parts.id');
        $this->db->join('codes_projects', 'codes_projects.id = codes_parts.project_id');
        $this->db->join('codes_divisions', 'codes_divisions.id = codes_projects.division_id');
        $this->db->join('companies', 'companies.id = codes_projects.company_id');

        // Add users.id for filtering
        parent::apply_db_selects();

        // $this->db->group_by('enquiries_outbound_quotations.id');

        $numrows = parent::filter($params, $filters);

        if ($limit) {
            $this->db->limit($limit);
        }

        // For table headings
        $table_data = array('headings' => parent::get_table_headings(),
                            'rows' => array(),
                            'numrows' => $numrows);

        $query = parent::get_with_aliased_columns();

        foreach ($query->result() as $row) {
            $table_data['rows'][] = parent::get_table_row_from_db_record($row);
        }

        return $table_data;
    }

    public function update_revision_string($project_id) {

        $this->load->model('qc/specrevision_model');

        $projectrevisionnumber = 0;
        $productrevisionnumber = 0;
        $qcrevisionnumber = 0;

        if ($lastrevision = $this->get_last_revision($project_id)) {
            $projectrevisionnumber = $lastrevision->number;
            $productrevision = $this->specrevision_model->get(array('type' => QC_SPEC_CATEGORY_TYPE_PRODUCT, 'revision_id' => $lastrevision->id), true);
            if (!empty($productrevision)) {
                $productrevisionnumber = $productrevision->number;
            }
            $qcrevision = $this->specrevision_model->get(array('type' => QC_SPEC_CATEGORY_TYPE_QC, 'revision_id' => $lastrevision->id), true);
            if (!empty($qcrevision)) {
                $qcrevisionnumber = $qcrevision->number;
            }
        }

        return $this->project_model->edit($project_id, array('revision_string' => "$projectrevisionnumber/$productrevisionnumber/$qcrevisionnumber"));
    }

    public function get_last_revision($project_id) {

        $this->load->model('qc/revision_model');
        return $this->revision_model->get(array('project_id' => $project_id), true, 'number DESC');
    }

    public function get_product_code($project_id) {

        $this->load->model('codes/part_model');
        $project = $this->get($project_id);
        $part = $this->part_model->get($project->part_id);
        if (!empty($part)) {
            return $this->part_model->get_number($part->id);
        }
    }

    public function get_details($project_id) {

        $this->load->model('codes/part_model');
        $this->load->model('qc/projectrelated_model');
        $this->load->helper('date');

        $project = $this->get($project_id);

        $part = $this->part_model->get($project->part_id);

        $data['projectid'] = $project->id;
        $data['jobno'] = $this->part_model->get_number($part->id);
        $data['productcode'] = $this->part_model->get_number($part->id);
        $data['productname'] = $part->name;
        $data['productnamech'] = $part->name_ch;
        $data['customerproductcode'] = $project->customer_code;
        $data['batchsize'] = $project->batch_size;
        $data['samplesize'] = $project->sample_size;
        $data['inspectionlevel'] = $project->inspection_level;
        $data['defectcriticallimit'] = $project->defect_critical_limit;
        $data['defectmajorlimit'] = $project->defect_major_limit;
        $data['defectminorlimit'] = $project->defect_minor_limit;
        $data['containschanges'] = $project->containschanges;
        $data['shippingmarks'] = $project->shippingmarks;
        // $data['approvedproductcustomer'] = $project->approved_product_customer;
        // $data['approvedqccustomer'] = $project->approved_qc_customer;
        // $data['approvedprojectadmin'] = $project->approved_project_admin;
        // $data['approvedproductadmin'] = $project->approved_product_admin;
        // $data['approvedqcadmin'] = $project->approved_qc_admin;
        $data['inspector_1_user_id'] = $project->inspector_1_user_id;
        $data['inspector_1_comments'] = $project->inspector_1_comments;
        $data['inspector_2_user_id'] = $project->inspector_2_user_id;
        $data['inspector_2_comments'] = $project->inspector_2_comments;
        $data['inspector_3_user_id'] = $project->inspector_3_user_id;
        $data['inspector_3_comments'] = $project->inspector_3_comments;
        $data['inspector_4_user_id'] = $project->inspector_4_user_id;
        $data['inspector_4_comments'] = $project->inspector_4_comments;
        $data['approvedprojectadminname'] = $this->user_model->get_name($project->approved_project_admin);
        $data['approvedproductadminname'] = $this->user_model->get_name($project->approved_product_admin);
        $data['approvedqcadminname'] = $this->user_model->get_name($project->approved_qc_admin);
        $data['status'] = $project->status;
        $data['result'] = $project->result;

        $data['related'] = array();

        if ($relateds = $this->projectrelated_model->get(array('project_id' => $project->id))) {
            foreach ($relateds as $related) {
                if ($relatedproject = $this->project_model->get($related->related_id)) {
                    $data['related'][$relatedproject->id] = $this->project_model->get_name($relatedproject->part_id, true);
                }
            }
        }

        if ($lastrevision = $this->get_last_revision($project_id)) {
            $data['revisionno'] = $lastrevision->number;
            $data['lastupdatedby'] = $this->user_model->get_name($lastrevision->user_id);
            $data['lastrevisiondate'] = mdate('%d/%m/%Y', $lastrevision->revision_date);
            $data['revisionstring'] = $project->revision_string;
        } else {
            $data['revisionstring'] = '0/0/0';
            $data['revisionno'] = 0;
            $data['lastupdatedby'] = ' ';
            $data['lastrevisiondate'] = ' ';
        }
        return $data;
    }

    public function update_acceptance_status($project_id) {

        $status = $this->project_model->get_acceptance_status($project_id);
        $this->project_model->edit($project_id, array('result' => $status));
        return null;
    }

    /**
     * Queries all QC results to see what status this project has
     */
    public function get_acceptance_status($project_id=null) {

        $this->load->model('qc/job_model');
        $this->load->model('qc/spec_model');
        $this->load->model('qc/speccategory_model');

        if (empty($project_id)) {
            return QC_RESULT_HOLD;
        }

        $status = QC_RESULT_PASS;

        if ($jobs = $this->job_model->get(array('project_id' => $project_id))) {
            $qc_specs_catid=$this->spec_model->get(array('project_id'=>$project_id), false, null, null);


		    foreach ($jobs as $job) {


                if ($job->result == QC_RESULT_HOLD && $status == QC_RESULT_PASS) {
                    foreach($qc_specs_catid as $k => $v)
                    {
                        if ($v -> category_id == $job -> category_id)
                        {
                            $status = QC_RESULT_HOLD;
                        }
                    }

                  } else if ($job->result == QC_RESULT_REJECT) {
                      $status = QC_RESULT_REJECT;
                      break;



                }
            }
        } else {
            $status = QC_RESULT_HOLD;
        }

        if ($status == QC_RESULT_REJECT) {
            return $status;
        }

        // Check processes that have no job: automatic HOLD unless already REJECT
        if ($specs = $this->spec_model->get(array('project_id' => $project_id))) {
            foreach ($specs as $spec) {
                $speccategory = $this->speccategory_model->get($spec->category_id);

                if ($speccategory->type == QC_SPEC_CATEGORY_TYPE_QC && $speccategory->name != 'Files') {
                    if (!($jobs = $this->job_model->get(array('project_id' => $project_id, 'category_id' => $spec->category_id)))) {

                         return QC_RESULT_HOLD;


                    }
                }
            }
        } else {
            xdebug_break();return QC_RESULT_HOLD;
        }
        xdebug_break();return $status;
    }

    public function flag_as_changed($project_id) {
        $this->edit($project_id, array('containschanges' => 1));
    }

    // Actually retrieves the codes_parts.name
    public function get_name($part_id, $withcode=false) {

        $this->load->driver('cache');
        $this->load->model('codes/part_model');

        $part_name = $this->cache->apc->get('codes_part_name_'.$part_id);
        $this->add_cache_key('codes_part_name_'.$part_id);
        $this->add_cache_key('codes_part_number_'.$part_id);

        if (empty($part_name)) {
            $part = $this->part_model->get($part_id, false, null, array('name'));
            if (empty($part)) {
                return "Deleted part";
            }
            $part_name = $part->name;
            $this->cache->apc->save('codes_part_name_'.$part_id, $part_name);
        }

        $retval = $part_name;
        if ($withcode) {
            $part_code = $this->cache->apc->get('codes_part_number_'.$part_id);
            if (empty($part_code)) {
                $part_code = $this->part_model->get_number($part_id);
            }
            $retval = $part_code . ' ' . $retval;
        }

        return $retval;
    }

    public function get_procedures($project_id, $ids_only=false) {

        $this->load->model('qc/procedure_model');

        $this->db->distinct();
        $this->db->select('qc_procedures.id')->from('qc_procedures');
        $this->db->join('qc_specs_procedures AS qsp', 'qsp.procedure_id = qc_procedures.id');
        $this->db->join('qc_specs', 'qc_specs.id = qsp.spec_id');
        $this->db->join('qc_projects', 'qc_specs.project_id = qc_projects.id');
        $this->db->where('qc_projects.id', $project_id);
        $this->db->order_by('qc_procedures.number');
        $query = $this->db->get();
        $procedures = array();

        if ($query->num_rows > 0) {
            foreach ($query->result() as $row) {
                if ($ids_only) {
                    $procedures[] = $row->id;
                } else {
                    $procedures[] = $this->procedure_model->get($row->id);
                }
            }
        }

        return $procedures;
    }

    public function get_specs($project_id, $spec_type=QC_SPEC_CATEGORY_TYPE_PRODUCT, $chinese=true, $sub_type=false, $valid_category_ids=array()) {

        $params = array('project_id' => $project_id);
        $specsarray = array();

        if ($sub_type) {
            $params['qc_specs.type'] = $sub_type;
        }

        if (!empty($valid_category_ids)) {
            $this->db->where_not_in('category_id', $valid_category_ids);
        }

        $specs = $this->spec_model->get_with_cat_data($params, false, 'qc_specs.language, qc_spec_categories.type, qc_specs.creation_date ASC', $spec_type);

        if (empty($specs)) {
            return null;
        }

        $specs_langarray = array(QC_SPEC_LANGUAGE_EN => array(), QC_SPEC_LANGUAGE_CH => array());

        // First organise the specs in two arrays, by language
        foreach ($specs as $specrecord) {
            $id = $specrecord->spec_id;
            if ($specrecord->spec_language == QC_SPEC_LANGUAGE_CH) {
                $id = $specrecord->spec_english_id;
            }
            $specs_langarray[$specrecord->spec_language][$id] = $specrecord;
        }

        foreach ($specs_langarray[QC_SPEC_LANGUAGE_EN] as $specrecord) {

            $langarray = array();
            $langarray[QC_SPEC_LANGUAGE_EN] = array('data' => $specrecord->spec_data,
                                     'categoryid' => $specrecord->speccategory_id,
                                     'specsid' => $specrecord->spec_id,
                                     'fileid' => $specrecord->spec_file_id,
                                     'partid' => $specrecord->spec_part_id,
                                     'type' => $specrecord->spec_type,
                                     'procedures' => $this->spec_model->get_procedures($specrecord->spec_id));

            if ($spec_type == QC_SPEC_CATEGORY_TYPE_QC) {
                $langarray[QC_SPEC_LANGUAGE_EN]['importance'] = $specrecord->spec_importance;
            }

            // Check for a chinese version
            if (isset($specs_langarray[QC_SPEC_LANGUAGE_CH][$specrecord->spec_id])) {
                $specch = $specs_langarray[QC_SPEC_LANGUAGE_CH][$specrecord->spec_id];
                $langarray[QC_SPEC_LANGUAGE_CH] = array('data' => $specch->spec_data,
                                         'categoryid' => $specch->speccategory_id,
                                         'specsid' => $specch->spec_id,
                                         'fileid' => $specch->spec_file_id,
                                         'partid' => $specch->spec_part_id,
                                         'type' => $specch->speccategory_type);
                if ($spec_type == QC_SPEC_CATEGORY_TYPE_QC) {
                    $langarray[QC_SPEC_LANGUAGE_CH]['importance'] = $specch->spec_importance;
                }
            }

            if (empty($specsarray[$specrecord->speccategory_name])) {
                $specsarray[$specrecord->speccategory_name] = array();
            }
            $specsarray[$specrecord->speccategory_name][] = $langarray;
        }

        return $specsarray;
    }

    public function get_parts($project_id) {

        $query = $this->db->where('project_id', $project_id)->get('qc_project_parts');
        $parts = array();

        if ($query->num_rows > 0) {
            foreach ($query->result() as $row) {
                unset($row->creation_date);
                unset($row->revision_date);
                unset($row->project_id);
                $parts[] = (array) $row;
            }
        }
        return $parts;
    }

    public function get_related($project_id) {


        $query = $this->db->where('project_id', $project_id)->get('qc_project_related');
        $relateds = array();

        if ($query->num_rows > 0) {
            foreach ($query->result() as $row) {
                $relateds[] = (array) $row;
            }
        }

        return $relateds;
    }

    public function get_jobs($project_id) {

        $query = $this->db->where('project_id', $project_id)->get('qc_jobs');
        $jobs = array();

        if ($query->num_rows > 0) {
            foreach ($query->result() as $row) {
                $jobs[$row->category_id] = (array) $row;
            }
        }
        return $jobs;

    }

    public function get_suppliers($project_id, $jobs=array()) {

        $this->load->model('company_model');

        if (empty($jobs)) {
            return null;
        }

        $suppliers = array();

        foreach ($jobs as $category_id => $job) {
            $suppliers[$category_id] = (array) $this->company_model->get($job['supplier_id']);
        }

        return $suppliers;
    }

    /**
     * @param int $project_id
     * @param int $type QC or Product, uses QC_FILE_TYPE_QC or QC_FILE_TYPE_PRODUCT
     * @return array of files
     */
    public function get_files($project_id, $type=null) {

        xdebug_break();$this->db->where('project_id', $project_id);
        if (!empty($type)) {
            $this->db->where('type', $type);
        }
        $query = $this->db->get('qc_project_files');
        $files = array();

        if ($query->num_rows > 0) {
            foreach ($query->result() as $row) {
                $files[] = (array) $row;
            }
        }
        return $files;

    }

    public function get_results($project_id) {

        $this->load->model('qc/specresult_model');

        $specs = $this->spec_model->get(array('project_id' => $project_id, 'job_id' => 0));

        $resultsarray = array();

        if (!empty($specs)) {
            foreach ($specs as $spec) {
                $results = $this->specresult_model->get(array('specs_id' => $spec->id));

                if (!empty($results)) {
                    foreach ($results as $result) {
                        $resultarray = array();
                        $resultarray['id'] = $result->id;
                        $resultarray['checked'] = $result->checked;
                        $resultarray['defects'] = $result->defects;
                        $resultarray['specs_id'] = $result->specs_id;
                        $resultsarray[$spec->id] = $resultarray;
                    }
                }
            }
        }
        return $resultsarray;
    }


    /**
     * Saves a "snapshot" of the entire project to disk, and increments the project's revision number.
     * Depending on which data is edited (project details, product specs or qc specs), the
     * product or QC spec revision numbers are also incremented.
     */
    public function save_revision($project_id) {

        $this->load->model('qc/revision_model');
        $this->load->model('qc/specphoto_model');
        $this->load->model('qc/specrevision_model');

        $newrevisionnumber = 1;
        $oldrevisionid = null;

        $oldrevisiondata->qcspecs = array();
        $oldrevisiondata->productspecs = array();
        $oldrevisiondata->parts = array();
        $oldrevisiondata->jobs = array();
        $oldrevisiondata->results = array();
        $oldrevisiondata->files = array();
        $oldrevisiondata = json_encode($oldrevisiondata);

        // Check if this project already has a revision saved
        $this->db->limit(1);
        $revision = $this->revision_model->get(array('project_id' => $project_id), true, 'number DESC');
        if (!empty($revision)) {
            $newrevisionnumber = $revision->number + 1;
            $oldrevisionid = $revision->id;
            $oldrevisiondata = $revision->data;
        }

        // Save new revision
        $newrevisiondata = $this->project_model->get_json($project_id, $newrevisionnumber);
        $newrevision_params = array('project_id' => $project_id, 'user_id' => $this->session->userdata('user_id'), 'number' => $newrevisionnumber, 'data' => $newrevisiondata);
        $revisionid = $this->revision_model->add($newrevision_params);
        $newrevision = $this->revision_model->get($revisionid);

        $existingspecrevisions = array(QC_SPEC_CATEGORY_TYPE_PRODUCT => 0, QC_SPEC_CATEGORY_TYPE_QC => 0);

        // Update the revision_id for any matching spec_revision record
        if (!empty($oldrevisionid)) {
            if ($specrevisions = $this->specrevision_model->get(array('revision_id' => $oldrevisionid))) {
                foreach ($specrevisions as $specrevision) {
                    $existingspecrevisions[$specrevision->type] = $specrevision;
                }
            }
        }

        $this->project_model->edit($project_id, array('containschanges' => false));

        // If this includes product or QC spec changes, increment the matching revision number
        foreach ($existingspecrevisions as $type => $existingspecrevision) {
            if ($this->revision_model->has_spec_data_changed($newrevision->id, $type, $oldrevisiondata)) {
                $newspecrevision_params = array('revision_id' => $newrevision->id, 'type' => $type, 'project_id' => $project_id);

                if (empty($existingspecrevision)) { // A spec change, but no spec revision: create one
                    $newspecrevision_params['number'] = 1;
                } else { // A spec change, and an existing spec revision: insert a new one, continuing the numbering from previous
                    $newspecrevision_params['number'] = $existingspecrevision->number + 1;
                }

                $this->specrevision_model->add($newspecrevision_params);

            } else if (!empty($existingspecrevision)) { // No spec changes, but a spec revision exists: update its revision_id
                $this->specrevision_model->edit($existingspecrevision->id, array('revision_id' => $newrevision->id));
            }
        }

        $this->revision_model->edit($newrevision->id, array('data' => $this->project_model->get_json($project_id, $newrevisionnumber)));

        // Update acceptance status
        $this->project_model->update_acceptance_status($project_id);

        // Update revision string
        $this->project_model->update_revision_string($project_id);

        return $newrevisionnumber;
    }

    public function get_json($project_id, $revisionno=null) {

        $specs = $this->spec_model->get(array('project_id' => $project_id));
        $project = $this->project_model->get($project_id);

        $snapshot = (array) $project;
        $snapshot['qcspecs'] = array();
        $snapshot['productspecs'] = array();
        $snapshot['details'] = $this->project_model->get_details($project_id);
        $snapshot['parts'] = $this->project_model->get_parts($project_id);
        $snapshot['jobs'] = $this->project_model->get_jobs($project_id);
        $snapshot['results'] = $this->project_model->get_results($project_id);
        $snapshot['files'] = $this->project_model->get_files($project_id);

        if (!is_null($revisionno)) {
            $snapshot['details']['revisionno'] = $revisionno;
        }

        if (!empty($specs)) {
            foreach ($specs as $spec) {
                $specscat = $this->speccategory_model->get($spec->category_id);
                $snapshot[strtolower(get_lang_for_constant_value('QC_SPEC_CATEGORY_TYPE_',$specscat->type)).'specs'][$spec->id] = (array) $spec;

                // Add array of photo ids
                $specphotos = $this->specphoto_model->get(array('spec_id' => $spec->id));

                if (!empty($specphoto)) {
                    $snapshot[strtolower(get_lang_for_constant_value('QC_SPEC_CATEGORY_TYPE_',$specscat->type)).'specs'][$spec->id]['photos'] = array();

                    foreach ($specphotos as $specphoto) {
                        $snapshot[strtolower(get_lang_for_constant_value('QC_SPEC_CATEGORY_TYPE_',$specscat->type)).'specs'][$spec->id]['photos'][$specphoto->id] = (array) $specphoto;
                    }
                }
            }
        }

        return json_encode($snapshot);
    }


    /**
     * In addition to the normal update, verify if the project contains changes.
     * Also check the last revision date. If too old, update the result field
     */
    public function edit($project_id, $params=array()) {

        $original = (array) $this->project_model->get($project_id);

        foreach ($original as $var => $val) {
            if (empty($params[$var])) {
                continue;
            }

            if ($params[$var] != $val && !in_array($var, array('revision_string', 'creation_date', 'revision_date', 'approved_project_admin', 'approved_product_admin', 'approved_qc_admin'))) {
                if ($var == 'containschanges' && !$val) {
                    continue;
                }
                $params['containschanges'] = true;
            }
        }

        // Update the result field if not updated for over 5 minutes
        
		if ((time() - $original['revision_date']) > 300) { // 5 minute cache
           xdebug_break(); $params['result'] = $this->project_model->get_acceptance_status($project_id);
        }
        return parent::edit($project_id, $params);
    }

    public function add($params) {
        return parent::add($params);
    }

    // Must call the spec's delete function too, so that photos are deleted correctly
    // Also delete related associations
    public function delete($project_id) {


        if (!($result = parent::delete($project_id))) {
            return false;
        }

        $specs = $this->spec_model->get(array('project_id' => $project_id));

        if (!empty($specs)) {
            foreach ($specs as $spec) {
                $this->spec_model->delete($spec->id);
            }
        }

        $this->db->where('project_id = '.$project_id.' OR related_id = '.$project_id, null, false);
        $relateds = $this->projectrelated_model->get();

        if (!empty($relateds)) {
            foreach ($relateds as $related) {
                $this->projectrelated_model->delete($related->id);
            }
        }
        return $result;
    }

    public function get_revision($project_id, $revisionno=null, $type=null) {

        $this->load->model('qc/revision_model');

        if (is_null($revisionno) && is_null($type)) {
            $revisionno = $this->project_model->get_last_revision($project_id)->number;
        }

        if (!is_null($type)) {
            $project = $this->project_model->get($project_id);
            $params = array('project_id' => $project_id, 'type' => $type, 'number' => $revisionno);

            if ($specrevision = $this->specrevision_model->get($params, true, 'number DESC')) {
                return $this->revision_model->decode($specrevision->revision_id, $type);
            } else {
                return false;
            }

        } else {
            $revision = $this->revision_model->get(array('project_id' => $project_id, 'number' => $revisionno), true);

            if (!empty($revision)) {

                $revisiondata = $this->revision_model->decode($revision->id, $type);
                // $revisiondata['files'] = $revision->files;
                return $revisiondata;
            } else {
                return false;
            }
        }
    }

    public function get_customer_emails($project_id) {

        $this->load->model('codes/codes_project_model');
        $this->load->model('codes/part_model');
        $this->load->model('company_model');

        $project = $this->project_model->get($project_id);
        if (!($part = $this->part_model->get($project->part_id))) {
            return null;
        }

        $codesproject = $this->codes_project_model->get($part->project_id);
        $company = $this->company_model->get($codesproject->company_id);


        $users = $this->user_model->get(array('company_id' => $company->id));
        $email_array = array();

        if (!empty($users)) {
            foreach ($users as $user) {
                $emails = $this->user_contact_model->get_by_user_id($user->id, USERS_CONTACT_TYPE_EMAIL, false, true);

                if (!empty($emails) && is_array($emails)) {
                    foreach ($emails as $email) {
                        $email_array[$email] = $this->user_model->get_name($user) . ' &lt;' . $email . '&gt;';
                    }
                }
            }
        }

        if (!empty($company->email)) {
            $email_array[$company->email] = $company->name . ' &lt;' . $company->email . '&gt;';
        }
        if (!empty($company->email2)) {
            $email_array[$company->email2] = $company->name . ' &lt;' . $company->email2 . '&gt;';
        }
        return $email_array;
    }

    public function get_categories($project_id, $type=QC_SPEC_CATEGORY_TYPE_PRODUCT, $revisionno=null, $only_with_jobs=false) {

        $this->load->model('qc/job_model');
        $categories_array = array();

        if (is_null($revisionno)) {
            // Find out if the project has files
            $files = $this->project_model->get_files($project_id, $type);

            if (!empty($files)) {
                $categories[$this->speccategory_model->get_files_id()] = 'Attached files ('.count($files).')';
            }

            $this->db->select('qc_spec_categories.id, qc_spec_categories.name', false);
            $this->db->join('qc_specs s', 's.category_id = qc_spec_categories.id');
            $this->db->join('qc_projects p', 'p.id = s.project_id');
            $this->db->group_by('qc_spec_categories.id');
            $categories = $this->speccategory_model->get(array('qc_spec_categories.type' => $type, 'p.id' => $project_id));

            foreach ($categories as $category) {
                if ($only_with_jobs && !($this->job_model->get(array('category_id' => $category->id, 'project_id' => $project_id)))) {
                    continue;
                }
                $categories_array[$category->id] = $category->name;
            }
        } else {
            $revisiondata = $this->project_model->get_revision($project_id, $revisionno, $type);

            if ($type == QC_SPEC_CATEGORY_TYPE_PRODUCT) {
                if (!empty($revisiondata['files'])) {
                    $files_count = 0;
                    foreach ($revisiondata['files'] as $file) {
                        if (!empty($file->type) && $file->type == QC_FILE_TYPE_PRODUCT) {
                            $files_count++;
                        }
                    }
                    $categories_array[$this->speccategory_model->get_files_id()] = 'Attached files ('.$files_count.')';
                }

                foreach ($revisiondata['specs'] as $categoryname => $specarray) {
                    foreach ($specarray as $spec_id => $langarray) {
                        $productspec = (array) $langarray[QC_SPEC_LANGUAGE_EN];
                        if (empty($productspec['category_id'])) {
                            continue;
                        }
                        $category = $this->speccategory_model->get($productspec['category_id']);
                        $categories_array[$category->id] = $category->name;
                    }
                }
            } else {
                if (!empty($revisiondata['files'])) {
                    $files_count = 0;
                    foreach ($revisiondata['files'] as $file) {
                        if (!empty($file->type) && $file->type == QC_FILE_TYPE_QC) {
                            $files_count++;
                        }
                    }
                    $categories_array[$this->speccategory_model->get_files_id()] = 'Attached files ('.$files_count.')';
                }

                foreach ($revisiondata['specs'] as $categoryname => $specarray) {
                    foreach ($specarray as $spec_id => $langarray) {
                        $qcspec = $langarray[QC_SPEC_LANGUAGE_EN];
                        if ($only_with_jobs && !($this->job_model->get(array('category_id' => $qcspec['category_id'], 'project_id' => $project_id)))) {
                            continue;
                        }
                        $category = $this->speccategory_model->get($qcspec['category_id']);
                        $categories_array[$category->id] = $category->name;
                    }
                }
            }
        }

        return $categories_array;
    }

    /**
     * Creates a new project based on the requested one, duplicating the Product and QC specs of the current project's latest revision
     */
    public function duplicate($project_id, $part_id) {

        $this->load->driver('cache');
        $this->cache->clean();

        $this->load->model('qc/projectpart_model');
        $this->load->model('qc/projectfile_model');

        $newproject = $this->get($project_id);
        unset($newproject->id);
        unset($newproject->approved_qc_admin);
        unset($newproject->approved_product_admin);
        unset($newproject->approved_project_admin);
        unset($newproject->approved_product_customer);
        unset($newproject->approved_qc_customer);
        unset($newproject->status);
        unset($newproject->result);
        unset($newproject->containschanges);
        unset($newproject->creation_date);
        unset($newproject->revision_date);

        $newproject->part_id = $part_id;
        $new_project_id = $this->add($newproject);

        // Copy project parts
        $this->db->order_by('creation_date');
        $parts = $this->get_parts($project_id);
        $part_ids = array();

        $creation_date_counter = 0;

        if (!empty($parts)) {
            foreach ($parts as $part) {
                $newpart = array(
                    'name' => $part['name'],
                    'length' => $part['length'],
                    'width' => $part['width'],
                    'height' => $part['height'],
                    'diameter' => $part['diameter'],
                    'thickness' => $part['thickness'],
                    'weight' => $part['weight'],
                    'other' => $part['other'],
                    'project_id' => $new_project_id,
                    'creation_date' => time() + $creation_date_counter++);

                $part_ids[$part['id']] = $this->projectpart_model->add($newpart, false);
            }
        }

        // Copy project files and physical files
        $this->db->order_by('creation_date');
        $files = $this->get_files($project_id);
        $file_ids = array();
        $creation_date_counter = 0;

        if (!empty($files)) {
            foreach ($files as $file) {
                $newfile = array(
                    'file' => $file['file'],
                    'hash' => $file['hash'],
                    'type' => $file['type'],
                    'description' => $file['description'],
                    'project_id' => $new_project_id,
                    'creation_date' => time() + $creation_date_counter++);

                $file_ids[$file['id']] = $this->projectfile_model->add($newfile, false);

                $oldfilepath = PATH_QC_FILES . "/$project_id/{$file['hash']}";
                $newfilepath = PATH_QC_FILES . "/$new_project_id/{$file['hash']}";

                if (!file_exists(PATH_QC_FILES."/$new_project_id")) {
                    mkdir(PATH_QC_FILES."/$new_project_id");
                }

                if (file_exists($oldfilepath)) {
                    copy($oldfilepath, $newfilepath);
                }
            }
        }

        // Copy related projects
        $this->db->order_by('creation_date');
        $relateds = $this->get_related($project_id);
        $related_ids = array();
        $creation_date_counter = 0;

        if (!empty($relateds)) {
            foreach ($relateds as $related) {
                $newrelated = array(
                    'related_id' => $related['related_id'],
                    'project_id' => $new_project_id,
                    'creation_date' => time() + $creation_date_counter++);
                $related_ids[$related['id']] = $this->projectrelated_model->add($newrelated, array('*'));
            }
        }

        // Add "related" associations between current and duplicated project
        $this->projectrelated_model->add(array('project_id' => $project_id, 'related_id' => $new_project_id), array('*'));
        $this->projectrelated_model->add(array('project_id' => $new_project_id, 'related_id' => $project_id), array('*'));

        // Duplicate english specs first to record their new Ids
        $spec_ids = array();
        $this->db->order_by('id');
        $this->db->where('english_id', 0, false);
        $this->db->where('project_id', $project_id);
        $english_specs = $this->spec_model->get();
        $creation_date_counter = 0;

        if (!empty($english_specs)) {
            foreach ($english_specs as $spec) {
                $spec = (array) $spec;

                $newspec = array(
                    'category_id' => $spec['category_id'],
                    'type' => $spec['type'],
                    'data' => $spec['data'],
                    'language' => $spec['language'],
                    'datatype' => $spec['datatype'],
                    'units' => $spec['units'],
                    'importance' => $spec['importance'],
                    'project_id' => $new_project_id,
                    'creation_date' => time() + $creation_date_counter++);

                if (!empty($spec['part_id'])) {
                    $newspec['part_id'] = $part_ids[$spec['part_id']];
                }

                if (!empty($spec->file_id)) {
                    $newspec['file_id'] = $file_ids[$spec['file_id']];
                }

                $spec_ids[$spec['id']] = $this->spec_model->add($newspec);
            }
        }

        // Now duplicate chinese specs and link them to the new english specs
        $this->db->order_by('creation_date');
        $this->db->where('english_id >', 0);
        $this->db->where('project_id', $project_id);
        $chinese_specs = $this->spec_model->get();

        $creation_date_counter = 0;

        if (!empty($chinese_specs) && !empty($english_specs)) {
            foreach ($chinese_specs as $spec) {
                $spec = (array) $spec;

                $newspec = array(
                    'category_id' => $spec['category_id'],
                    'type' => $spec['type'],
                    'data' => $spec['data'],
                    'language' => $spec['language'],
                    'datatype' => $spec['datatype'],
                    'units' => $spec['units'],
                    'importance' => $spec['importance'],
                    'project_id' => $new_project_id,
                    'english_id' => $spec_ids[$spec['english_id']],
                    'creation_date' => time() + $creation_date_counter++);

                if (!empty($spec['part_id'])) {
                    $newspec['part_id'] = $part_ids[$spec['part_id']];
                }

                if (!empty($spec->file_id)) {
                    $newspec['file_id'] = $file_ids[$spec['file_id']];
                }

                $this->spec_model->add($newspec);
            }
        }

        // Now duplicate spec photos
        $this->db->order_by('qc_spec_photos.creation_date');
        $this->db->join('qc_specs', 'qc_specs.id = qc_spec_photos.spec_id');
        $spec_photos = $this->specphoto_model->get(array('project_id' => $project_id));;
        $creation_date_counter = 0;

        if (!empty($spec_photos) && !empty($english_specs)) {
            foreach ($spec_photos as $spec_photo) {
                $newspec_photo = new stdClass();
                $newspec_photo->file = $spec_photo->file;
                $newspec_photo->hash = $spec_photo->hash;
                $newspec_photo->description = $spec_photo->description;

                if (!empty($spec_ids[$spec_photo->spec_id])) {
                    $newspec_photo->spec_id = $spec_ids[$spec_photo->spec_id];
                    $newspec_photo->creation_date = time() + $creation_date_counter++;
                    $this->specphoto_model->add($newspec_photo, false);

                    $oldphotopath = ROOTPATH . "/files/qc/$project_id";
                    $newphotopath = ROOTPATH . "/files/qc/$new_project_id";

                    if (!file_exists($newphotopath.'/small')) {
                        mkdir($newphotopath.'/small', 0777, true);
                        mkdir($newphotopath.'/thumb', 0777, true);
                    }

                    if (file_exists($oldphotopath."/$spec_photo->hash")) {
                        copy($oldphotopath."/$spec_photo->hash", $newphotopath."/$spec_photo->hash");
                    }
                    if (file_exists($oldphotopath."/small/$spec_photo->hash")) {
                        copy($oldphotopath."/small/$spec_photo->hash", $newphotopath."/small/$spec_photo->hash");
                    }
                    if (file_exists($oldphotopath."/thumb/$spec_photo->hash")) {
                        copy($oldphotopath."/thumb/$spec_photo->hash", $newphotopath."/thumb/$spec_photo->hash");
                    }
                }
            }
        }
        return $new_project_id;
    }

    /**
     * Returns an array of all projects, indexed by ID
     * @param string $term String by which to restrict the results, using codes_parts.name
     * @param int $project_id Project whose related projects won't be included in this search
     * @return array
     *
     */
    public static function get_list($term=null, $project_id=null) {
        $ci = get_instance();

        $sql = "SELECT * FROM ((
                SELECT
                qc_projects.id AS project_id,
                CONCAT(codes_divisions.code, DATE_FORMAT(FROM_UNIXTIME(codes_projects.creation_date), '%y'), '.', codes_projects.number, '.', codes_parts.number, '.', companies.code) AS productcode,
                codes_parts.name AS productname

                FROM (`qc_projects`)
                JOIN `codes_parts` ON `qc_projects`.`part_id` = `codes_parts`.`id`
                JOIN `codes_projects` ON `codes_projects`.`id` = `codes_parts`.`project_id`
                JOIN `codes_divisions` ON `codes_divisions`.`id` = `codes_projects`.`division_id`
                JOIN `companies` ON `companies`.`id` = `codes_projects`.`company_id`)
                qc_projects)

                WHERE
                productcode LIKE '%" . $ci->db->escape_like_str($term) . "%'  OR
                productname LIKE '%" . $ci->db->escape_like_str($term) . "%'
                LIMIT 40";

        $query = $ci->db->query($sql);

        $projects = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $object = new stdClass();
                $object->value = $row->project_id;
                $object->label = "[$row->productcode] $row->productname";
                $projects[] = $object;
            }
        } else {
            return false;
        }

        return $projects;
    }

    public function get_relatable_projects($project_id, $term=null) {

        $this->db->where_not_in('id', array_keys($data['related']) + array($project_id));
        $this->db->select('part_id, id');
        $allprojects = $this->project_model->get();

        if (!empty($allprojects)) {
            foreach ($allprojects as $otherproject) {
                $otherproject_name = $this->project_model->get_name($otherproject->part_id, true);
                $data['otherprojects'][$otherproject->id] = $otherproject_name;
            }
        }
    }
}
