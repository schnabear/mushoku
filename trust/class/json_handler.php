<?php
/**
 * JSON file handler
 *
 * PHP Version 5.3.0 or Upper version
 *
 * @package    Dura
 * @author     schnabear
 * @copyright  2014 schnabear
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 */

class Dura_Class_JsonHandler
{
	protected $className = 'Dura_Class_Json';
	protected $fileName  = 'json';

	public function __construct($className = null)
	{
		if ( $className )
		{
			$this->className = $className;
		}
	}

	public function create()
	{
		return new $this->className(array());
	}

	public function load($id)
	{
		$file = $this->getFilePath($id);

		$contents = file_get_contents($file);
		$contents = json_decode($contents, true);

		if ( !$contents )
		{
			return false;
		}

		$json = new $this->className($contents);

		return $json;
	}

	public function save($id, $json)
	{
		$json->update = time();
		$file = $this->getFilePath($id);
		return file_put_contents($file, (string) $json, LOCK_EX);
	}

	public function delete($id)
	{
		$file = $this->getFilePath($id);
		return @unlink($file);
	}

	public function getFilePath($id)
	{
		return DURA_STORAGE_PATH.'/'.$this->getFileName($id);
	}

	public function getFileName($id)
	{
		return $this->fileName.'_'.$id.'.json';
	}
}
