<?php

/**
 * QC Sample Sizes class
 * @package controllers
 */
class Sample_Size extends MY_Controller {

	public function index()
	{
		$sample_sizes = Eloquent\SampleSize::all();

		$pageDetails = array(
            'title' => 'Sample Sizes',
            'csstoload' => array(),
            'jstoloadinfooter' => array('application/qc/sample_size'),
            'content_view' => 'qc/sample_size',
            'sample_sizes' => $sample_sizes,
            );

        $this->load->view('template/default', $pageDetails);
	}

	public function update()
	{
		$input = $this->input->post();
		Eloquent\SampleSize::truncate();

		$sample_sizes = array();
		foreach($input['qty'] as $key => $value)
		{
			$sample_sizes[$key]['max_batch_qty'] = $value ?: null;
			$sample_sizes[$key]['a'] = $input['a'][$key];
			$sample_sizes[$key]['b'] = $input['b'][$key];
		}

		Eloquent\SampleSize::insert($sample_sizes);

		redirect('/qc/sample_size');
	}

}