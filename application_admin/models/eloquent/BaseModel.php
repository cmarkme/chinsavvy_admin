<?php namespace Eloquent;
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('connection.php');

use Exception;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class ValidationException extends Exception {}

class BaseModel extends DataTable
{
	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'creation_date';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string
	 */
	const UPDATED_AT = 'revision_date';

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_BY = 'creation_user_id';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string
	 */
	const UPDATED_BY = 'revision_user_id';

	/**
	 * Whether or not we validate this model.
	 * @var boolean
	 */
	protected $validate = true;

	public function __construct($attributes = array())
	{
		parent::__construct($attributes);
		if ($this->useSti()) {
			$this->setAttribute($this->stiClassField,get_class($this));
		}
	}

	private function useSti()
	{
		return ($this->stiClassField && $this->stiBaseClass);
	}

	public function newQuery($excludeDeleted = true)
	{
		$builder = parent::newQuery($excludeDeleted);
		// If I am using STI, and I am not the base class,
		// then filter on the class name.

		if ($this->useSti() && get_class(new $this->stiBaseClass) !== get_class($this))
		{
		    $builder->where($this->stiClassField,"=",get_class($this));
		}
		return $builder;
	}

	public function newFromBuilder($attributes = array())
	{
		if ($this->useSti() && $attributes[$this->stiClassField])
		{
			$class = $attributes[$this->stiClassField];

			$instance = new $class;

			$instance->exists = true;

			$instance->setRawAttributes((array) $attributes, true);
			return $instance;
		}
		else
		{
		 	return parent::newFromBuilder($attributes);
		}
	}

	/**
	 * Set the value of the "created at" attribute.
	 *
	 * @param  mixed  $value
	 * @return void
	 */
	public function setCreatedAt($value)
	{
		$CI = &get_instance();

		$this->{static::CREATED_AT} = $value;
		$this->{static::CREATED_BY} = $CI->session->userdata('user_id');
	}

	/**
	 * Set the value of the "updated at" attribute.
	 *
	 * @param  mixed  $value
	 * @return void
	 */
	public function setUpdatedAt($value)
	{
		$CI = &get_instance();

		$this->{static::UPDATED_AT} = $value;
		$this->{static::UPDATED_BY} = $CI->session->userdata('user_id');
	}

	public static $actionColumns = array(
		'edit',
		'delete',
		);

	protected $entityUrl = '';

	public function isEditable()
	{
		return true;
	}

	public function isDeleteable()
	{
		return false;
	}

	public function getActionColumn()
	{
		$html = '';
		foreach (static::$actionColumns as $action)
		{
			$method = 'is' . ucfirst($action) . 'able';
			if ( ! method_exists($this, $method) or $this->{$method}() )
			{
				$html .= '<a href="' . $this->entityUrl . '/' . $action . '/' . $this->getKey() . '"';
				$html .= ' class="action-icon ' . $action . '" title="' . ucfirst($action) . ' item."></a>';
			}
		}

		return $html;
	}

	public function creator()
	{
		return $this->belongsTo('Eloquent\User', 'creation_user_id');
	}

	public function revisor()
	{
		return $this->belongsTo('Eloquent\User', 'revision_user_id');
	}

	/**
	 * Convert a DateTime to a storable string.
	 *
	 * @param  DateTime|int  $value
	 * @return string
	 */
	public function fromDateTime($value)
	{
		return time($value);
	}

	public function getCreationDateAttribute($date)
	{
		return $this->getUKDateString($date);
	}

	public function getRevisionDateAttribute($date)
	{
		return $this->getUKDateString($date);
	}

	public function getExpiringRevisionDateAttribute($date)
	{
		$date = $this->attributes['revision_date'];

		$uk_date = $this->getUKDateString($date);

		if ( Carbon::createFromTimeStamp($date)->lt(Carbon::today()->subDays(ESTIMATES_OVER_AGE_COST_THRESHOLD)) )
		{
			return '<span class="error">' . $uk_date . ' *</span>';
		}
		else
		{
			return $uk_date;
		}
	}

	public function getUKDateString($date)
	{
		if (empty($date))
		{
			return null;
		}
		elseif ($date instanceof \DateTime)
		{
			return $date->format('d/m/Y');
		}
		elseif (is_numeric($date))
		{
			return date('d/m/Y', $date);
		}

		return $date;
	}

	public static function getDropdown($val, $key = 'id')
	{
		return array('' => '-- Please Select --') + static::lists($val, $key);
	}

	public static function getNextId()
	{
		return static::max('id') + 1;
	}

	public function ValidateOff()
	{
		$this->validate = false;

		return $this;
	}

	public function ValidateOn()
	{
		$this->validate = true;

		return $this;
	}

	/**
     * Validates current attributes against rules
     */
    public function validate()
    {
    	if ( $this->validate === false or empty(static::$rules))
    	{
    		return true;
    	}

    	$CI = & get_instance();

    	$post = $_POST;

    	$_POST = $this->toArray();

    	$CI->form_validation->set_rules( static::$rules );

    	$valid = $CI->form_validation->run();

    	$_POST = $post;

    	return $valid;
    }

    protected function performInsert($query)
	{
		if ( ! $this->validate() )
		{
			throw new ValidationException;
		}

		return parent::performInsert($query);
	}

	protected function performUpdate($query)
	{
		if ( ! $this->validate() )
		{
			throw new ValidationException;
		}

		return parent::performUpdate($query);
	}



}
