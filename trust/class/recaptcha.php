<?php
/**
 * Quick and simple ReCAPTCHA library
 *
 * PHP Version 5.3.0 or Upper version
 *
 * @package    Dura
 * @author     schnabear
 * @copyright  2017 schnabear
 * @link       https://github.com/schnabear/recaptcha
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 */

class Dura_Class_Recaptcha
{
	const API_ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

	public static function httpPostSocket($host, $path, $data, $port = 80)
	{
		$query = http_build_query($data);

		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= "Content-Length: " . strlen($query) . "\r\n";
		$http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
		$http_request .= "\r\n";
		$http_request .= $query;

		$response = '';
		if ( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) )
		{
			return false;
		}

		fwrite($fs, $http_request);

		while ( !feof($fs) )
		{
			$response .= fgets($fs, 1160); // One TCP-IP packet
		}

		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);

		return $response;
	}

	public static function httpPostCurl($url, $data, $port = 80)
	{
		$options = array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPAUTH => CURLAUTH_ANY,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_PORT => $port,
		);

		$curl = curl_init($url);
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);
		curl_close($curl);

		return $response;
	}

	public static function verify($ip, $response)
	{
		if ( !defined('RECAPTCHA_PRIVATE_KEY') || RECAPTCHA_PRIVATE_KEY == '' )
		{
			return false;
		}

		if ( strlen($ip) == 0 || strlen($response) == 0 )
		{
			return false;
		}

		$params = array(
			"secret" => RECAPTCHA_PRIVATE_KEY,
			"remoteip" => $ip,
			"response" => $response,
		);

		// $response = self::httpPostSocket('www.google.com', '/recaptcha/api/verify', $params);
		$response = self::httpPostCurl(self::API_ENDPOINT, $params, 443);
		$response = json_decode($response);

		return $response->success;
	}
}
