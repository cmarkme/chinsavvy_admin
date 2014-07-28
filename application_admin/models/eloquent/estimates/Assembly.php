<?php namespace Eloquent;


if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Assembly extends Component {

	public function children($type = 'Eloquent\Component')
	{
		return $this->belongsToMany($type, 'estimates_assembly_component', 'assembly_id', 'component_id')
			->withPivot('qty')->orderBy('type')->orderBy('name');
	}

	public function parts()
	{
		return $this->children('Eloquent\Part');
	}

	public function materials()
	{
		return $this->children('Eloquent\Material');
	}

	public function processes()
	{
		return $this->children('Eloquent\Process');
	}

	public function subassemblies()
	{
		return $this->children('Eloquent\Assembly');
	}

	public function getLCDC($qty = 1)
	{
		global $LCDC;

		if ($this instanceof Product)
		{
			$LCDC = array();
		}

		foreach ($this->children as $component)
		{
			$itemQty = $qty * $component->pivot->qty;

			if ($component instanceof Assembly)
			{
				$LCDC += $component->getLCDC($itemQty);
			}
			else
			{
				if (isset($LCDC[$component->id]))
				{
					$LCDC[$component->id]->qty += $itemQty;
				}
				else
				{
					$LCDC[$component->id] = $component;
					$LCDC[$component->id]->qty = $itemQty;
				}
			}
		}

		return $LCDC;
	}

	public function groupLCDCByType($qty = 1)
	{
		$components = $this->getLCDC($qty);

		$result = array_fill_keys(array('Eloquent\Material', 'Eloquent\Process', 'Eloquent\Part'), array());

		foreach ($components as $key => $component)
		{
			$result[get_class($component)][$key] = $component;
		}

		return $result;
	}

	public function getTotalPriceForQty($qty)
	{
		$components = $this->getLCDC($qty);

		$total = 0;
		foreach ($components as $component)
		{
			$total += $component->cost->getLinePriceForQty($component->qty);
		}

		return $total;
	}

	public function getUseableSubassemblies()
	{
		$ancestors = $this->getAncestors()->lists('id');
		$children = $this->children->lists('id');

		$exclude = array_unique(array_merge($ancestors, $children));

		$exclude[] = $this->id;

		return Assembly::where('estimate_id', $this->estimate_id)
			->whereNotIn('id', $exclude)
			->get();
	}

	public function getUseableParts()
	{
		$children = $this->parts->lists('id');

		if (empty($children)) $children = array(0);

		return Part::where('estimate_id', $this->estimate_id)
			->whereNotIn('id', $children)
			->with('cost')
			->get();
	}

	public function getUseableMaterials()
	{
		$children = $this->materials->lists('id');

		if (empty($children)) $children = array(0);

		return Material::where('estimate_id', $this->estimate_id)
			->whereNotIn('id', $children)
			->with('cost')
			->get();
	}

	public function getUseableProcesses()
	{
		$children = $this->processes->lists('id');

		if (empty($children)) $children = array(0);

		return Process::where('estimate_id', $this->estimate_id)
			->whereNotIn('id', $children)
			->with('cost')
			->get();
	}


}