<?php
/**
 * JSON array storage
 *
 * PHP Version 5.3.0 or Upper version
 *
 * @package    Dura
 * @author     schnabear
 * @copyright  2014 schnabear
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 */

class Dura_Class_Json implements ArrayAccess
{
	protected $array = array();

	public function __construct($array)
	{
		$this->array = $array;
	}

	public function __set($name, $value)
	{
		$this->array[$name] = $value;
	}

	public function __get($name)
	{
		return $this->array[$name];
	}

	public function __invoke()
	{
		return $this->array;
	}

	public function __toString()
	{
		$this->array = static::sanitize($this->array);
		return json_encode($this->array);
	}

	public function offsetSet($offset, $value)
	{
		$this->array[$offset] = $value;
	}

	public function &offsetGet($offset)
	{
		return $this->array[$offset];
	}

	public function offsetExists($offset)
	{
		return isset($this->array[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->array[$offset]);
	}

	// https://stackoverflow.com/a/52641198
	public static function sanitize($mixed)
	{
		if ( is_array($mixed) )
		{
			foreach ( $mixed as $key => $value )
			{
				$mixed[$key] = static::sanitize($value);
			}
		}
		elseif ( is_string($mixed) )
		{
			$mixed = Dura::trim($mixed, false);
			$mixed = mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
		}
		return $mixed;
	}
}
