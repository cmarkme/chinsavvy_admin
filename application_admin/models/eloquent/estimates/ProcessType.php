<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ProcessType extends BaseModel {

	protected $table = 'estimates_process_types';

	protected $entityUrl = 'estimates/process_type';

	protected $guarded = array();

	protected $fillable = array('name');

	protected static $rules = array(
		array(
			'field' => 'name',
			'label' => 'Name',
			'rules' => 'required',
			)
		);

	public function subtypes()
	{
		return $this->hasMany('Eloquent\ProcessSubtype');
	}

	public function getSubtypesFormattedAttribute()
	{
		return implode('<br>', $this->subtypes->fetch('name')->toArray());
	}

	public function getDataTable()
	{
		return ProcessType::select(
			'estimates_process_types.*',
			'cu.username as creator',
			'ru.username as revisor'
			)

		->with('subtypes')

		->leftJoin('users as cu', 'creation_user_id', '=', 'cu.id')
		->leftJoin('users as ru', 'revision_user_id', '=', 'ru.id')

		->getTable(array(
	    	array('estimates_process_types.name', 'name'),
	    	array('', 'subtypes_formatted'),
	    	array('cu.username', 'creator'),
	    	array('ru.username', 'revisor'),
	    	array('', function($m) { return $m->getActionColumn(); })
	    	)
		);
	}

	public function isDeleteable()
	{
		return $this->subtypes->count() === 0;
	}

}