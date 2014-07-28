<?php namespace Eloquent;

use Illuminate\Database\Eloquent\Collection;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Component extends BaseModel {

	protected $table = 'estimates_components';

	protected $stiClassField = 'type';
	protected $stiBaseClass = 'Eloquent\Component';

	protected $fillable = array('estimate_id', 'type_id', 'name', 'description');

	protected static $rules = array(
		array(
			'field' => 'estimate_id',
			'label' => 'estimate_id',
			'rules' => 'required|is_natural_no_zero',
			),
		array(
			'field' => 'name',
			'label' => 'Name',
			'rules' => 'required',
			),
		array(
			'field' => 'type',
			'label' => 'Type',
			'rules' => 'required',
			),
		);

	public function parents()
	{
		return $this->belongsToMany('Eloquent\Component', 'estimates_assembly_component', 'component_id', 'assembly_id');
	}

	public function savedPrice()
	{
		return $this->hasOne('Eloquent\SavedPrice', 'component_id');
	}

	public function getPartDropdown($estimate_id)
	{
		return array('' => '-- New Part --') + Component::where('estimate_id', $estimate_id)
			->where('type', 'Eloquent\Part')->lists('name', 'id');
	}

	public function getTypeForHumansAttribute()
	{
		return substr($this->type, 9);
	}

	public function getAncestors()
	{
		$ancestors = new Collection;

		foreach ($this->parents as $component)
		{
			$ancestors->add($component);

			$ancestors = $ancestors->merge( $component->getAncestors() );
		}

		return $ancestors;
	}

	/**
	 * returns true if the price stored against the stored price break
	 * differs from the up-to-date cost on the costs DB
	 * @param  [int] $qty optional. If ommitted it will compare all price breaks
	 *                     If included it will compare the price for the given qty
	 * @return [bool]
	 */
	public function hasPriceChanged($qty = null)
	{
		if ( ! is_null($qty) )
		{
			return $this->saved_price->getUnitPriceForQty($qty)
				!== $this->cost->getUnitPriceForQty($qty);
		}
		else
		// if $qty is omited we will just compare the json strings of each
		{
			return $this->saved_price->price_breaks !== $this->cost->price_breaks;
		}
	}

}
