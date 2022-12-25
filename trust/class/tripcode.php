<?php
/**
 * Tripcode generator based from Wakaba and Shiichan
 * Most parts are directly converted from Perl source
 *
 * PHP Version 5.3.0 or Upper version
 *
 * @package    Dura
 * @author     schnabear
 * @copyright  2013 schnabear
 * @link       https://github.com/schnabear/tripcode
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 */

class Dura_Class_Tripcode
{
	public static function process($string, $limit = 0)
	{
		$name = str_replace(array("﹟", "＃", "♯"), "#", $string);
		$raw = '';
		$tripcode = '';

		if ( preg_match("/^([^#]+)(#)(.+)/us", $name, $matches) )
		{
			$name = $matches[1];
			$raw  = $matches[2] . $matches[3];
			$code = $limit ? mb_substr($matches[3], 0, $limit) : $matches[3];
			$code = array_pad(preg_split("/#/", $code, 2), 2, '');
			$normalCode = $code[0];
			$secureCode = $code[1];
			$tripcode = "";

			if ( $normalCode )
			{
				$tripcode = Dura_Class_Tripcode::makeNormalTripcode($normalCode);
			}

			if ( $secureCode )
			{
				$tripcode .= Dura_Class_Tripcode::makeSecureTripcode($secureCode);
			}
		}

		return array(
			'name' => $name,
			'raw'  => $raw,
			'code' => $tripcode
		);
	}

	public static function isForbiddenUnicode($dec, $hex = '')
	{
		if ( mb_strlen($dec) > 7 || mb_strlen($hex) > 7 )
		{
			return true;
		}

		$ord = (int) ( $dec ?: hexdec($hex) );

		return $ord > 1114111
			|| $ord < 32
			|| ( $ord >= 0x7f && $ord <= 0x84 )
			|| ( $ord >= 0xd800 && $ord <= 0xdfff )
			|| ( $ord >= 0x202a && $ord <= 0x202e )
			|| ( $ord >= 0xfdd0 && $ord <= 0xfdef )
			|| ( $ord % 0x10000 >= 0xfffe );
	}

	public static function decode($string)
	{
		return preg_replace_callback(
			'/&#(?:([0-9]*)|([Xx&])([0-9A-Fa-f]*))([;&])/s',
			function ($matches)
			{
				list($full, $dec, $ampex, $hex, $end) = array_pad($matches, 5, "");
				$ord = (int) ( $dec ?: hexdec($hex) );
				if ( $ampex == "&" || $end == "&" )
				{
					return $full;
				}
				elseif ( Dura_Class_Tripcode::isForbiddenUnicode($dec, $hex) )
				{
					return "";
				}
				elseif ( $ord == 35 || $ord == 38 )
				{
					return $full;
				}
				else
				{
					mb_substitute_character("none");
					return mb_convert_encoding("&#" . $ord . ";", "UTF-8", "HTML-ENTITIES");
				}
			},
			$string
		);
	}

	public static function clean($string)
	{
		$string = preg_replace_callback(
			'/&(#([0-9]+);|#[Xx]([0-9A-Fa-f]+);|)/s',
			function ($matches)
			{
				list($full, $codePoint, $dec, $hex) = array_pad($matches, 4, "");
				if ( $codePoint == "" )
				{
					return "&amp;";
				}
				elseif ( Dura_Class_Tripcode::isForbiddenUnicode($dec, $hex) )
				{
					return "";
				}
				else
				{
					return "&{$codePoint}";
				}
			},
			$string
		);

		// $string = str_replace('&', '&amp;', $string);
		$string = str_replace(',', '&#44;', $string);
		$string = str_replace('"', '&quot;', $string);
		$string = str_replace('\'', '&#39;', $string);
		$string = str_replace('<', '&lt;', $string);
		$string = str_replace('>', '&gt;', $string);
		$string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]+/', '', $string);

		return $string;
	}

	public static function makeNormalTripcode($code)
	{
		if ( function_exists('mb_convert_encoding') )
		{
			mb_substitute_character('none');
			$encode = mb_convert_encoding(Dura_Class_Tripcode::decode($code), 'SJIS', 'UTF-8');
			$code = $encode ?: $code;
		}

		$code = Dura_Class_Tripcode::clean($code);
		$salt = strtr(preg_replace('/[^\.-z]/', '.', substr($code.'H..', 1, 2)), ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');

		$output = substr(crypt($code, $salt), -10);
		return '!'.$output;
	}

	public static function makeSecureTripcode($code)
	{
		$output = substr(base64_encode(pack("H*", sha1($code.DURA_SECURE_KEY))), 0, 11);
		return '!!'.$output;
	}
}
