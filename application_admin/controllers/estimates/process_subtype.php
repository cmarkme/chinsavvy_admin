<?php

use Eloquent\ProcessSubtype;
use Eloquent\ProcessType;

/**
*
*/
class Process_Subtype extends MY_Controller
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
		echo ProcessSubtype::getDataTable($id);
	}

	public function add($process_type_id)
	{
		$this->load->helper('form_template');

		$process_type = ProcessType::find($process_type_id);

		$pageDetails = array(
			'title' => 'Add Process Sub-Type',
            'csstoload' => array(),
            'jstoloadinfooter' => array(),
            'content_view' => 'estimates/process_subtype/add',
            'process_type' => $process_type,
            );

        $this->load->view('template/default', $pageDetails);
	}

	public function edit($id)
	{
		$this->load->helper('form_template');

		$process_subtype = ProcessSubtype::with('type')->find($id);

		$pageDetails = array(
			'title' => 'Edit Process Sub-Type',
            'csstoload' => array(),
            'jstoloadinfooter' => array(),
            'content_view' => 'estimates/process_subtype/edit',
            'process_subtype' => $process_subtype,
            );

		form_element::set_default_data($process_subtype->toArray());

        $this->load->view('template/default', $pageDetails);
	}

	public function process($id)
	{
		try
		{
			ProcessSubtype::find($id)->update( $this->input->post() );
		}
		catch (Eloquent\ValidationException $e)
		{
			return $this->edit($id);
		}

		redirect('estimates/process_type/edit/' . $this->input->post('process_type_id'));
	}

	public function process_add($process_type_id)
	{
		$process_subtype = array(
			'process_type_id' => $process_type_id,
			'name' => $this->input->post('name')
			);

		ProcessSubtype::create( $process_subtype );
	}

	public function delete($id)
	{
		$process_subtype = ProcessSubType::find($id);

		$name = $process_subtype->name;

		$process_subtype->delete();

		echo json_encode(array(
			'id' => $id,
			'message' => "Process SubType '$name' was successfully deleted",
			));
	}

	public function dropdown($process_type_id)
    {
        echo json_encode(ProcessSubtype::where('process_type_id', $process_type_id)->lists('name', 'id'));
    }

}