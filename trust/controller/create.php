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

class Dura_Controller_Create extends Dura_Abstract_Controller
{
	protected $error = null;
	protected $input = null;
	protected $marks = array();
	protected $languages = array();
	protected $userMax = null;

	public function __construct()
	{
		parent::__construct();

		$this->_validateUser();

		$this->marks = Dura_Class_Mark::getMarks();
		$this->languages = Dura::getLanguages();
	}

	public function main()
	{
		$this->_redirectToRoom();

		$this->_roomLimit();

		$this->_getInput();

		if ( Dura::post('submit') )
		{
			try
			{
				$this->_create();
			}
			catch ( Exception $e )
			{
				$this->error = $e->getMessage();
			}
		}

		$this->_default();
	}

	protected function _redirectToRoom()
	{
		if ( Dura_Class_RoomSession::isCreated() )
		{
			Dura::redirect('room');
		}
	}

	protected function _getInput()
	{
		$this->input['name'] = Dura::post('name');
		$this->input['name'] = Dura::trim($this->input['name'], false);
		$this->input['limit'] = (int) Dura::post('limit');
		$this->input['language'] = Dura::post('language');
		$this->input['password'] = Dura::post('password');
		$this->input['password'] = Dura::trim($this->input['password']);

		if ( DURA_USE_RECAPTCHA )
		{
			$this->input['recaptcha_response'] = Dura::post('g-recaptcha-response');
		}

		if ( Dura::user()->isAdmin() )
		{
			$this->input['permanent'] = (bool) Dura::post('permanent');
			$this->input['mark']  = Dura::post('mark');
			$this->input['color'] = Dura::post('color');
		}
	}

	protected function _default()
	{
		$this->output['user_min'] = DURA_USER_MIN;
		$this->output['user_max'] = $this->userMax;
		$this->output['languages'] = $this->languages;
		$this->output['input'] = $this->input;
		$this->output['marks'] = $this->marks;
		$this->output['error'] = $this->error;
		$this->_view();
	}

	protected function _create()
	{
		$this->_validate();

		$this->_createRoom();
	}

	protected function _validate()
	{
		$name = $this->input['name'];
		$password = $this->input['password'];

		if ( $name === '' )
		{
			throw new Exception(t("Please input name."));
		}

		if ( mb_strlen($name) > 12 )
		{
			throw new Exception(t("Name should be less than 12 letters."));
		}

		if ( Dura_Class_NgWord::isNG($name) )
		{
			throw new Exception(t("Name should contain appropriate words."));
		}

		if ( mb_strlen($password) > 25 )
		{
			throw new Exception(t("Password should be less than 25 characters."));
		}

		$limit = $this->input['limit'];

		if ( $limit < DURA_USER_MIN )
		{
			throw new Exception(t("Member should be more than {1}.", DURA_USER_MIN));
		}

		if ( $limit > $this->userMax )
		{
			throw new Exception(t("Member should be less than {1}.", $this->userMax));
		}

		if ( !in_array($this->input['language'], array_keys($this->languages)) )
		{
			throw new Exception(t("The language is not in the option."));
		}

		if ( DURA_USE_RECAPTCHA )
		{
			$recaptchaResponse = $this->input['recaptcha_response'];

			if ( !Dura_Class_Recaptcha::verify(Dura::user()->getIP(), $recaptchaResponse) )
			{
				throw new Exception(t("CAPTCHA challenge failed."));
			}
		}

		if ( Dura::user()->isAdmin() )
		{
			$permanent = $this->input['permanent'];
			$mark  = $this->input['mark'];
			$color = $this->input['color'];

			if ( !is_bool($permanent) )
			{
				throw new Exception(t("The permanent selection is not in the option."));
			}

			if ( $mark != '' && !isset($this->marks[$mark]) )
			{
				throw new Exception(t("The room mark is not in the option."));
			}

			if ( mb_strlen($color) > 10 )
			{
				throw new Exception(t("Color should be less than 10 letters."));
			}
		}
	}

	protected function _roomLimit()
	{
		$roomHandler = new Dura_Model_RoomHandler;
		$roomModels = $roomHandler->loadAll();

		$roomExpire = time() - DURA_CHAT_ROOM_EXPIRE;

		$usedCapacity = 0;

		foreach ( $roomModels as $id => $roomModel )
		{
			if ( $roomModel['update'] < $roomExpire && !$roomModel['permanent'] )
			{
				$roomHandler->delete($id);
				continue;
			}

			$usedCapacity += $roomModel['limit'];
		}

		unset($roomHandler, $roomModels, $roomModel);

		if ( $usedCapacity >= DURA_SITE_USER_CAPACITY )
		{
			Dura::trans(t("Cannot create new room anymore."), 'lounge');
		}

		$this->userMax = DURA_SITE_USER_CAPACITY - $usedCapacity;

		if ( $this->userMax > DURA_USER_MAX )
		{
			$this->userMax = DURA_USER_MAX;
		}

		if ( $this->userMax < DURA_USER_MIN )
		{
			Dura::trans(t("Cannot create new room anymore."), 'lounge');
		}
	}

	protected function _createRoom()
	{
		$userName = Dura::user()->getName();
		$userId   = Dura::user()->getId();
		$userIcon = Dura::user()->getIcon();
		$userIP   = Dura::user()->getIP();
		$userCode = Dura::user()->getCode();

		$roomHandler = new Dura_Model_RoomHandler;
		$roomModel = $roomHandler->create();
		$roomModel['name']   = $this->input['name'];
		$roomModel['create'] = time();
		$roomModel['update'] = time();
		$roomModel['limit']  = $this->input['limit'];
		$roomModel['host']   = $userId;
		$roomModel['language'] = $this->input['language'];
		$roomModel['invite'] = Dura::randomString(DURA_ROOM_KEY_LENGTH);
		$roomModel['password'] = $this->input['password'];

		if ( Dura::user()->isAdmin() )
		{
			$roomModel['permanent'] = $this->input['permanent'];
			$roomModel['mark']      = $this->input['mark'];
			$roomModel['color']     = $this->input['color'];
		}
		else
		{
			$roomModel['permanent'] = false;
			$roomModel['mark']      = '';
			$roomModel['color']     = '';
		}

		$user = array();
		$user['name']   = $userName;
		$user['id']     = $userId;
		$user['icon']   = $userIcon;
		$user['ip']     = $userIP;
		$user['update'] = time();
		$user['code']   = $userCode;

		$roomModel['users']   = array();
		$roomModel['users'][] = $user;

		if ( Dura::$language != $this->input['language'] )
		{
			$langFile = DURA_LANGUAGE_PATH.'/'.$this->input['language'].'.php';
			Dura::$catalog = require $langFile;
		}

		$talk = array();
		$talk['id'] = md5(microtime().mt_rand());
		$talk['uid'] = '';
		$talk['name'] = $userName;
		$talk['message'] = "{1} logged in.";
		$talk['icon'] = '';
		$talk['time'] = microtime(true);

		$roomModel['talks']   = array();
		$roomModel['talks'][] = $talk;

		$id = md5(microtime().mt_rand());

		if ( !$roomHandler->save($id, $roomModel) )
		{
			throw new Exception(t("Data Error: Room creating failed."));
		}

		Dura_Class_RoomSession::create($id);

		Dura::redirect('room');
	}
}
