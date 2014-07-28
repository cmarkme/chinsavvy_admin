<?php

use Eloquent\Estimate;

/**
* Written by Christopher Reid
* chrisreiduk@gmail.com
*/
class Fixed_Cost extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
        require_capability('estimates:user');
        $this->load->library('form_validation');
        $this->load->helper('currency');
        $this->config->set_item('replacer', array('estimates' => array('estimate|Estimates')));
        $this->config->set_item('exclude', array('browse'));
	}

	public function index()
	{
        redirect('estimates/fixed_cost/browse');
    }

	public function browse($id)
	{
        $fixed_costs = Estimate::find($id)->fixed_costs;

		$pageDetails = array(
            'title' => 'Browse Fixed Costs',
            'csstoload' => array(),
            'jstoloadinfooter' => array(),
            'content_view' => 'estimates/fixed_cost/browse',
            'fixed_costs' => $fixed_costs,
            'active' => 'fixed_cost',
            'estimate_id' => $id,
            );

        $this->load->view('template/default', $pageDetails);
	}

    public function add($estimate_id)
    {
        $this->load->helper('form_template');

        $pageDetails = array(
            'title' => 'Add Fixed Cost',
            'csstoload' => array(),
            'jstoloadinfooter' => array(),
            'content_view' => 'estimates/fixed_cost/add',
            'estimate_id' => $estimate_id,
            'types_dropdown' => with( new Eloquent\FixedCost)->getTypesDropdown(),
            );

        $this->load->view('template/default', $pageDetails);
    }

    public function edit($fixed_cost_id)
    {
        $this->load->helper('form_template');

        $fixed_cost = Eloquent\FixedCost::findOrFail($fixed_cost_id);

        $pageDetails = array(
            'title' => 'Edit Fixed Cost',
            'csstoload' => array(),
            'jstoloadinfooter' => array(),
            'content_view' => 'estimates/fixed_cost/edit',
            'fixed_cost_id' => $fixed_cost_id,
            'types_dropdown' => with( new Eloquent\FixedCost)->getTypesDropdown(),
            );

        form_element::set_default_data($fixed_cost->toArray());

        $this->load->view('template/default', $pageDetails);
    }

    public function insert($estimate_id)
    {
        $input = $this->input->post() + compact('estimate_id');

        try
        {
            Eloquent\FixedCost::create( $input );
        }
        catch (Eloquent\ValidationException $e)
        {
            return $this->add($estimate_id);
        }

        return redirect('estimates/fixed_cost/browse/' . $estimate_id);
    }

    public function update($fixed_cost_id)
    {
        try
        {
            $fixed_cost = Eloquent\FixedCost::find($fixed_cost_id);
            $fixed_cost->update( $this->input->post() );
        }
        catch (Eloquent\ValidationException $e)
        {
            return $this->edit($fixed_cost_id);
        }

        return redirect('estimates/fixed_cost/browse/' . $fixed_cost->estimate_id);
    }

    public function delete($fixed_cost_id)
    {
        $fixed_cost = Eloquent\FixedCost::find($fixed_cost_id);

        $estimate_id = $fixed_cost->estimate_id;

        $fixed_cost->delete();

        return redirect('estimates/fixed_cost/browse/' . $estimate_id);
    }

}