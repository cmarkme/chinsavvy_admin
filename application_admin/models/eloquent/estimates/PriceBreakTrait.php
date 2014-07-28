<?php namespace Eloquent;

/**
*
*/
trait PriceBreakTrait
{
	public function supplier()
	{
		return $this->belongsTo('Eloquent\Supplier', 'supplier_id');
	}

	public function getPriceBreaksAttribute()
	{
		return (array) json_decode($this->attributes['price_breaks'], true);
	}

	public function setPriceBreaksAttribute($priceBreaks)
	{
		$combined = array_combine($priceBreaks['qty'], $priceBreaks['price']);

		$combined = array_filter($combined);

		ksort($combined);

		$this->attributes['price_breaks'] = json_encode($combined);
	}

	public function getPriceBreaksJsonAttribute()
	{
		return $this->attributes['price_breaks'];
	}

	public function setPriceBreaksJsonAttribute($json)
	{
		$this->attributes['price_breaks'] = $json;
	}

	public function getMoqAttribute()
	{
		return current(array_keys($this->price_breaks));
	}

	public function isBelowMoq($qty)
	{
		return $qty < $this->moq;
	}

	public function getUnitPriceForQty($qty)
	{
		if ( $this->isBelowMoq($qty) )
		{
			return $this->price_breaks[$this->moq];
		}

		foreach (array_reverse($this->price_breaks, true) as $pbQty => $price)
		{
			if ($pbQty <= $qty) return $price;
		}

		// We return false if there is no price break for this quantity
		// as this is below the minimum order quantity
		// return false;
	}

	public function getLinePriceForQty($qty)
	{
		$unitPrice = $this->getUnitPriceForQty($qty);

		if ($unitPrice) return $qty * $unitPrice;

		$priceBreaks = $this->price_breaks;

		$moqPrice = reset($priceBreaks);
		$moqQty = key($priceBreaks);

		return $moqQty * $moqPrice;
	}

}