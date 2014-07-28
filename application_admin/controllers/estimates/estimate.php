<?php

/**
* Written by Christopher Reid
* chrisreiduk@gmail.com
*/
class Estimate extends MY_Controller
{
	public function __construct()
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
        redirect('estimates/estimate/browse');
    }

	public function add($enquiry_id = null)
	{
        $this->config->set_item('replacer', array('estimates' => array('estimate|Estimates'), 'add' => 'Create a new Estimate'));
		$this->load->helper('form_template');
        $this->load->helper('date');

        $this->load->model('enquiries/enquiry_model');
        $this->load->model('country_model');

        $dropdowns['enquiries'] = $this->enquiry_model->get_dropdown();

        // An optional enquiry_id can be passed to this page to pre-fill company, enquirer and product details
        if ($enquiry_id)
        {
            $enquiry_data = $this->enquiry_model->get_values($enquiry_id);
            $enquiry_data['address_country_name'] = (!empty($enquiry_data['address_country_id']))
                ? $this->country_model->get_name($enquiry_data['address_country_id']) : '';
        }

        form_element::$default_data = array('enquiry_prefill' => $enquiry_id, 'enquiry_id' => $enquiry_id);

        $pageDetails = array(
            'title' => 'Add Estimates',
            'dropdowns' => $dropdowns,
            'csstoload' => array(),
            'jstoloadinfooter' => array(),
            'content_view' => 'estimates/add',
            'enquiry_id' => $enquiry_id,
            'enquiry_data' => $enquiry_id ? $enquiry_data : null,
            );

        $this->load->view('template/default', $pageDetails);
	}

	/**
     * Browse all estimates grouped by enquiry
     */
    public function browse()
	{
		$pageDetails = array(
            'title' => 'Browse all Estimates',
            'csstoload' => array('jquery.datatable'),
            'jstoloadinfooter' => array('datatables/js/jquery.dataTables', 'common/dataTables-init'),
            'content_view' => 'estimates/browse',
            'user_id' => $this->session->userdata('user_id'),
            );

        $this->load->view('template/default', $pageDetails);
	}

    /**
     * Browse all estimates of a given enquiry_id
     */
    public function versions($enquiry_id)
    {
        $pageDetails = array(
            'title' => 'Browse Estimates for Enquiry Ref# ' . $enquiry_id,
            'csstoload' => array('jquery.datatable'),
            'jstoloadinfooter' => array('datatables/js/jquery.dataTables', 'common/dataTables-init', 'application/estimates/estimate'),
            'content_view' => 'estimates/versions',
            'enquiry_id' => $enquiry_id,
            );

        $this->load->view('template/default', $pageDetails);
    }

    public function datatable($enquiry_id = null)
    {
        if ($enquiry_id)
        {
            echo Eloquent\Estimate::getDataTableForEnquiryId($enquiry_id);
        }
        else
        {
            echo Eloquent\Estimate::getDataTable();
        }
    }

    public function edit($id)
    {
        $this->load->helper('form_template');
        $this->load->model('enquiries/enquiry_model');
        $this->load->model('country_model');

        $estimate = Eloquent\Estimate::findOrFail($id);

        $enquiry_data = $this->enquiry_model->get_values($estimate->enquiry_id);
        $enquiry_data['address_country_name'] = (!empty($enquiry_data['address_country_id'])) ? $this->country_model->get_name($enquiry_data['address_country_id']) : '';

        $pageDetails = array(
            'title' => 'Edit Bill Of Materials - ' . $estimate->name,
            'csstoload' => array(),
            'jstoloadinfooter' => array(),
            'content_view' => 'estimates/edit',
            'active' => 'edit',
            'estimate_id' => $id,
            'estimate' => $estimate,
            'enquiry_data' => $enquiry_data,
            );

        form_element::set_default_data($estimate->toArray());

        $this->load->view('template/default', $pageDetails);
    }

    public function insert($enquiry_id)
    {
        $input = $this->input->post();

        try
        {
            $estimate = Eloquent\Estimate::create( $input );

            $product = new Eloquent\Product( $input );

            $estimate->product()->save( $product );
        }
        catch (Eloquent\ValidationException $e) {
            return $this->add($enquiry_id);
        }

        return redirect('estimates/estimate/edit/' . $estimate->id);
    }

    public function update($id)
    {
        $input = $this->input->post();

        try
        {
            Eloquent\Estimate::find($id)->update( $this->input->post() );
        }
        catch (Eloquent\ValidationException $e) {
            return $this->edit($id);
        }

        return redirect('estimates/estimate/edit/' . $id);
    }

    public function ajax_qty()
    {
        $parent = Eloquent\Component::find($this->input->post('parent_id'));
        $component = Eloquent\Component::find($this->input->post('component_id'));

        $parent->children()->detach($component);
        $parent->children()->attach($component, array('qty' => $this->input->post('qty')));
    }

    public function duplicate($id)
    {
        if ( ! $this->input->is_ajax_request()) {
           exit('No direct script access allowed');
        }

        $estimate = Eloquent\Estimate::find($id);

        $estimateCopy = $estimate->replicate();

        $estimateCopy->description .= "\n*** Duplicated from version {$estimate->version} on " . date('d/m/Y \a\t H:i:s') . " ***\n";

        unset($estimateCopy->creation_date, $estimateCopy->revision_date, $estimateCopy->version);

        $estimateCopy->save();

        /**
         * Now we will copy the fixed costs
         */

        foreach($estimate->fixed_costs as $fixed_cost)
        {
            $fixed_costCopy = $fixed_cost->replicate();

            $estimateCopy->fixedCosts()->save( $fixed_costCopy );
        }

        /**
         * At this point we copy all of the components
         * along with their many to many relationships
         */

        foreach($estimate->components as $component)
        {
            $componentCopy = $component->replicate();

            /**
             * If the component is a part, we copy the instance of
             * PartCost too so that we are dealing with a seperate copy.
             * Any editing of this copy will not affect previous versions.
             */
            if ($componentCopy instanceof Eloquent\Part)
            {
                $costCopy = $componentCopy->cost->replicate();

                $costCopy->save();

                $componentCopy->cost()->associate( $costCopy );
            }

            $estimateCopy->components()->save( $componentCopy );

            if ($componentCopy instanceof Eloquent\Material
                or $componentCopy instanceof Eloquent\Process)
            {
                Eloquent\SavedPrice::createForComponent($componentCopy);
            }

            $map[$component->id] = $componentCopy->id;
        }

        $db = require(APPPATH . 'models/eloquent/connection.php');

        $links = $db->table('estimates_assembly_component')
            ->whereIn('assembly_id', array_keys($map))
            ->orWhereIn('component_id', array_keys($map))
            ->get();

        foreach ($links as &$link)
        {
            unset($link['id']);
            $link['assembly_id'] = $map[$link['assembly_id']];
            $link['component_id'] = $map[$link['component_id']];
        }

        $db->table('estimates_assembly_component')->insert($links);

        //echo json_encode('success', 200);
    }


}