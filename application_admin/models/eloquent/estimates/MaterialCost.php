<?php namespace Eloquent;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MaterialCost extends BaseModel {

	use PriceBreakTrait;

	protected $table = 'estimates_material_costs';

	protected $entityUrl = 'estimates/material_cost';

	protected $guarded = array();

	protected $fillable = array('code', 'material_type_id', 'material_grade_id',
		'form', 'measurement_unit_id', 'source', 'price_breaks');

	public function type()
	{
		return $this->belongsTo('Eloquent\MaterialType', 'material_type_id');
	}

	public function grade()
	{
		return $this->belongsTo('Eloquent\MaterialGrade', 'material_grade_id');
	}

	public function measurementUnit()
	{
		return $this->belongsTo('Eloquent\MeasurementUnit', 'measurement_unit_id');
	}

	public function getDataTable()
	{
		return MaterialCost::select(
				'estimates_material_costs.*',
				'estimates_material_types.name as type',
				'estimates_material_grades.name as grade',
				'estimates_measurement_units.name as unit',
				'cu.username as creator',
				'ru.username as revisor'
				)
			->leftJoin('users as cu', 'creation_user_id', '=', 'cu.id')
			->leftJoin('users as ru', 'revision_user_id', '=', 'ru.id')
			->leftJoin('estimates_material_types', 'material_type_id', '=', 'estimates_material_types.id')
			->leftJoin('estimates_material_grades', 'material_grade_id', '=', 'estimates_material_grades.id')
			->leftJoin('estimates_measurement_units', 'measurement_unit_id', '=', 'estimates_measurement_units.id')

			->getTable(array(
		    	array('estimates_material_costs.id', 'id'),
		    	array('estimates_material_costs.creation_date', 'creation_date'),
		    	array('estimates_material_costs.revision_date', 'expiring_revision_date'),
		    	array('estimates_material_types.name', 'type'),
		    	array('estimates_material_grades.name', 'grade'),
		    	'form',
		    	array('estimates_measurement_units.name', 'unit'),
		    	array(null, 'moq'),
		    	'source',
		    	array('cu.username', 'creator'),
		    	array('ru.username', 'revisor'),
		    	function($m) { return $m->getActionColumn(); }
		    )
		);
	}

}