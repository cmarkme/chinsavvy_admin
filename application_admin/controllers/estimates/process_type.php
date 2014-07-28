<?php

use Eloquent\ProcessType;

/**
*
*/
class Process_Type extends MY_Controller
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
		redirect('estimates/process_type/browse');
	}

	public function browse($add = false)
	{
		$this->load->helper('form_template');

		$pageDetails = array(
            'title' => 'Browse Process Types',
            'csstoload' => array('jquery.datatable'),
            'jstoloadinfooter' => array('datatables/js/jquery.dataTables', 'common/dataTables-init', 'application/estimates/ajax_add'),
            'content_view' => 'estimates/process_type/browse',
            'add' => $add === true,
            );

        $this->load->view('template/default', $pageDetails);
	}

	public function datatable()
	{
		echo ProcessType::getDataTable();
	}

	public function edit($id)
	{
		$this->load->helper('form_template');

		$process_type = ProcessType::find($id);

		$pageDetails = array(
            'title' => 'Edit Process Types',
            'csstoload' => array('jquery.datatable'),
            'jstoloadinfooter' => array('datatables/js/jquery.dataTables', 'common/dataTables-init', 'application/estimates/ajax_add'),
            'content_view' => 'estimates/process_type/edit',
            'process_type' => $process_type,
            );

		form_element::set_default_data($process_type->toArray());

        $this->load->view('template/default', $pageDetails);
	}

	public function process()
	{
		$id = $this->input->post('id');

		try
		{
			ProcessType::find($id)->update( $this->input->post() );
		}
		catch (Eloquent\ValidationException $e)
        {
            return $this->edit($id);
        }

		return redirect('estimates/process_type/browse');
	}

	public function process_add()
	{
		try
		{
			$process = ProcessType::create( $this->input->post() );
		}
		catch (Eloquent\ValidationException $e)
        {
        	return $this->browse(true);
        }

		redirect('estimates/process_type/edit/' . $process->id);
	}

	public function delete($id)
	{
		$process_type = ProcessType::find($id);

		$name = $process_type->name;

		$process_type->delete();

		echo json_encode(array(
			'id' => $id,
			'message' => "Process Type '$name' was successfully deleted",
			));
	}

}