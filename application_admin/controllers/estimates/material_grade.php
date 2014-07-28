<?php

use Eloquent\MaterialGrade;
use Eloquent\MaterialType;

/**
*
*/
class Material_Grade extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		require_capability('estimates:doanything');
        $this->config->set_item('replacer', array('estimate' => array('estimates|Material Types'), 'add' => 'Create new user account'));
        //$this->config->set_item('replacer', array('users' => array('user|Users'), 'add' => 'Create new user account'));
	}

	public function datatable($id)
	{
		echo MaterialGrade::getDataTable($id);
	}

	public function add($material_type_id)
	{
		$this->load->helper('form_template');

		$material_type = MaterialType::find($material_type_id);

		$pageDetails = array(
            'title' => 'Add Material Grade',
            'csstoload' => array(),
            'jstoloadinfooter' => array(),
            'content_view' => 'estimates/material_grade/add',
            'material_type' => $material_type,
            );

        $this->load->view('template/default', $pageDetails);
	}

	public function edit($id)
	{
		$this->load->helper('form_template');

		$material_grade = MaterialGrade::with('type')->find($id);

		$pageDetails = array(
			'title' => 'Edit Material Grade',
            'csstoload' => array(),
            'jstoloadinfooter' => array(),
            'content_view' => 'estimates/material_grade/edit',
            'material_grade' => $material_grade,
            );

		form_element::set_default_data($material_grade->toArray());

		//dd($material_grade->type);

        $this->load->view('template/default', $pageDetails);
	}

	public function process($id)
	{
		try
		{
			MaterialGrade::find($id)->update( $this->input->post() );
		}
		catch (Eloquent\ValidationException $e)
		{
			return $this->edit($id);
		}

		return redirect('estimates/material_type/edit/' . $this->input->post('material_type_id'));
	}

	public function process_add($material_type_id)
	{
		$material_grade = array(
			'material_type_id' => $material_type_id,
			'name' => $this->input->post('name')
			);

		MaterialGrade::create( $material_grade );
	}

	public function delete($id)
	{
		$material_grade = MaterialGrade::find($id);

		$name = $material_grade->name;

		$material_grade->delete();

		echo json_encode(array(
			'id' => $id,
			'message' => "Material Grade '$name' was successfully deleted",
			));
	}

	public function dropdown($material_type_id)
    {
        echo json_encode(MaterialGrade::where('material_type_id', $material_type_id)->lists('name', 'id'));
    }

}