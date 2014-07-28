<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Extended inflector_helper to add contextual() and improve plural() and singular()
 */

// --------------------------------------------------------------------

/**
 * Singular
 *
 * Takes a plural word and makes it singular
 *
 * @access	public
 * @param	string  $str to be changed
 * @return	str
 */
if ( ! function_exists('singular'))
{
	function singular($str)
	{
		return _process_plural_singular($str, 'singular');
	}
}

// --------------------------------------------------------------------

/**
 * Plural
 *
 * Takes a singular word and makes it plural
 *
 * @access	public
 * @param	string  $str to be changed
 * @return	str
 */
if ( ! function_exists('plural'))
{
	function plural($str)
	{
		return _process_plural_singular($str, 'plural');
	}
}

// --------------------------------------------------------------------

/**
 * Process Plural Singular
 *
 * Depending on type the word is changed from singular to plural or the opposite
 *
 * This function is based on the CakePHP(tm) inflector class (http://book.cakephp.org/view/572/Class-methods)
 *
 * @access	private
 * @param	string  $str to be changed
 * @param	string  $type  (plural|singular)
 * @return	str
 * @author	milkboyuk@gmail.com based on Inflector class by CakePHP(tm)
 * @copyright	Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link	http://cakephp.org CakePHP(tm) Project
 * @license	http://www.opensource.org/licenses/mit-license.php The MIT License
 */
if ( ! function_exists('_process_plural_singular'))
{
	function _process_plural_singular($str, $type = 'plural')
	{
		static $processed_words = array();

		// has that word already been processed?
		if ( ! isset($processed_words[$str])) {

			// check if the string contains multiple words. If the second word is 'of',
			// the first word in the string is processed. e.g Field of Studies => Fields of Studies
			$words = explode(' ', $str);
			if (count($words) > 1
				&& strtolower($words[1]) === 'of')
			{
				$words[0] = _process_plural_singular($words[0], $type);
				$processed_words[$str] = implode(' ', $words);
				return $processed_words[$str];
			}

			// compile the required rule arrays based on config file
			static $rules = array();
			if ( ! $rules)
			{
				$CI =& get_instance();
				$CI->config->load('inflector', TRUE);
				$rules = $CI->config->item('inflector');
				$rules['uninflected'] = '(?:'.implode( '|', $rules['uninflected']).')';

				$irregular = $rules['irregular'];
				unset($rules['irregular']);
				$rules['plural']['irregular'] = $irregular;
				$rules['plural']['regex_irregular'] = '(?:'.implode('|', array_keys($irregular)).')';

				$irregular = array_flip($irregular); // flip the plural array for singular as it is just the opposite
				$rules['singular']['irregular'] = $irregular;
				$rules['singular']['regex_irregular'] = '(?:'.implode('|', array_keys($irregular)).')';
			}

			// check if plural and singular are the same
			if (preg_match('/^(' . $rules['uninflected'] . ')$/i', $str, $regs))
			{
				$processed_words[$str] = $str;
			}
			// check if word is irregular
			elseif (preg_match('/(.*)\\b(' . $rules[$type]['regex_irregular'] . ')$/i', $str, $regs))
			{
				$processed_words[$str] = $regs[1] . substr($str, 0, 1) . substr($rules[$type]['irregular'][strtolower($regs[2])], 1);
			}
			// check the conventional rules
			else {
				foreach ($rules[$type]['rules'] as $rule => $replacement)
				{
					if (preg_match($rule, $str))
					{
						$processed_words[$str] = preg_replace($rule, $replacement, $str);
						break;
					}
				}
			}
		}
		return $processed_words[$str];
	}
}

// ------------------------------------------------------------------------

/**
* Contextual
*
* Takes a string and a number and decides how to deal with the string
*
* @access	public
* @param	string  $str to be changed to plural or single depending on $num
* @param	int  $num counter which indicates if plural or singular should be used
* @return	string
* @author	hotmeteor & helmutbjorg (http://codeigniter.com/forums/viewthread/139031/)
*/
if ( ! function_exists('contextual'))
{
	function contextual($str, $num)
	{
	    return ($num != 1) ? plural($str) : singular($str);
	}
}

/* End of file MY_inflector_helper.php */
/* Location: ./system/application/helpers/MY_inflector_helper.php */
