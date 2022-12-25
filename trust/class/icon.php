<?php
/**
 * A simple description for this script
 *
 * PHP Version 5.3.0 or Upper version
 *
 * @package    Dura
 * @author     Hidehito NOZAWA aka Suin <http://suin.asia>
 * @author     schnabear
 * @copyright  2010 Hidehito NOZAWA
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 */

class Dura_Class_Icon
{
	static $iconDir = '/css/icon';
	static $iconPrefix = '';
	static $iconExt = 'png';

	public static function &getIcons()
	{
		static $icons = null;

		if ( $icons === null )
		{
			$icons = array();
			$iconDir = DURA_PATH.self::$iconDir;

			if ( $dir = opendir($iconDir) )
			{
				while ( ($file = readdir($dir)) !== false )
				{
					if ( preg_match('/^'.self::$iconPrefix.'(.+)\.'.self::$iconExt.'$/', $file, $match) )
					{
						list($dummy, $icon) = $match;
						$icons[$icon] = $file;
					}
				} 

				closedir($dir);
			}
		}

		ksort($icons);

		return $icons;
	}

	public static function getIconUrl($icon)
	{
		$url = DURA_URL.self::$iconDir.'/'.self::$iconPrefix.$icon.'.'.self::$iconExt;
		return $url;
	}
}
