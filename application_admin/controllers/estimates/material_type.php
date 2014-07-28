<?php

use Eloquent\MaterialType;

/**
*
*/
class Material_Type extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		require_capability('estimates:doanything');
        $this->config->set_item('replacer', array('estimate' => array('estimates|Material Types'), 'add' => 'Create new user account'));
        //$this->config->set_item('replacer', array('users' => array('user|Users'), 'add' => 'Create new user account'));
	}

	public function index()
	{
		redirect('estimates/material_type/browse');
	}

	public function browse($add = false)
	{
		$this->load->helper('form_template');

		$pageDetails = array(
            'title' => 'Browse Raw Material Types',
            'csstoload' => array('jquery.datatable'),
            'jstoloadinfooter' => array('datatables/js/jquery.dataTables', 'common/dataTables-init', 'application/estimates/ajax_add'),
            'content_view' => 'estimates/material_type/browse',
            'add' => $add === true,
            );

        $this->load->view('template/default', $pageDetails);
	}

	public function datatable()
	{
		echo MaterialType::getDataTable();
	}

	public function edit($id)
	{
		$this->load->helper('form_template');

		$material_type = MaterialType::find($id);

		$pageDetails = array(
            'title' => 'Edit Raw Material Types',
            'csstoload' => array('jquery.datatable'),
            'jstoloadinfooter' => array('datatables/js/jquery.dataTables', 'common/dataTables-init', 'application/estimates/ajax_add'),
            'content_view' => 'estimates/material_type/edit',
            'material_type' => $material_type,
            );

		form_element::set_default_data($material_type->toArray());

        $this->load->view('template/default', $pageDetails);
	}

	public function process()
	{
		$id = $this->input->post('id');

		try
		{
			MaterialType::find($id)->update( $this->input->post() );
		}
		catch (Eloquent\ValidationException $e)
        {
            return $this->edit($id);
        }

		return redirect('estimates/material_type/browse');
	}

	public function process_add()
	{
		try
		{
			$material = MaterialType::create( $this->input->post() );
		}
		catch (Eloquent\ValidationException $e)
        {
            return $this->browse(true);
        }

		redirect('estimates/material_type/edit/' . $material->id);
	}

	public function delete($id)
	{
		$material_type = MaterialType::find($id);

		$name = $material_type->name;

		$material_type->delete();

		echo json_encode(array(
			'id' => $id,
			'message' => "Material Type '$name' was successfully deleted",
			));
	}

}