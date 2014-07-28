<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MaterialGrade extends BaseModel {

	protected $table = 'estimates_material_grades';

	protected $entityUrl = 'estimates/material_grade';

	protected $guarded = array();

	protected $fillable = array('material_type_id', 'name');

	protected static $rules = array(
		array(
			'field' => 'name',
			'label' => 'Name',
			'rules' => 'required',
			)
		);

	public function type()
	{
		return $this->belongsTo('Eloquent\MaterialType', 'material_type_id');
	}

	public function costs()
	{
		return $this->hasMany('Eloquent\MaterialCost', 'material_grade_id');
	}

	public function isDeleteable()
	{
		return ! $this->costs()->count();
	}

	public function getDatatable($id)
	{
		return MaterialType::find($id)->grades()->select(
			'estimates_material_grades.*',
			'cu.username as creator',
			'ru.username as revisor'
			)

		->leftJoin('users as cu', 'creation_user_id', '=', 'cu.id')
		->leftJoin('users as ru', 'revision_user_id', '=', 'ru.id')

		->getTable(array(
	    	'name',
	    	'creator',
	    	'revisor',
	    	array('', function($m) { return $m->getActionColumn(); })
	    	)
		);
	}

	public static function getDropdown($material_type_id)
	{
		return MaterialGrade::where('material_type_id', $material_type_id)
			->lists('name', 'id');
	}

}