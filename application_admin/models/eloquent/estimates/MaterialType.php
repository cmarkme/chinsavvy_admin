<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MaterialType extends BaseModel {

	protected $table = 'estimates_material_types';

	protected $entityUrl = 'estimates/material_type';

	protected $guarded = array();

	protected $fillable = array('name');

	protected static $rules = array(
		array(
			'field' => 'name',
			'label' => 'Name',
			'rules' => 'required',
			)
		);

	public function grades()
	{
		return $this->hasMany('Eloquent\MaterialGrade');
	}

	public function getGradesFormattedAttribute()
	{
		return implode('<br>', $this->grades->fetch('name')->toArray());
	}

	public function getDataTable()
	{
		return MaterialType::select(
			'estimates_material_types.*',
			'cu.username as creator',
			'ru.username as revisor'
			)

		->with('grades')

		->leftJoin('users as cu', 'creation_user_id', '=', 'cu.id')
		->leftJoin('users as ru', 'revision_user_id', '=', 'ru.id')

		->getTable(array(
	    	array('estimates_material_types.name', 'name'),
	    	array('', 'grades_formatted'),
	    	array('cu.username', 'creator'),
	    	array('ru.username', 'revisor'),
	    	array('', function($m) { return $m->getActionColumn(); })
	    	)
		);
	}

	public function isDeleteable()
	{
		return $this->grades->count() === 0;
	}

}