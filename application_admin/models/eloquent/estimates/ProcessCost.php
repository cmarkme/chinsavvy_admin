<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ProcessCost extends BaseModel {

	use PriceBreakTrait;

	protected $table = 'estimates_process_costs';

	protected $entityUrl = 'estimates/process_cost';

	protected $guarded = array();

	protected $fillable = array('code', 'process_type_id',
		'process_subtype_id', 'machine_size', 'action', 'source', 'price_breaks');

	public function type()
	{
		return $this->belongsTo('Eloquent\ProcessType', 'process_type_id');
	}

	public function subtype()
	{
		return $this->belongsTo('Eloquent\ProcessSubtype', 'process_subtype_id');
	}

	public function getDataTable()
	{
		return ProcessCost::select(
				'estimates_process_costs.*',
				'estimates_process_types.name as type',
				'estimates_process_subtypes.name as subtype',
				'cu.username as creator',
				'ru.username as revisor'
				)
			->leftJoin('users as cu', 'creation_user_id', '=', 'cu.id')
			->leftJoin('users as ru', 'revision_user_id', '=', 'ru.id')
			->leftJoin('estimates_process_types', 'process_type_id', '=', 'estimates_process_types.id')
			->leftJoin('estimates_process_subtypes', 'process_subtype_id', '=', 'estimates_process_subtypes.id')

			->getTable(array(
		    	array('estimates_process_costs.id', 'id'),
		    	array('estimates_process_costs.creation_date', 'creation_date'),
		    	array('estimates_process_costs.revision_date', 'expiring_revision_date'),
		    	array('estimates_process_types.name', 'type'),
		    	array('estimates_process_subtypes.name', 'subtype'),
		    	'machine_size',
		    	'action',
		    	'source',
		    	array('cu.username', 'creator'),
		    	array('ru.username', 'revisor'),
		    	function($m) { return $m->getActionColumn(); }
		    )
		);
	}

}