<?php

/**
 * Admin
 */
// define('DURA_ADMIN_NAME', 'ROOT');
// define('DURA_ADMIN_PASS', 'TOOR');

$duraAdmin = array(
	array('name' => 'ROOT', 'pass' => 'TOOR')
);

/**
 * TimeZone
 */
date_default_timezone_set('UTC');

/**
 * URL & Path
 */
// $duraDirectory = basename(dirname(__FILE__));
// define('DURA_URL', 'http://'.$_SERVER['SERVER_NAME'].(empty($duraDirectory)?'':'/'.$duraDirectory)); // DO NOT ADD SLASH TO END.
$isSecure = false;
if (
	(!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https' )
	|| (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
	|| (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
)
{
	$isSecure = true;
}
$duraProtocol = $isSecure ? 'https' : 'http';
define('DURA_URL', $duraProtocol.'://'.$_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT'] == '80' ? '' : ':'.$_SERVER['SERVER_PORT'])); // DO NOT ADD SLASH TO END.
define('DURA_PATH', dirname(__FILE__));

/**
 * Trust Path directory sould be put outside of Document Root.
 */
define('DURA_TRUST_PATH', DURA_PATH.'/trust');
define('DURA_LANGUAGE_PATH', DURA_TRUST_PATH.'/language');
define('DURA_RESOURCE_PATH', DURA_TRUST_PATH.'/resource');
define('DURA_STORAGE_PATH', DURA_TRUST_PATH.'/storage');
define('DURA_TEMPLATE_PATH', DURA_TRUST_PATH.'/template');

/**
 * If use mod_rewrite, set true.
 */
define('DURA_USE_REWRITE', true);

/**
 * Chat room settings
 */
define('DURA_LOG_LIMIT', 50);
define('DURA_TIMEOUT', 60 * 5);
define('DURA_USER_MIN', 2);
define('DURA_USER_MAX', 12);
define('DURA_SITE_USER_CAPACITY', 500); // 132 to 156
define('DURA_ROOM_LIMIT', DURA_SITE_USER_CAPACITY / DURA_USER_MIN);
define('DURA_CHAT_ROOM_EXPIRE', 60 * 30);
define('DURA_MESSAGE_MAX_LENGTH', 140);
define('DURA_ROOM_KEY_LENGTH', 10);

/**
 * Language setting
 */
define('DURA_LANGUAGE', 'en-US');

/**
 * Title settings
 */
define('DURA_TITLE', 'MUSHOKU');
define('DURA_SUBTITLE', 'NEET');
define('DURA_KEYWORDS', 'DuRaRaRa, デュラララ, MUSHOKU, NEET');

/**
 * Session name
 */
define('DURA_SESSION_NAME', 'DURASESS');

/**
 * Secure key
 */
define('DURA_SECURE_KEY', 'FOOBAR');

/**
 * reCAPTCHA key
 */
define('DURA_USE_RECAPTCHA', false);
define('RECAPTCHA_PUBLIC_KEY', '');
define('RECAPTCHA_PRIVATE_KEY', '');

/**
 * Comet settings
 */
define('DURA_USE_COMET', 1);
// define('DURA_SLEEP_LOOP', (ini_get('max_execution_time') - 5)); // DEFAULT >> 300 secs 
define('DURA_SLEEP_LOOP', 3);
define('DURA_SLEEP_TIME', 1);
