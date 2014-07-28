<?php

use Eloquent\Estimate;
use Eloquent\Component;
use Eloquent\Assembly;
use Eloquent\Part;
use Eloquent\PartCost;
use Eloquent\Material;
use Eloquent\Process;
use Eloquent\SavedPrice;
use Eloquent\ValidationException;

/**
* Written by Christopher Reid
* chrisreiduk@gmail.com
*/
class BOM extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('currency');
    }

    public function diagram($id)
    {
        $estimate = Estimate::findOrFail($id);

        $pageDetails = array(
            'title' => 'BOM Diagram',
            'content_view' => 'estimates/bom/diagram',
            'jstoloadinfooter' => array(),
            'estimate' => $estimate,
            'estimate_id' => $estimate->id,
            'active' => 'diagram',
        );

        $this->load->view('template/default', $pageDetails);
    }

    public function summary($id, $qty = 1)
    {
        $estimate = Estimate::findOrFail($id);

        $pageDetails = array(
            'title' => 'Summary',
            'content_view' => 'estimates/bom/summary',
            'jstoloadinfooter' => array(),
            'estimate' => $estimate,
            'estimate_id' => $estimate->id,
            'active' => 'summary',
            'qty' => $qty,
            'components' => $estimate->product->groupLCDCByType($qty),
            'fixed_costs_grouped' => $estimate->fixedCosts()->grouped()->lists('cost', 'type'),
        );

        $this->load->view('template/default', $pageDetails);
    }

    public function wastebin($id)
    {
        $estimate = Estimate::findOrFail($id);

        $pageDetails = array(
            'title' => 'Waste Bin',
            'content_view' => 'estimates/bom/wastebin',
            'jstoloadinfooter' => array(),
            'estimate' => $estimate,
            'estimate_id' => $estimate->id,
            'active' => 'wastebin',
        );

        $this->load->view('template/default', $pageDetails);
    }

    public function build($id)
    {
        if (func_num_args() === 1)
        {
            $estimate = Estimate::findOrFail($id);

            return redirect('estimates/bom/build/' . $id . '/' . $estimate->product->id);
        }

        $args = func_get_args();

        $url = 'estimates/bom/build/' . array_shift($args);

        $components = Component::whereIn('id', $args)->get();

        $breadcrumb = array();
        foreach ($args as $key => $id)
        {
            $url .= '/' . $id;
            $component = $components->find($id);
            if ($key === count($args) - 1)
            {
                $breadcrumb[] = $component->name;
            }
            else
            {
                $breadcrumb[] = '<a href="' . $url . '">' . $component->name . '</a>';
            }
        }
        $breadcrumb = implode(' &rarr; ', $breadcrumb);

        $this->load->helper('form_template');

        // $component will be the last component in the uri
        // therefore the one we're interested in
        switch (get_class($component))
        {
            case 'Eloquent\Product':
            case 'Eloquent\Assembly':
                $this->load->model('codes/supplier_model');
                $pageDetails = array(
                    'title' => (count($args) > 1 ? 'View Sub-Assembly: ' : 'View Product: ') . $component->name,
                    'content_view' => 'estimates/bom/assembly',
                    'supplier_dropdown' => $this->supplier_model->get_dropdown_data(),
                );
                break;
            case 'Eloquent\Material':
                $pageDetails = array(
                    'title' => 'View Material: ' . $component->name,
                    'content_view' => 'estimates/bom/material',
                );
                $component->load('cost.type', 'cost.grade');
                break;
            case 'Eloquent\Process':
                $pageDetails = array(
                    'title' => 'View Process: ' . $component->name,
                    'content_view' => 'estimates/bom/process',
                );
                $component->load('cost.type', 'cost.subtype');
                break;
            case 'Eloquent\Part':
                $pageDetails = array(
                    'title' => 'View Part: ' . $component->name,
                    'content_view' => 'estimates/bom/part',
                );
                $component->load('cost');
                break;
            default:
                throw new UnexpectedValueException();
        }

        $pageDetails += array(
            'jstoloadinfooter' => array('application/estimates/price_breaks', 'application/estimates/ajax_dropdowns', 'application/estimates/modal'),
            'estimate_id' => $component->estimate_id,
            'active' => 'build',
            'component' => $component,
            'breadcrumb' => $breadcrumb,
            );

        $this->load->view('template/default', $pageDetails);
    }

    public function add_subassembly($parent_id)
    {
        $parent = Component::findOrFail($parent_id);

        if ($id = $this->input->post('id'))
        {
            $subassembly = Assembly::findOrFail($id);
        }
        else
        {
            try
            {
                $subassembly = Assembly::create( $this->input->post() + array('estimate_id' => $parent->estimate_id) );
            }
            catch (ValidationException $e)
            {
                return;
            }
        }

        $parent->children()->attach($subassembly, array('qty' => $this->input->post('qty')));
    }

    public function add_part($parent_id)
    {
        $parent = Component::findOrFail($parent_id);

        if ($id = $this->input->post('id'))
        {
            $part = Part::findOrFail($id);
        }
        else
        {
            $part_cost = PartCost::create( $this->input->post() );

            $part = new Part( $this->input->post() + array('estimate_id' => $parent->estimate_id) );

            $part->cost()->associate( $part_cost )->save();
        }

        $parent->children()->attach($part, array('qty' => $this->input->post('qty')));
    }

    public function add_material($parent_id)
    {
        $parent = Component::findOrFail($parent_id);

        if ($id = $this->input->post('id'))
        {
            $material = Material::findOrFail($id);
        }
        else
        {
            $material = Material::create( $this->input->post()
                + array('estimate_id' => $parent->estimate_id));

            SavedPrice::createForComponent($material);
        }

        $parent->children()->attach($material, array('qty' => $this->input->post('qty')));

    }

    public function add_process($parent_id)
    {
        $parent = Component::findOrFail($parent_id);

        if ($id = $this->input->post('id'))
        {
            $process = Process::findOrFail($id);
        }
        else
        {
            $process = Process::create( $this->input->post()
                + array('estimate_id' => $parent->estimate_id));

            SavedPrice::createForComponent($process);
        }

        $parent->children()->attach($process, array('qty' => $this->input->post('qty')));

    }

    public function detach($parent_id, $child_id)
    {
        $parent = Component::findOrFail($parent_id);

        $parent->children()->detach($child_id);
    }

    public function update_qty($parent_id, $child_id)
    {
        $parent = Component::findOrFail($parent_id);

        $parent->children()->detach($child_id);
        $parent->children()->attach($child_id, array('qty' => $this->input->post('qty')));
    }

}