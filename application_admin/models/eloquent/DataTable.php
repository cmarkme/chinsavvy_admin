<?php namespace Eloquent;
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('connection.php');

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;

function input_get($key) {
	return isset($_POST[$key]) ? $_POST[$key] : false;
}

class DataTable extends Eloquent
{
	public function newQuery($excludeDeleted = true)
	{
		$builder = new MyBuilder($this->newBaseQueryBuilder());

		// Once we have the query builders, we will set the model instances so the
		// builder can easily access any information it may need from the model
		// while it is constructing and executing various queries against it.
		$builder->setModel($this)->with($this->with);

		if ($excludeDeleted and $this->softDelete)
		{
			$builder->whereNull($this->getQualifiedDeletedAtColumn());
		}

		return $builder;
	}

}

class MyBuilder extends Builder {

	public $iTotalDisplayRecords;

	/**
	 *	Prepares variables according to Datatables parameters
	 *
	 *	@return null
	 */

	public function getTable(array $columns)
	{
		$this->columns = $columns;

		$model = $this->getModel();

		$iTotalRecords = $model->count();

		$this->filtering();
		$this->paging();
		$this->ordering();

		$query = $this->getQuery()->toSql();

		$rows = $this->processRows($columns);

		return json_encode(array(
			"sEcho" => intval(input_get('sEcho')),
			"iTotalRecords" => intval($iTotalRecords),
			"iTotalDisplayRecords" => intval($this->iTotalDisplayRecords),
			"aaData" => $rows,
			"query" => @$query
			)
		,  JSON_PRETTY_PRINT);
	}

	/**
	 *	Datatable paging
	 *
	 *	@return null
	 */
	protected function paging()
	{
		if(input_get('iDisplayStart') !== false && input_get('iDisplayLength') != -1)
		{
			$this->skip(input_get('iDisplayStart'))->take(input_get('iDisplayLength',10));
		}
	}

	/**
	 *	Datatable ordering
	 *
	 *	@return null
	 */
	protected function ordering()
	{
		if(input_get('iSortCol_0') !== false)
		{

			for ( $i = 0; $i < intval(input_get('iSortingCols')) ; $i++ )
			{
				$sortCol = intval(input_get('iSortCol_'.$i));

				if ( input_get('bSortable_'.$sortCol) == "true" )
				{
					if ($columnName = $this->getColumnName($this->columns[$sortCol]))
					{
						$this->orderBy($columnName , input_get('sSortDir_'.$i));
					}
				}
			}

		}
	}

	/**
	 *	Datatable filtering
	 *
	 *	@return null
	 */

	protected function filtering()
	{
		/*
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		 */

		if ( $sSearch = input_get('sSearch'))
		{
			$i = 0;
			foreach ( $this->columns as $column )
			{
				$columnName = $this->getColumnName($column);
				if ($columnName and input_get('bSearchable_'.$i) == "true")
				{
					$this->orWhere($this->getColumnName($column), 'like', "%{$sSearch}%");//$this->wildcard_like_string($sSearch));
				}
				$i++;
			}
		}

		/* Individual column filtering */
		$i = 0;
		foreach ( $this->columns as $column )
		{
			$columnName = $this->getColumnName($column);
			if ($columnName and input_get('bSearchable_'.$i) == "true" and $sSearch = input_get('sSearch_'.$i))
			{
				$this->orWhere($this->getColumnName($column), 'like', "%{$sSearch}%");//$this->wildcard_like_string($column));
			}
			$i++;
		}
	}


	/**
	 *  Adds % wildcards to the given string
	 *
	 *  @return string
	 */

	public function wildcard_like_string($str, $lowercase = true) {
	    $wild = '%';
	    $length = strlen($str);
	    if($length) {
	        for ($i=0; $i < $length; $i++) {
	            $wild .= $str[$i].'%';
	        }
	    }
	    if($lowercase) $wild = strtolower($wild);
	    return $wild;
	}


	protected function processRows(array $columns)
	{
		$collection = $this->get();

		$this->iTotalDisplayRecords = $collection->count();

		$rows = array();
		foreach ($collection as $model)
		{
			$row = array();
			foreach ($columns as $column)
			{
				$row[] = $this->getColumn($model, $column);
			}
			$rows[] = $row;
		}

		return $rows;
	}

	protected function getColumnName($column)
	{
		if (is_string($column))
		{
			return $column;
		}
		elseif (is_array($column))
		{
			return $column[0];
		}

		return false;
	}

	protected function getColumn($model, $column)
	{
		if (is_array($column))
		{
			$column = $column[1];
		}

		if (is_string($column))
		{
			$column = $this->removeTableFromKey($column);

			return $model->$column;
		}

		if (is_callable($column))
		{
			return $column($model);
		}


		return false;
	}

	protected function removeTableFromKey($key)
	{
		if ( ! str_contains($key, '.')) return $key;

		return last(explode('.', $key));
	}

	// protected function objectGet($object, $key, $default = null)
	// {
	// 	if (is_null($key)) return $object;

	// 	foreach (explode('.', $key) as $segment)
	// 	{
	// 		if ( ! is_object($object) or is_null($object->{$segment}))
	// 		{
	// 			return value($default);
	// 		}

	// 		$object = $object->{$segment};
	// 	}

	// 	return $object;
	// }




}