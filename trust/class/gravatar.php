<?php
/**
 * Gravatar generator
 *
 * PHP Version 5.3.0 or Upper version
 *
 * @package    Dura
 * @author     schnabear
 * @copyright  2013 schnabear
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 */

class Dura_Class_Gravatar
{
	const HTTP_URL = 'http://www.gravatar.com/';
	const HTTPS_URL = 'https://secure.gravatar.com/';

	public static function createHash($email)
	{
		return md5(strtolower(trim($email)));
	}

	public static function buildProfileURL($email, $hashEmail = false, $isSecure = false)
	{
		$url = '';

		if ( $isSecure === true )
		{
			$url = self::HTTPS_URL;
		}
		else
		{
			$url = self::HTTP_URL;
		}

		if ( $hashEmail === true )
		{
			$url .= self::createHash($email);
		}
		elseif ( !empty($email) )
		{
			$url .= $email;
		}
		else
		{
			$url .= str_repeat('0', 32);
		}

		return $url;
	}

	public static function buildAvatarURL($email, $hashEmail = false, $options = array())
	{
		$default = array(
			'extension' => '',
			'secure' => false
		);

		$options = array_merge($default, $options);

		// Size
		if ( isset($options['s']) )
		{
			if ( !is_int($options['s']) && !ctype_digit($options['s']) )
			{
				unset($options['s']);
			}

			if ( $options['s'] > 2048 || $options['s'] < 1 )
			{
				$options['s'] = 80;
			}
		}

		// Default
		if ( isset($options['d']) )
		{
			$defaultAvatar = array(
				'404',
				'mm',
				'identicon',
				'monsterid',
				'wavatar',
				'retro',
				'blank'
			);

			if ( !in_array($options['d'], $defaultAvatar) && !filter_var($options['d'], FILTER_VALIDATE_URL) )
			{
				unset($options['d']);
			}
		}

		// Force Default
		if ( isset($options['f']) )
		{
			if ( $options['f'] !== 'y' )
			{
				unset($options['f']);
			}
		}

		// Rating
		if ( isset($options['r']) )
		{
			$defaultRating = array(
				'g',
				'pg',
				'r',
				'x'
			);

			if ( !in_array($options['r'], $defaultRating) )
			{
				unset($options['r']);
			}
		}

		$url = '';

		if ( $options['secure'] === true )
		{
			$url = self::HTTPS_URL;
		}
		else
		{
			$url = self::HTTP_URL;
		}

		$url .= 'avatar/';

		if ( $hashEmail === true )
		{
			$url .= self::createHash($email);
		}
		elseif ( !empty($email) )
		{
			$url .= $email;
		}
		else
		{
			$url .= str_repeat('0', 32);
		}

		if ( isset($options['extension']) && $options['extension'] !== '' )
		{
			$url .= '.'.$options['extension'];
		}

		unset($options['secure']);
		unset($options['extension']);

		if ( $param = http_build_query($options) )
		{
			$url .= '?'.$param;
		}

		return $url;
	}
}
