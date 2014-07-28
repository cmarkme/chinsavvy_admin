<?php

use Eloquent\ProcessCost;
use Eloquent\ProcessSubtype;

/**
*
*/
class Process_Cost extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
        require_capability('estimates:user');
        $this->load->library('form_validation');

	}

	public function index()
	{
        redirect('estimates/process/browse');
    }

	public function browse()
    {
        $pageDetails = array(
            'title' => 'Browse Process Costs',
            'csstoload' => array('jquery.datatable'),
            'jstoloadinfooter' => array('datatables/js/jquery.dataTables', 'common/dataTables-init'),
            'content_view' => 'estimates/process_cost/browse',
            );

        $this->load->view('template/default', $pageDetails);
    }

    public function add()
    {
        $this->load->helper('form_template');
        $this->load->model('codes/supplier_model');

        $pageDetails = array(
            'title' => 'Add Process Cost',
            'csstoload' => array(),
            'jstoloadinfooter' => array('application/estimates/price_breaks', 'application/estimates/ajax_dropdowns'),
            'content_view' => 'estimates/process_cost/add',
            'supplier_dropdown' => $this->supplier_model->get_dropdown_data(),
            );

        $this->load->view('template/default', $pageDetails);
    }

    public function edit($id)
    {
        $this->load->helper('form_template');
        $this->load->model('codes/supplier_model');

        $process_cost = ProcessCost::find($id);

        $pageDetails = array(
            'title' => 'Edit Process Cost',
            'csstoload' => array(),
            'jstoloadinfooter' => array('application/estimates/price_breaks', 'application/estimates/ajax_dropdowns'),
            'content_view' => 'estimates/process_cost/edit',
            'process_cost' => $process_cost,
            'supplier_dropdown' => $this->supplier_model->get_dropdown_data(),
            );

        form_element::set_default_data($process_cost->toArray());

        $this->load->view('template/default', $pageDetails);
    }

    public function datatable()
    {
        echo ProcessCost::getDataTable();
    }

    public function process()
    {
        $id = $this->input->post('id');

        if ($id === false)
        {
            # insert
           ProcessCost::create( $this->input->post() );
        }
        else
        {
            # update
            ProcessCost::find($id)
                ->update( $this->input->post() );

        }

        return redirect('estimates/process_cost/browse');
    }

    public function dropdown($process_subtype_id)
    {
        $process_cost = ProcessCost::where('process_subtype_id', $process_subtype_id)
            ->get();

        $return = array();
        foreach ($process_cost as $process_cost)
        {
            $return[$process_cost->id] = $process_cost->id . ' | ' . $process_cost->machine_size . ' | ' . $process_cost->action;
        }

        echo json_encode($return);
    }
}