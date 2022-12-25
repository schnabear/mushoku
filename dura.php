<?php
/**
 * A simple description for this script
 *
 * PHP Version 5.3.0 or Upper version
 *
 * @package    Dura
 * @author     Hidehito NOZAWA aka Suin
 * @author     schnabear
 * @copyright  2010 Hidehito NOZAWA
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 */

class Dura
{
	public static $controller;
	public static $action;

	public static $Controller;
	public static $Action;

	public static $roomId;

	public static $catalog = array();
	public static $language = null;

	public static function setup()
	{
		if ( defined('DURA_LOADED') ) return;

		define('DURA_VERSION', '1.2.5');

		header("X-Content-Type-Options: nosniff");
		header("X-Frame-Options: DENY"); // DENY, SAMEORIGIN, ALLOW-FROM, ALLOWALL

		self::compressOutput();

		spl_autoload_register(array(__CLASS__, 'autoload'));

		session_name(DURA_SESSION_NAME);
		session_start();

		self::user()->loadSession();

		mb_internal_encoding('UTF-8');

		self::setLanguage(self::user()->getLanguage());

		define('DURA_LOADED', true);
	}

	public static function execute()
	{
		$controller = self::get('controller', 'default');
		$action     = self::get('action', 'default');

		self::$Controller = self::putintoClassParts($controller);
		self::$Controller = (self::$Controller != '') ? self::$Controller : self::putintoClassParts('default');
		self::$Action     = self::putintoClassParts($action);
		self::$Action     = (self::$Action != '') ? self::$Action : self::putintoClassParts('default');

		self::$controller = self::putintoPathParts(self::$Controller);
		self::$action     = self::putintoPathParts(self::$Action);

		self::$Action[0]  = strtolower(self::$Action[0]);

		$class = 'Dura_Controller_'.self::$Controller;

		if ( !class_exists($class) )
		{
			die("Invalid Access");
		}

		$instance = new $class();
		$instance->main();

		self::user()->updateExpire();

		unset($instance);
	}

	public static function autoload($class)
	{
		if ( class_exists($class, false) ) return;
		if ( !preg_match('/^Dura_/', $class) ) return;

		$parts = explode('_', $class);
		$parts = array_map(array(__CLASS__, 'putintoPathParts'), $parts);

		$module = array_shift($parts);

		$class = implode('/', $parts);
		$path  = sprintf('%s/%s.php', DURA_TRUST_PATH, $class);

		if ( !file_exists($path) ) return;

		require $path;
	}

	public static function setLanguage($language = null)
	{
		$langFile = DURA_LANGUAGE_PATH.'/'.$language.'.php';
		self::$language = $language;

		if ( !file_exists($langFile) )
		{
			$langFile = DURA_LANGUAGE_PATH.'/'.DURA_LANGUAGE.'.php';
			self::$language = DURA_LANGUAGE;
		}

		self::$catalog = require $langFile;
	}

	public static function getLanguages()
	{
		require_once DURA_LANGUAGE_PATH.'/list.php';

		$languages = dura_get_language_list();

		foreach ( $languages as $langcode => $name )
		{
			if ( !file_exists(DURA_LANGUAGE_PATH.'/'.$langcode.'.php') )
			{
				unset($languages[$langcode]);
			}
		}

		asort($languages);

		return $languages;
	}

	public static function get($name, $default = null)
	{
		$request = ( isset($_GET[$name]) ) ? $_GET[$name] : $default;
		if ( !is_array($request) && $request == null ) $request = $default;
		// if ( get_magic_quotes_gpc() && !is_array($request) ) $request = stripslashes($request);
		return $request;
	}

	public static function post($name, $default = null)
	{
		$request = ( isset($_POST[$name]) ) ? $_POST[$name] : $default;
		if ( !is_array($request) && $request == null ) $request = $default;
		// if ( get_magic_quotes_gpc() && !is_array($request) ) $request = stripslashes($request);
		return $request;
	}

	public static function request($name, $default = null)
	{
		$request = ( isset($_REQUEST[$name]) ) ? $_REQUEST[$name] : $default;
		if ( !is_array($request) && $request == null ) $request = $default;
		// if ( get_magic_quotes_gpc() && !is_array($request) ) $request = stripslashes($request);
		return $request;
	}

	public static function putintoClassParts($str)
	{
		$str = preg_replace('/[^a-z0-9_]/', '', $str);
		$str = explode('_', $str);
		$str = array_map('trim', $str);
		$str = array_diff($str, array(''));
		$str = array_map('ucfirst', $str);
		$str = implode('', $str);
		return $str;
	}

	public static function putintoPathParts($str)
	{
		$str = preg_replace('/[^a-zA-Z0-9]/', '', $str);
		$str = preg_replace('/([A-Z])/', '_$1', $str);
		$str = strtolower($str);
		if ( preg_match('/^_/', $str) ) $str = substr($str, 1, strlen($str));
		return $str;
	}

	public static function escapeHtml($string)
	{
		return htmlspecialchars($string, ENT_QUOTES);
	}

	public static function decodeHtml($string)
	{
		return htmlspecialchars_decode($string, ENT_QUOTES);
	}

	public static function normalizeSpace($string, $skipCRLF = false)
	{
		// http://www.bogofilter.org/pipermail/bogofilter/2003-March/001889.html
		// https://stackoverflow.com/q/14245053

		$spaces = array();
		$spaces[] = '/\xC2\xA0/'; // NBSP
		$spaces[] = '/\xC2\xAD/'; // Soft Hyphen
		$spaces[] = '/\xE2\x80\x80/'; // U+2000
		$spaces[] = '/\xE2\x80\x81/'; // U+2001
		$spaces[] = '/\xE2\x80\x82/'; // U+2002
		$spaces[] = '/\xE2\x80\x83/'; // U+2003
		$spaces[] = '/\xE2\x80\x84/'; // U+2004
		$spaces[] = '/\xE2\x80\x85/'; // U+2005
		$spaces[] = '/\xE2\x80\x86/'; // U+2006
		$spaces[] = '/\xE2\x80\x87/'; // U+2007
		$spaces[] = '/\xE2\x80\x88/'; // U+2008
		$spaces[] = '/\xE2\x80\x89/'; // U+2009
		$spaces[] = '/\xE2\x80\x8A/'; // U+200A
		$spaces[] = '/\xE2\x80\x8B/'; // U+200B ZWSP
		// $spaces[] = '/\xE2\x80\x8C/'; // U+200C ZWNJ Zero-Width Non-Joiner
		// $spaces[] = '/\xE2\x80\x8D/'; // U+200D ZWJ Zero-Width Joiner
		$spaces[] = '/\xE2\x80\xA8/'; // U+2028
		$spaces[] = '/\xE2\x80\xAF/'; // U+202F
		$spaces[] = '/\xE2\x81\x9F/'; // U+205F
		$spaces[] = '/\xE2\x80\xA9/'; // U+2029
		$spaces[] = '/\xE3\x80\x80/'; // U+3000
		$spaces[] = '/\xEF\xBB\xBF/'; // U+FEFF
		$spaces[] = '/\xE1\x9A\x80/'; // U+1680 OGHAM SPACE MARK
		$spaces[] = '/\xE1\xA0\x8E/'; // U+180E MONGOLIAN VOWEL SEPARATOR

		$string = preg_replace($spaces, ' ', $string);

		if ( !$skipCRLF )
		{
			$string = preg_replace('/[\r\n]+/', ' ', $string);
		}

		return preg_replace('/\s+/u', ' ', $string);
	}

	/*
	public static function wipeControlASCII($string, $urlQuery = false)
	{
		$pattern = array();

		if ( $urlQuery )
		{
			$pattern[] = '/%0[0-8bcef]/';
			$pattern[] = '/%1[0-9a-f]/';
		}

		$pattern[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/'; // 00-08, 11, 12, 14-31, 127
		// $pattern[] = '/\xEF(?:\xB7[\x90\xAF]|\xBF[\xBE\xBF])|(?:\xF0[\x9F\xAF]|[\xF1\xF4]\x8F|[\xF3|xF0]\xBF)\xBF[\xBE\xBF]/';
		$pattern[] = '/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u';

		do
		{
			$string = preg_replace($pattern, '', $string, -1, $count);
		}
		while ( $count );

		return $string;
	}
	*/

	public static function randomString($length = 8)
	{
		$chars = 'bcdfghjklmnprstvwxzaeiou';
		$string = '';

		for ( $p = 0; $p < $length; $p++ )
		{
			$letter = ( $p % 2 ) ? $chars[mt_rand(19, 23)] : $chars[mt_rand(0, 18)];
			$string .= mt_rand(0, 1) ? strtolower($letter) : strtoupper($letter);
		}

		return $string;
	}

	public static function redirect($controller = null, $action = null, $extra = array())
	{
		$url = self::url($controller, $action, $extra);
		header('Location: '.$url);
		die;
	}

	public static function url($controller = null, $action = null, $extra = array())
	{
		$params = array();

		if ( DURA_USE_REWRITE )
		{
			$url = DURA_URL.'/';
		}
		else
		{
			$url = DURA_URL.'/index.php';
		}

		if ( $controller )
		{
			if ( DURA_USE_REWRITE )
			{
				$url .= $controller.'/';
			}
			else
			{
				$params['controller'] = $controller;
			}
		}

		if ( $action )
		{
			if ( DURA_USE_REWRITE )
			{
				$url .= $action.'/';
			}
			else
			{
				$params['action'] = $action;
			}
		}

		if ( is_array($extra) )
		{
			$params = array_merge($params, $extra);
		}

		if ( $param = http_build_query($params) )
		{
			$url .= '?'.$param;
		}

		return $url;
	}

	public static function &user()
	{
		$user =& Dura_Class_User::getInstance();
		return $user;
	}

	public static function getUrl()
	{
		if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' )
		{
			$protocol = 'https://';
		}
		else
		{
			$protocol = 'http://';
		}

		$url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

		$parts = parse_url($url);

		if ( preg_match('/\.php$/', $parts['path']) )
		{
			$url = dirname($url);
		}
		elseif ( preg_match('/\/$/', $parts['path']) )
		{
			$url = substr($url, 0, -1);
		}

		return $url;
	}

	public static function trans($message, $controller = null, $action = null, $extra = array())
	{
		$url = self::url($controller, $action, $extra);

		$url = self::escapeHtml($url);
		$message = self::escapeHtml($message);

		require DURA_TEMPLATE_PATH.'/trans.php';
		die;
	}

	public static function trim($string, $skipCRLF = true)
	{
		$string = self::normalizeSpace($string, $skipCRLF);
		return preg_replace("/(^\s+)|(\s+$)/u", '', trim($string));
	}

	public static function isAJAX()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
	}

	public static function checkIPVersion($ip)
	{
		return strpos($ip, ':') === false ? 4 : 6;
	}

	public static function maskIP($ip)
	{
		if ( self::checkIPVersion($ip) == 6 )
		{
			return preg_replace('/:([0-9A-Fa-f]){4}/', ':XXXX', $ip);
		}
		else
		{
			return preg_replace('/\d{1,3}\.\d{1,3}$/', 'XXX.XXX', $ip);
		}
	}

	/*
	public static function validIP($ip)
	{
		if ( !empty($ip) && ip2long($ip) != -1 && ip2long($ip) !== false )
		{
			$privateIPs = array(
				array('0.0.0.0', '0.255.255.255'),
				array('0.0.0.0', '2.255.255.255'),
				array('10.0.0.0', '10.255.255.255'),
				array('127.0.0.0', '127.255.255.255'),
				array('169.254.0.0', '169.254.255.255'),
				array('172.16.0.0', '172.31.255.255'),
				array('192.0.2.0', '192.0.2.255'),
				array('192.168.0.0', '192.168.255.255'),
				array('255.255.255.0', '255.255.255.255')
			);

			foreach ( $privateIPs as $privateIP )
			{
				$min = ip2long($privateIP[0]);
				$max = ip2long($privateIP[1]);

				if ( ip2long($ip) >= $min && ip2long($ip) <= $max )
				{
					return false;
				}
			}

			return true;
		}
		else
		{
			return false;
		}
	}
	*/

	public static function validIP($ip)
	{
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE) !== false;
	}

	public static function fetchIP()
	{
		if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )
		{
			foreach ( explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip )
			{
				$ip = trim($ip);

				if ( self::validIP($ip) )
				{
					return $ip;
				}
			}
		}

		$keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR'
		);

		foreach ($keys as $key)
		{
			if ( isset($_SERVER[$key]) && self::validIP($_SERVER[$key]) )
			{
				return $_SERVER[$key];
			}
		}
	}

	public static function compressOutput()
	{
		if (
			@ini_get('zlib.output_compression') == false
			&& extension_loaded('zlib')
			&& isset($_SERVER['HTTP_ACCEPT_ENCODING'])
			&& strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false
		)
		{
			return ob_start('ob_gzhandler');
		}

		return false;
	}

	public static function hash($string, $salt = '')
	{
		return str_rot13(md5(substr(sha1($string.$salt), 0, -8)));
	}

	public static function microtime($isFloat = false)
	{
		// $time = implode('.', array_reverse(explode(' ', implode('', array_slice(explode('.', microtime()), 1, 1)))));
		$time = implode('.', array_slice(gettimeofday(), 0, 2));

		if ( $isFloat )
		{
			return (float) $time;
		}

		return $time;
	}
}

function t($message)
{
	if ( isset(Dura::$catalog[$message]) )
	{
		$message = Dura::$catalog[$message];
	}

	if ( func_num_args() == 1 ) return $message;

	$params = func_get_args();

	foreach ( $params as $i => $param )
	{
		$message = str_replace('{'.$i.'}', $param, $message);
	}

	return $message;
}

function e($string)
{
	echo $string;
}
