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

class Dura_Class_Mark
{
	static $markDir = '/images/mark';
	static $markPrefix = '';
	static $markExt = 'png';

	public static function &getMarks()
	{
		static $marks = null;

		if ( $marks === null )
		{
			$marks = array();
			$markDir = DURA_PATH.self::$markDir;

			if ( $dir = opendir($markDir) )
			{
				while ( ($file = readdir($dir)) !== false )
				{
					if ( preg_match('/^'.self::$markPrefix.'(.+)\.'.self::$markExt.'$/', $file, $match) )
					{
						list($dummy, $mark) = $match;
						$marks[$mark] = $file;
					}
				} 

				closedir($dir);
			}
		}

		ksort($marks);

		return $marks;
	}

	public static function getMarkUrl($mark)
	{
		$url = DURA_URL.self::$markDir.'/'.self::$markPrefix.$mark.'.'.self::$markExt;
		return $url;
	}
}
