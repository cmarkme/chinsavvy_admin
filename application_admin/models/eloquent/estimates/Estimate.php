<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Illuminate\Database\Connection as DB;

class Estimate extends BaseModel {

	protected $entityUrl = 'estimates/estimate';

	protected $guarded = array();

	protected static $rules = array(
		array(
			'field' => 'name',
			'label' => 'Name',
			'rules' => 'required',
			),
		array(
			'field' => 'price_breaks',
			'label' => 'Price Breaks',
			'rules' => 'required',
			),
		);

	protected $fillable = array('enquiry_id', 'name', 'description', 'price_breaks');

	public static $actionColumns = array('edit', 'duplicate');

	public function product()
	{
		return $this->hasOne('Eloquent\Product', 'estimate_id');
	}

	public function getPriceBreaks()
	{
		return explode(',', $this->price_breaks);
	}

	public function getLatestVersion()
	{
		return $this->where('enquiry_id', $this->enquiry_id)->max('version') ?: 0;
	}

	public function getNextVersion()
	{
		return $this->getLatestVersion() + 1;
	}

	public function components()
	{
		return $this->hasMany('Eloquent\Component', 'estimate_id');
	}

	public function fixedCosts()
	{
		return $this->hasMany('Eloquent\FixedCost', 'estimate_id')->orderBy('type');
	}

	public function getDescriptionHtmlAttribute()
	{
		return nl2br($this->description);
	}

	public function setPriceBreaksAttribute($value)
	{
		$price_breaks = explode(',', str_replace(' ', '', $value));

		sort($price_breaks);

		$this->attributes['price_breaks'] = implode(',', $price_breaks);
	}

	public static function getNextVersionFromEnquiryId($enquiry_id)
	{
		return static::where('enquiry_id', $enquiry_id)->max('version') + 1;
	}

	/**
	 * Perform a model insert operation.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder
	 * @return bool
	 */
	protected function performInsert($query)
	{
		$this->attributes['version'] = $this->getNextVersion();

		return parent::performInsert($query);
	}

	/**
	 * Get the total estimate price for each price break
	 * @return [type] [description]
	 */
	public function getPrices()
	{
		$priceBreaks = $this->getPriceBreaks();

		$fixed_costs = $this->fixedCosts()->sum('cost');

		$tooling = $this->product->getToolingTotal();

		$result = array();
		foreach ($priceBreaks as $qty)
		{
			$BOM = $this->product->getTotalPriceForQty($qty);

			$result[$qty] = ($BOM + $fixed_costs + $tooling) / $qty;
		}

		return $result;
	}

	public function getUnusedComponents()
	{
		return $this->components->has('children');
	}

	public function getDataTable()
	{
		Estimate::$actionColumns = array(
			'versions',
		);

		return Estimate::select(
				'e.*',
				'p.title',
				'p.description',
				DB::raw('max(version) as latest_version')
				)

			->leftJoin('enquiries as e', 'enquiry_id', '=', 'e.id')
			->leftJoin('enquiries_enquiry_products as p', 'e.enquiry_product_id', '=', 'p.id')

			->groupBy('enquiry_id')

			->getTable(array(
		    	array('e.creation_date', 'creation_date'),
		    	array('e.id', 'id'),
		    	array('p.title', 'title'),
		    	array('p.description', 'description'),
		    	array(null, 'latest_version'),
		    	function($m) { return $m->getActionColumn(); }
		    )
		);
	}

	public function getDataTableForEnquiryId($enquiry_id)
	{
		return Estimate::select(
				'estimates.*',
				'cu.username as creator',
				'ru.username as revisor'
				)
			->leftJoin('users as cu', 'creation_user_id', '=', 'cu.id')
			->leftJoin('users as ru', 'revision_user_id', '=', 'ru.id')

			->where('estimates.enquiry_id', $enquiry_id)

			->getTable(array(
		    	array('estimates.creation_date', 'creation_date'),
		    	'version',
		    	'name',
		    	array('description', 'descriptionHtml'),
		    	array('cu.username', 'creator'),
		    	array('ru.username', 'revisor'),
		    	function($m) { return $m->getActionColumn(); }
		    )
		);
	}

}