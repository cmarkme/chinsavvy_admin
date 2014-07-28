<?php

function currency_format($number, $symbol = '&yen;')
{
	return $symbol . number_format($number, 2);
}
