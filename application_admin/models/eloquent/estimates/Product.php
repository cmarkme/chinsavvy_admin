<?php namespace Eloquent;


if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product extends Assembly {

	public function getToolingTotal()
	{
		$components = $this->getLCDC();

		$tooling = 0;
		foreach ($components as $component)
		{
			if ($component instanceof Process)
			{
				$tooling += $component->cost->tooling_cost;
			}
		}

		return $tooling;
	}
}