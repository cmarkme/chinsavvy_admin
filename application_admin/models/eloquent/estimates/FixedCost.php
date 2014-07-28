<?php namespace Eloquent;

use Illuminate\Database\Connection as DB;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FixedCost extends BaseModel {

	public static $types = array(
		1 => 'Tooling',
		2 => 'QA',
		3 => 'Transport',
		4 => 'Other',
		);

	protected $table = 'estimates_fixed_costs';

	protected $entityUrl = 'estimates/fixed_cost';

	protected $guarded = array();

	protected static $rules = array(
		array(
			'field' => 'type',
			'label' => 'Type',
			'rules' => 'required',
			),
		array(
			'field' => 'description',
			'label' => 'Description',
			'rules' => 'required',
			),
		array(
			'field' => 'cost',
			'label' => 'Cost',
			'rules' => 'required',
			),
		);

	protected $fillable = array('estimate_id', 'type', 'description', 'cost');

	public function getCostFormattedAttribute()
	{
		return currency_format($this->cost);
	}

	public function getTypesDropdown()
	{
		return array('' => '-- Please Select --') + static::$types;
	}

	public function isDeleteable()
	{
		return true;
	}

	public function scopeGrouped($query)
	{
		return $query->select('type', DB::raw('sum(cost) as cost'))->groupBy('type');
	}


}