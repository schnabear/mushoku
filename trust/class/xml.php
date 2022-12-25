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

class Dura_Class_Xml extends SimpleXMLElement
{
	public function asXML($string = '')
	{
		if ( !empty($string) )
		{
			return parent::asXML($string);
		}

		$string = parent::asXML();

		if ( $string === false )
		{
			return false;
		}

		$this->_cleanupXML($string);
		return $string;
	}

	public function asArray()
	{
		$this->_objectToArray($this);
		return $this;
	}

	// Filter does not cover direct property access which is troublesome
	// Additional fix is needed for auto filtering of properties
	public function addChild($name, $value = null, $namespace = null)
	{
		if ( is_string($value) )
		{
			$pattern[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/';
			// http://www.w3.org/TR/REC-xml/#charsets
			$pattern[] = '/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u';

			do
			{
				$value = preg_replace($pattern, '�', $value, -1, $count);
			}
			while ( $count );

			$value = htmlentities($value, ENT_QUOTES | ENT_DISALLOWED, "UTF-8");
		}

		return parent::addChild($name, $value, $namespace);
	}

	protected function _cleanupXML(&$string)
	{
		$string = preg_replace("/>\s*</", ">\n<", $string);
		$lines  = explode("\n", $string);
		$string = array_shift($lines) . "\n";
		$depth  = 0;

		foreach ( $lines as $line )
		{
			if ( preg_match('/^<[\w]+>$/U', $line) )
			{
				$string .= str_repeat("\t", $depth);
				$depth++;
			}
			elseif ( preg_match('/^<\/.+>$/', $line) )
			{
				$depth--;
				$string .= str_repeat("\t", $depth);
			}
			else
			{
				$string .= str_repeat("\t", $depth);
			}

			$string .= $line . "\n";
		}

		$string = Dura::trim($string);
	}

	protected function _objectToArray(&$object)
	{
		if ( is_object($object) ) $object = (array) $object;
		if ( !is_array($object) ) return;

		foreach ( $object as &$member )
		{
			$this->_objectToArray($member);
		}
	}
}
