<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Part extends Component {

	public function cost()
	{
		return $this->belongsTo('Eloquent\PartCost', 'type_id');
	}

}