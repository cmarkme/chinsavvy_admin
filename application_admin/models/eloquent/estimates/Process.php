<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Process extends Component {

	public function cost()
	{
		return $this->belongsTo('Eloquent\ProcessCost', 'type_id');
	}

}