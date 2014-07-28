<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Supplier extends BaseModel {

	protected $table = 'companies';

	public function scopeSupplier()
	{
		return $query->where('role', COMPANY_ROLE_SUPPLIER);
	}

}