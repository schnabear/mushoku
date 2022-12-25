<?php
/**
 * Array object as storage for data
 * Remains experimental since native array is still faster
 *
 * PHP Version 5.5.0 or Upper version
 *
 * @package    Dura
 * @author     schnabear
 * @copyright  2014 schnabear
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 */

class Dura_Class_Array extends ArrayObject
{
	public function __construct($array = [], $flags = 0, $iteratorClass = ArrayIterator::class)
	{
		parent::__construct($array, $flags, $iteratorClass);
		static::toArrayObject($this);
	}

	public function __set($name, $value)
	{
		$this->offsetSet($name, $value);
	}

	public function __get($name)
	{
		return $this->offsetGet($name);
	}

	public function offsetSet($name, $value)
	{
		$value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
		parent::offsetSet($name, $value);
	}

	public function offsetGet($name)
	{
		if ( !isset($this[$name]) )
		{
			throw new Exception();
		}

		return parent::offsetGet($name);
	}

	public function offsetUnset($key)
	{
		parent::offsetUnset($key);

		if ( is_int($key) )
		{
			$this->exchangeArray(array_values($this->getArrayCopy()));
		}
	}

	public function reindex()
	{
		$this->exchangeArray(array_values($this->getArrayCopy()));
	}

	public static function toArrayObject($array)
	{
		foreach ( $array as &$value )
		{
			if ( is_array($value) && !($value instanceof ArrayObject) )
			{
				$value = new static($value);
				$value = static::toArrayObject($value);
			}
		}
		unset($value);
		return $array;
	}
}
