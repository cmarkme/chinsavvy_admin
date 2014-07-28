<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PartCost extends BaseModel {

	use PriceBreakTrait;

	protected $table = 'estimates_part_costs';

	protected $fillable = array('source', 'code', 'price_breaks',);

	public function part()
	{
		return $this->hasOne('Eloquent\Part');
	}

}