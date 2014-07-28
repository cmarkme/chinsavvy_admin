<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SampleSize extends BaseModel {

	protected $table = 'qc_sample_sizes';

	public static function getForBatchQty($batch_size, $inspection_level = null)
	{
		if (in_array($inspection_level, array(QC_INSPECTION_LEVEL_TOTAL, QC_INSPECTION_LEVEL_OTHER)))
		{
			return $batch_size;
		}

		$sample_sizes = static::all();

		foreach ($sample_sizes as $sample_size)
		{
			if ($sample_size->max_batch_qty >= $batch_size) break;
		}

		switch ($inspection_level)
		{
			case QC_INSPECTION_LEVEL_A:
				return $batch_size * ($sample_size->A / 100);
			case QC_INSPECTION_LEVEL_B:
				return $batch_size * ($sample_size->B / 100);
			default:
				return $sample_size;
		}
	}

	public function getDisplayAttribute()
	{
		return 'A: ' . $this->A . ' | B: ' . $this->B;
	}
}