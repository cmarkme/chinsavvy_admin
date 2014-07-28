<?php
/**
 * Contains the MY_AJAX_Controller class
 * @package libraries
 */

/**
 * MY_AJAX_Controller class
 * @package libraries
 * @abstract
 */
abstract class MY_AJAX_Controller extends MY_Controller {
    /**
     * @var string $subsystem The subsystem for the purpose of permission checking (enquiries|users|qc|commodities|codes)
     */
    public $subsystem;

    /**
     * @var array $params An array of parameters that will be used by the model
     */
    public $params;

    /**
     * @var mixed $model The model that will handle the AJAX request and return appropriate data
     */
    public $model;

    public function check_capability($type='has', $action, $element) {
        $this->load->helper->('capabilities');
        $functionname = $type.'_capability';
        return $functionname("$this->subsystem:$action".$element);
    }

    public function setup_params_for_datatable() {


        $this->params['page'] = $this->input->get('page');
        $this->params['start'] = $this->input->get('iDisplayStart');
        $this->params['perpage'] = $this->input->get('iDisplayLength');
        $this->params['search'] = $this->input->get('sSearch');
        $this->params['echo'] = $this->input->get('sEcho');
        $this->params['sortcol_0'] = $this->input->get('iSortCol_0');
        $this->params['sortcol_1'] = $this->input->get('iSortCol_1');
        $this->params['sortcol_2'] = $this->input->get('iSortCol_2');
        $this->params['sortcol_3'] = $this->input->get('iSortCol_3');
        $this->params['sortcol_4'] = $this->input->get('iSortCol_4');
        $this->params['sortdir_0'] = $this->input->get('iSortDir_0');
        $this->params['sortdir_1'] = $this->input->get('iSortDir_1');
        $this->params['sortdir_2'] = $this->input->get('iSortDir_2');
        $this->params['sortdir_3'] = $this->input->get('iSortDir_3');
        $this->params['sortdir_4'] = $this->input->get('iSortDir_4');
        $this->params['sortingcols'] = $this->input->get('iSortingCols');

    }

    /**
     * Uses the given model to retrieve a list of optionally filtered/sorted rows of data.
     */
    public function find_for_datatable() {


        $totalrecords = $this->model->count_all_results();

        $this->model->limit($this->params['start'], $this->params['perpage']);

        if (!is_null($this->params['sortcol_0'])) {
            $orderby = '';
            for ($i = 0; $i < $this->params['sortingcols']; $i++) {
                if ($this->sortcolumns[$i] == 'roles') {
                    $orderby = '';
                } else {
                    $orderby .= $this->sortcolumns[$this->params["sortcol_$i"]] . ' ' . $this->params["sortdir_$i"] . ', ';
                }
            }

            if (!empty($orderby)) {
                $orderby = substr_replace($orderby, "", -2);
                $this->model->order_by($orderby);
            }
        }

        $this->displayrecords = $this->model->count_all_results();
    }

    public function get_json_for_datatable() {
        $this->find_for_datatable();
        $output = new stdClass();
        $output->sEcho = $this->params['echo'];
        $output->iTotalRecords = $this->totalrecords;
        $output->iTotalDisplayRecords = $this->displayrecords;
        $output->aaData = array();

        $output = $this->populate_output($output);

        return json_encode($output);
    }

    // To implement in child classes
    abstract protected function populate_output($output);
    abstract public function setup_model();
}
