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

class Dura_Controller_Default extends Dura_Abstract_Controller
{
	protected $error = null;
	protected $icons = array();
	protected $languages = array();
	protected $defaultLanguage = null;

	public function __construct()
	{
		parent::__construct();

		$this->icons = Dura_Class_Icon::getIcons();
		$this->languages = Dura::getLanguages();

		unset($this->icons['admin']);
	}

	public function main()
	{
		if ( Dura::user()->isUser() )
		{
			$this->_redirect();
		}

		$this->_getDefaultLanguage();

		if ( Dura::post('login') )
		{
			try
			{
				$this->_login();
			}
			catch ( Exception $e )
			{
				$this->error = $e->getMessage();
			}
		}

		$this->_rooms();
		$this->_default();
	}

	protected function _login()
	{
		$name = Dura::post('name');
		$icon = Dura::post('icon');
		$language = Dura::post('language');

		extract(Dura_Class_Tripcode::process($name, 30));

		$name = Dura::trim($name, false);

		if ( $name === '' )
		{
			throw new Exception(t("Please input name."));
		}

		if ( mb_strlen($name) > 10 )
		{
			throw new Exception(t("Name should be less than 10 letters."));
		}

		if ( mb_strlen($raw) > 30 )
		{
			throw new Exception(t("Code should be less than 30 letters."));
		}

		if ( !in_array($language, array_keys($this->languages)) )
		{
			throw new Exception(t("The language is not in the option."));
		}

		$token = Dura::post('token');

		if ( !Dura_Class_Ticket::check($token) )
		{
			throw new Exception(t("Login error happened."));
		}

		if ( !isset($this->icons[$icon]) )
		{
			$icons = array_keys($this->icons);
			$icon = reset($icons);
		}

		$user =& Dura_Class_User::getInstance();
		$user->login($name, $icon, $code, $language);

		Dura_Class_Ticket::destory();

		$this->_redirect();
	}

	protected function _redirect()
	{
		if ( Dura::get('id') && Dura::get('invite') )
		{
			$id     = Dura::get('id');
			$invite = Dura::get('invite');

			Dura::redirect('room', null, array('id' => $id, 'invite' => $invite));
		}
		else
		{
			Dura::redirect('lounge');
		}
	}

	protected function _getDefaultLanguage()
	{
		$acceptLangs = getenv('HTTP_ACCEPT_LANGUAGE');
		$acceptLangs = explode(',', $acceptLangs);
		$defaultLanguage = Dura::get('language');

		if ( $defaultLanguage === '' || !in_array($defaultLanguage, array_keys($this->languages)) )
		{
			$defaultLanguage = DURA_LANGUAGE;

			foreach ( $acceptLangs as $k => $acceptLang )
			{
				@list($langcode, $dummy) = explode(';', $acceptLang);

				foreach ( $this->languages as $language => $v )
				{
					if ( stripos($language, $langcode) === 0 )
					{
						$defaultLanguage = $language;
						break 2;
					}
				}
			}
		}

		$this->defaultLanguage = $defaultLanguage;
	}

	protected function _rooms()
	{
		$roomHandler = new Dura_Model_RoomHandler;
		$roomModels = $roomHandler->loadAll();

		$rooms = array();

		$roomExpire = time() - DURA_CHAT_ROOM_EXPIRE;
		$activeUser = 0;
		$activeCapacity = 0;
		$roomCount = 0;

		foreach ( $roomModels as $id => $roomModel )
		{
			if ( $roomModel['update'] < $roomExpire && !$roomModel['permanent'] )
			{
				$roomHandler->delete($id);
				continue;
			}

			$isDefaultLanguage = (int) ( $roomModel['language'] != $this->defaultLanguage );

			$rooms[$isDefaultLanguage][$roomModel['language']][(int) $roomModel['permanent']][$id] = $roomModel;

			if ( !empty($roomModel['users']) )
			{
				$activeUser += count($roomModel['users']);
			}

			$activeCapacity += $roomModel['limit'];
			$roomCount++;
		}

		unset($roomHandler, $roomModels, $roomModel);

		ksort($rooms);

		foreach ( $rooms as $keyDefaultLanguage => $value )
		{
			foreach ( $value as $keyLanguage => $dummy )
			{
				ksort($rooms[$keyDefaultLanguage][$keyLanguage]);
			}
		}

		$this->output['rooms'] = $rooms;
		$this->output['room_count'] = $roomCount;
		$this->output['active_user'] = $activeUser;
		$this->output['active_capacity'] = $activeCapacity;
	}

	protected function _default()
	{
		$this->output['languages'] = $this->languages;
		$this->output['default_language'] = $this->defaultLanguage;
		$this->output['icons'] = $this->icons;
		$this->output['error'] = $this->error;
		$this->output['token'] = Dura_Class_Ticket::issue();

		Dura::setLanguage($this->output['default_language']);

		$this->_view();
	}
}
