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

class Dura_Model_RoomHandler extends Dura_Class_JsonHandler
{
	protected $className = 'Dura_Model_Room';
	protected $fileName  = 'room';
	protected $fileExt   = 'json';

	public function loadAll()
	{
		$path = DURA_STORAGE_PATH.'/';
		$dir = opendir($path);

		$contents = array();

		while ( $file = readdir($dir) )
		{
			if ( !is_file($path.$file) || strpos($file, $this->fileName) !== 0 )
			{
				continue;
			}

			$id = str_replace($this->fileName.'_', '', $file);
			$id = str_replace('.'.$this->fileExt, '', $id);

			$content = $this->load($id);

			if ( $content )
			{
				$contents[$id] = $content;
			}
		}

		closedir($dir);

		return $contents;
	}
}
