<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Material extends Component {

	public function cost()
	{
		return $this->belongsTo('Eloquent\MaterialCost', 'type_id');
	}

}