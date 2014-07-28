<?php
/**
 * Contains the Job_Model Model class
 * @package models
 */

/**
 * Job Model class
 * @package models
 */
class Job_Model extends MY_Model {

    /**
     * @var string The DB table used by this model
     */
    var $table = 'qc_jobs';

    /**
     * Returns an array of additional specifications associated with this Job
     */
    public function get_additional_specs($job_id) {

        $job = $this->get($job_id);

        $jobspecs = array(QC_SPEC_TYPE_ADDITIONAL => array(), QC_SPEC_TYPE_OBSERVATION => array());

        $specs = $this->spec_model->get(array('type' => QC_SPEC_TYPE_ADDITIONAL, 'job_id' => $job_id, 'language' => QC_SPEC_LANGUAGE_EN), false, 'creation_date');

        if (!empty($specs)) {
            $jobspecs[QC_SPEC_TYPE_ADDITIONAL] = array();

            $counter = 0;
            foreach ($specs as $spec) {
                $jobspecs[QC_SPEC_TYPE_ADDITIONAL][$counter][QC_SPEC_LANGUAGE_EN] = $spec;

                if ($chinesespec = $this->spec_model->get(array('english_id' => $spec->id, 'language' => QC_SPEC_LANGUAGE_CH), true)) {
                    $jobspecs[QC_SPEC_TYPE_ADDITIONAL][$counter][QC_SPEC_LANGUAGE_CH] = $chinesespec;
                }

                $counter++;
            }
        }

        $specs = $this->spec_model->get(array('type' => QC_SPEC_TYPE_OBSERVATION, 'job_id' => $job_id, 'language' => QC_SPEC_LANGUAGE_EN), false, 'creation_date');

        if (!empty($specs)) {
            $jobspecs[QC_SPEC_TYPE_OBSERVATION] = array();

            $counter = 0;
            foreach ($specs as $spec) {
                $jobspecs[QC_SPEC_TYPE_OBSERVATION][$counter][QC_SPEC_LANGUAGE_EN] = $spec;

                if ($chinesespec = $this->spec_model->get(array('english_id' => $spec->id, 'language' => QC_SPEC_LANGUAGE_CH), true)) {
                    $jobspecs[QC_SPEC_TYPE_OBSERVATION][$counter][QC_SPEC_LANGUAGE_CH] = $chinesespec;
                }

                $counter++;
            }
        }

        return $jobspecs;
    }

    public function get_qc_results($job_id=null, $project_id=null, $category_id=null) {


        $processes = array();

        if (empty($job_id)) {
            $params['project_id']= $project_id;

            if ($category_id != -1) {
                $params['category_id'] = $category_id;
            }

            if ($jobs = $this->job_model->get($params)) {
                foreach ($jobs as $job) {
                    $processes[$job->category_id]['job'] = $job;
                }
            }

        } else if ($job = $this->job_model->get($job_id)) {
            $processes[$job->category_id]['job'] = $job;
        } else {
            add_message('This QC job does not exist!', 'error');
            return false;
        }

        return $processes;
    }

    public function get_files($job_id) {

        $this->load->model('qc/jobfile_model');
        return $this->jobfile_model->get(array('job_id' => $job_id, 'is_image' => 0));
    }

    public function get_acceptance_status($project_id, $category_id)
    {
        $this->load->model('project_model');

        $project = $this->project_model->get($project_id);

        $params = array(
            'category_id' => $category_id,
            'project_id' => $project_id,
            );

        $specs = $this->spec_model->get_with_result_data($params);

        // $job = $this->job_model->get($params);

        foreach ($specs as $spec)
        {
            if ( ! $spec->checked) return QC_RESULT_HOLD;
            switch($spec->importance) {
                case QC_SPEC_IMPORTANCE_CRITICAL:
                    $permitted = floor($project->sample_size * ($project->defect_critical_limit / 100));
                    break;
                case QC_SPEC_IMPORTANCE_MAJOR:
                    $permitted = floor($project->sample_size * ($project->defect_major_limit / 100));
                    break;
                case QC_SPEC_IMPORTANCE_MINOR:
                    $permitted = floor($project->sample_size * ($project->defect_minor_limit / 100));
                    break;
            }
            if ($permitted < $spec->defects)
            {
                return QC_RESULT_REJECT;
            }
        }

        return QC_RESULT_PASS;
    }

    public function update_acceptance_status($project_id, $category_id)
    {
        $result = $this->get_acceptance_status($project_id, $category_id);

        $this->db->update($this->table, compact('result'), compact('project_id', 'category_id'));

        return $result;
    }

}
