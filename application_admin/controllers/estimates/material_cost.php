<?php

use Eloquent\MaterialCost;
use Eloquent\MaterialGrade;

/**
*
*/
class Material_Cost extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
        require_capability('estimates:user');
        $this->load->library('form_validation');

	}

	public function index()
	{
        redirect('estimates/material_cost/browse');
    }

	public function browse()
    {
        $pageDetails = array(
            'title' => 'Browse Raw Material Costs',
            'csstoload' => array('jquery.datatable'),
            'jstoloadinfooter' => array('datatables/js/jquery.dataTables', 'common/dataTables-init'),
            'content_view' => 'estimates/material_cost/browse',
            );

        $this->load->view('template/default', $pageDetails);
    }

    public function add()
    {
        $this->load->helper('form_template');
        $this->load->model('codes/supplier_model');

        $pageDetails = array(
            'title' => 'Add Raw Material Cost',
            'csstoload' => array(),
            'jstoloadinfooter' => array('application/estimates/price_breaks', 'application/estimates/ajax_dropdowns'),
            'content_view' => 'estimates/material_cost/add',
            'supplier_dropdown' => $this->supplier_model->get_dropdown_data(),
            );

        $this->load->view('template/default', $pageDetails);
    }

    public function edit($id)
    {
        $this->load->helper('form_template');
        $this->load->model('codes/supplier_model');

        $material_cost = MaterialCost::find($id);

        $pageDetails = array(
            'title' => 'Edit Raw Material Cost',
            'csstoload' => array(),
            'jstoloadinfooter' => array('application/estimates/price_breaks', 'application/estimates/ajax_dropdowns'),
            'content_view' => 'estimates/material_cost/edit',
            'material_cost' => $material_cost,
            'supplier_dropdown' => $this->supplier_model->get_dropdown_data(),
            );

        form_element::set_default_data($material_cost->toArray());

        $this->load->view('template/default', $pageDetails);
    }

    public function datatable()
    {
        echo MaterialCost::getDataTable();
    }

    public function process()
    {
        $id = $this->input->post('id');

        if ($id === false)
        {
            # insert
            try
            {
                MaterialCost::create( $this->input->post() );
            }
            catch (Eloquent\ValidationException $e)
            {
                return $this->add();
            }
        }
        else
        {
            # update
            try
            {
                MaterialCost::find($id)->update( $this->input->post() );
            }
            catch (Eloquent\ValidationException $e)
            {
                return $this->edit($id);
            }
        }

        return redirect('estimates/material_cost/browse');
    }

    public function dropdown($material_grade_id)
    {
        $material_costs = MaterialCost::where('material_grade_id', $material_grade_id)
            ->get();

        $return = array();
        foreach ($material_costs as $material_cost)
        {
            $return[$material_cost->id] = $material_cost->id . ' | ' . $material_cost->form;
        }

        echo json_encode($return);
    }
}