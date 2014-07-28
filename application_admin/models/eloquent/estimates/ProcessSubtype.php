<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ProcessSubtype extends BaseModel {

	protected $table = 'estimates_process_subtypes';

	protected $entityUrl = 'estimates/process_subtype';

	protected $guarded = array();

	protected $fillable = array('process_type_id', 'name');

	protected static $rules = array(
		array(
			'field' => 'name',
			'label' => 'Name',
			'rules' => 'required',
			)
		);

	public function type()
	{
		return $this->belongsTo('Eloquent\ProcessType', 'process_type_id');
	}

	public function costs()
	{
		return $this->hasMany('Eloquent\ProcessCost', 'process_subtype_id');
	}

	public function isDeleteable()
	{
		return ! $this->costs()->count();
	}

	public function getDatatable($id)
	{
		return ProcessType::find($id)->subtypes()->select(
			'estimates_process_subtypes.*',
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

	public static function getDropdown($process_type_id)
	{
		return ProcessSubtype::where('process_type_id', $process_type_id)
			->lists('name', 'id');
	}

}