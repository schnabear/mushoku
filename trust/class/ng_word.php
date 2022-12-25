<?php
/**
 * A simple description for this script
 *
 * PHP Version 5.3.0 or Upper version
 *
 * @package    Dura
 * @author     schnabear
 * @copyright  2013 schnabear
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 */

class Dura_Class_NgWord
{
	public static $words = array();

	public static function isNG($string)
	{
		if ( empty(self::$words) )
		{
			self::$words = require_once DURA_LANGUAGE_PATH."/ng_word_list.php";
		}

		$string = " ".$string." ";
		str_ireplace(self::$words, "", $string, $count);

		return $count > 0;
	}
}
