<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SavedPrice extends BaseModel {

	use PriceBreakTrait;

	protected $table = 'estimates_saved_prices';

	protected $guarded = array();

	protected $fillable = array('price_breaks_json');

	public function createForComponent(Component $component)
	{
		if ( ! $component instanceof Material and ! $component instanceof Process)
		{
			throw new \UnexpectedValueException('Component must be an instance of Eloquent\Material or Eloquent\Process');
		}

		$saved_price = new SavedPrice(array(
            'price_breaks_json' => $component->cost->price_breaks_json
            ));

        return $component->savedPrice()->save( $saved_price );
	}

}