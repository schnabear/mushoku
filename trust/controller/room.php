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

class Dura_Controller_Room extends Dura_Abstract_Controller
{
	protected $id   = null;
	protected $chat = null;
	protected $isAjax = null;
	protected $roomHandler = null;
	protected $roomModel   = null;
	protected $minLimit = null;
	protected $maxLimit = null;
	protected $checkPassword = true;
	protected $validInvite = false;
	protected $languages = array();

	public function __construct()
	{
		parent::__construct();

		$this->_validateUser();

		if ( Dura_Class_RoomSession::isCreated() )
		{
			$this->id = Dura_Class_RoomSession::get('id');
		}
		elseif ( Dura::get('id') )
		{
			$this->id = Dura::get('id');
		}
		else
		{
			$this->id = Dura::post('id');
		}

		if ( !$this->id && Dura::$action != 'ajax' )
		{
			Dura::redirect('lounge');
		}

		$this->roomHandler = new Dura_Model_RoomHandler;
		$this->roomModel   = $this->roomHandler->load($this->id);

		if ( !$this->roomModel && Dura::$action != 'ajax' )
		{
			Dura_Class_RoomSession::delete();
			Dura::trans(t("Room not found."), 'lounge');
		}
	}

	public function main()
	{
		if ( Dura::$action == 'ajax' )
		{
			$this->_ajax();
		}
		elseif ( Dura::$action == 'password' )
		{
			$this->_password();
		}

		if ( Dura::get('invite') )
		{
			$this->_invite();
		}
		elseif ( Dura::post('login') )
		{
			$this->_login();
		}
		elseif ( Dura::post('source') )
		{
			$this->_source();
		}
		elseif ( Dura::post('download') )
		{
			$this->_download();
		}
		elseif ( Dura::post('delete') )
		{
			$this->_delete();
		}

		if ( !$this->_isLogin() )
		{
			Dura_Class_RoomSession::delete();
			Dura::redirect('lounge');
		}

		if ( Dura::post('logout') )
		{
			$this->_logout();
		}
		elseif ( Dura::post('message') )
		{
			$this->_message();
		}
		elseif ( isset($_POST['room_name']) && isset($_POST['room_language']) )
		{
			$this->_changeRoomDetail();
		}
		elseif ( isset($_POST['room_limit']) )
		{
			$this->_changeRoomLimit();
		}
		elseif ( isset($_POST['room_password']) )
		{
			$this->_changeRoomPassword();
		}
		elseif ( isset($_POST['blocked_ip']) )
		{
			$this->_removeBlock();
		}
		elseif ( isset($_POST['new_host']) )
		{
			$this->_handoverHostRight();
		}
		elseif ( isset($_POST['ban_user']) )
		{
			$this->_banUser();
		}
		elseif ( isset($_POST['kick_user']) )
		{
			$this->_kickUser();
		}

		$this->_computeRoomLimit();
		$this->_languages();
		$this->_default();
	}

	protected function _invite()
	{
		if ( $this->_isLogin() )
		{
			return;
		}

		$inviteCode = Dura::get('invite');

		// Skip password screen if invite is present
		if ( $this->roomModel['invite'] == $inviteCode )
		{
			$this->validInvite = true;
			$this->_login();
		}

		Dura::trans(t("Invite code is invalid."), 'lounge');
	}

	protected function _login()
	{
		if ( $this->_isLogin() )
		{
			return;
		}

		$password = $this->roomModel['password'];

		if ( !empty($password) && $this->checkPassword && !$this->validInvite )
		{
			Dura::redirect('room', 'password', array('id' => $this->id));
		}

		if ( count($this->roomModel['users']) >= $this->roomModel['limit'] )
		{
			Dura::trans(t("Room is full."), 'lounge');
		}

		foreach ( $this->roomModel['bans'] as $ban )
		{
			if ( $ban['id'] == Dura::hash(Dura::user()->getIP()) )
			{
				Dura::trans(t("Youv'e been banned from entering the room."), 'lounge');
			}
		}

		$userName = Dura::user()->getName();
		$userId   = Dura::user()->getId();
		$userIcon = Dura::user()->getIcon();
		$userCode = Dura::user()->getCode();

		$changeHost = false;

		foreach ( $this->roomModel['users'] as $key => $user )
		{
			if ( $userName == $user['name'] && $userIcon == $user['icon'] )
			{
				Dura::trans(t("Same name user exists. Please rename or change icon."), 'lounge');
			}

			if ( $user['update'] < time() - DURA_CHAT_ROOM_EXPIRE )
			{
				$userName = $user['name'];

				$this->_npcDisconnect($userName);

				if ( $this->_isHost($user['id']) )
				{
					$changeHost = true;
				}

				$this->_removeWhisper($user['id']);

				unset($this->roomModel['users'][$key]);
			}
		}
		$this->roomModel['users'] = array_values($this->roomModel['users']);

		$user = array();
		$user['name'] = $userName;
		$user['id'] = $userId;
		$user['icon'] = $userIcon;
		$user['ip'] = Dura::user()->getIP();
		$user['update'] = time();
		$user['code'] = $userCode;

		if ( empty($this->roomModel['users']) )
		{
			$this->roomModel['users'] = array();
		}

		$this->roomModel['users'][] = $user;

		$this->_npcLogin($userName);

		if ( ($changeHost || count($this->roomModel['users']) == 1) && !$this->roomModel['permanent'] )
		{
			$this->_moveHostRight();
		}

		$this->_weepTalk();

		$this->roomHandler->save($this->id, $this->roomModel);

		Dura_Class_RoomSession::create($this->id);

		Dura::redirect('room');
	}

	protected function _logout()
	{
		$userName = Dura::user()->getName();
		$userId   = Dura::user()->getId();

		$this->_removeWhisper();

		foreach ( $this->roomModel['users'] as $key => $user )
		{
			if ( $userId == $user['id'] )
			{
				unset($this->roomModel['users'][$key]);
				break;
			}
		}
		$this->roomModel['users'] = array_values($this->roomModel['users']);

		if ( count($this->roomModel['users']) )
		{
			$this->_npcLogout($userName);

			if ( $this->_isHost() && !$this->roomModel['permanent'] )
			{
				$this->_moveHostRight();
			}

			$this->_weepTalk();

			$this->roomHandler->save($this->id, $this->roomModel);
		}
		else
		{
			if ( !$this->roomModel['permanent'] )
			{
				$this->roomHandler->delete($this->id);
			}
			else
			{
				$this->_npcLogout($userName);
				$this->_npcRoomEmpty();

				$this->_weepTalk();

				$this->roomHandler->save($this->id, $this->roomModel);
			}
		}

		Dura_Class_RoomSession::delete();

		Dura::redirect('lounge');
	}

	protected function _delete()
	{
		if ( $this->_isLogin() || !Dura::user()->isAdmin() )
		{
			return;
		}

		if ( $this->roomHandler->delete($this->id) )
		{
			Dura::trans(t("Room has been deleted."), 'lounge');
		}
		else
		{
			Dura::trans(t("Failed deleting the room."), 'lounge');
		}
	}

	protected function _source()
	{
		if ( $this->_isLogin() || !Dura::user()->isAdmin() )
		{
			return;
		}

		header('Content-Type: application/json; charset=UTF-8');
		die((string) $this->roomModel);
	}

	protected function _download()
	{
		if ( $this->_isLogin() || !Dura::user()->isAdmin() )
		{
			return;
		}

		$data     = (string) $this->roomModel;
		$filename = $this->roomHandler->getFileName($this->id);

		if ( strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== false )
		{
			header('Content-Type: application/json; charset=UTF-8');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Expires: 0');
			header('Pragma: public');
			header('Content-Length: '.strlen($data));
		}
		else
		{
			header('Content-Type: application/json; charset=UTF-8');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Pragma: no-cache');
			header('Content-Length: '.strlen($data));
		}

		die($data);
	}

	protected function _message()
	{
		$message = Dura::post('message');
		$message = Dura::trim($message, false);
		// $message = preg_replace('/^[ 　]*(.*?)[ 　]*$/u', '$1', $message);

		if ( isset($_POST['recipient']) && $_POST['recipient'] != '' )
		{
			$recipient = Dura::post('recipient');

			if ( $recipient == Dura::user()->getId() ) return;

			$userFound = false;

			foreach ( $this->roomModel['users'] as $user )
			{
				if ( $recipient == $user['id'] )
				{
					$userFound = true;
					break;
				}
			}

			if ( !$userFound ) return;
		}

		if ( !$message ) return;

		if ( mb_strlen($message) > DURA_MESSAGE_MAX_LENGTH )
		{
			$message = mb_substr($message, 0, DURA_MESSAGE_MAX_LENGTH).'...';
		}

		$name = Dura::user()->getName();
		$icon = Dura::user()->getIcon();
		$code = Dura::user()->getCode();

		if ( !empty($recipient) )
		{
			$whisper = array();
			$whisper['id'] = md5(microtime().mt_rand());
			$whisper['uid'] = Dura::user()->getId();
			$whisper['rid'] = $recipient;
			$whisper['name'] = $name;
			$whisper['message'] = $message;
			$whisper['icon'] = $icon;
			$whisper['time'] = microtime(true);
			$whisper['code'] = $code;

			if ( empty($this->roomModel['whispers']) )
			{
				$this->roomModel['whispers'] = array();
			}

			$this->roomModel['whispers'][] = $whisper;
		}
		else
		{
			$talk = array();
			$talk['id'] = md5(microtime().mt_rand());
			$talk['uid'] = Dura::user()->getId();
			$talk['name'] = $name;
			$talk['message'] = $message;
			$talk['icon'] = $icon;
			$talk['time'] = microtime(true);
			$talk['code'] = $code;

			$this->roomModel['talks'][] = $talk;
		}

		$id = Dura::user()->getId();

		foreach ( $this->roomModel['users'] as &$user )
		{
			if ( $id == $user['id'] )
			{
				$user['update'] = time();
				break;
			}
		}
		unset($user);

		$this->_weepTalk();

		if ( !empty($this->roomModel['whispers']) )
		{
			$this->_weepWhisper();
		}

		$this->roomHandler->save($this->id, $this->roomModel);

		if ( Dura::get('ajax') ) die; // TODO

		Dura::redirect('room');
	}

	protected function _ajax()
	{
		if ( !isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], DURA_URL) === false )
		{
			die;
		}

		if ( !$this->id )
		{
			// Session not exists.
			header('Content-Type: application/json; charset=UTF-8');
			die(json_encode(array('error' => 1)));
		}

		if ( !$this->roomModel )
		{
			Dura_Class_RoomSession::delete();

			// Room not found.
			header('Content-Type: application/json; charset=UTF-8');
			die(json_encode(array('error' => 2)));
		}

		$file = $this->roomHandler->getFilePath($this->id);

		$filehash = @hash_file('crc32b', $file);

		session_write_close();

		if ( !isset($_GET['fast']) )
		{
			for ( $i = 0; $i < DURA_SLEEP_LOOP; $i++ )
			{
				if ( $filehash != @hash_file('crc32b', $file) )
				{
					break;
				}

				sleep(DURA_SLEEP_TIME);

				// If User-Agent has been altered, pull will then be made every 1 second
				// if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false )
				// {
				// 	break;
				// }
			}
		}

		// Reload room data.
		$this->roomModel = $this->roomHandler->load($this->id);

		$userId = Dura::user()->getId();
		$isLogin = false;

		if ( isset($this->roomModel['users']) )
		{
			foreach ( $this->roomModel['users'] as &$user )
			{
				$user['ip'] = Dura::maskIP($user['ip']);

				if ( $userId == $user['id'] )
				{
					$isLogin = true;
				}
			}
			unset($user);
		}

		if ( !$isLogin )
		{
			session_name(DURA_SESSION_NAME);
			session_start();
			Dura_Class_RoomSession::delete();

			// Room timeout.
			header('Content-Type: application/json; charset=UTF-8');
			die(json_encode(array('error' => 3)));
		}

		$currentTime = microtime(true);
		$accessTime = $currentTime;

		if ( preg_match("/^[0-9]+\.[0-9]+$/", Dura::get('access')) )
		{
			$accessTime = (float) Dura::get('access');
		}

		$this->roomModel['error'] = 0;

		foreach ( $this->roomModel['bans'] as &$ban )
		{
			$ban['ip'] = Dura::maskIP($ban['ip']);
		}
		unset($ban);

		foreach ( $this->roomModel['talks'] as &$talk )
		{
			if ( $talk['uid'] == '' )
			{
				$name    = $talk['name'];
				$message = $talk['message'];

				$talk['message'] = t($message, $name);
			}
		}
		unset($talk);

		foreach ( $this->roomModel['talks'] as $key => $talk )
		{
			if ( $talk['time'] < $accessTime )
			{
				unset($this->roomModel['talks'][$key]);
			}
		}
		$this->roomModel['talks'] = array_values($this->roomModel['talks']);

		if ( !empty($this->roomModel['whispers']) )
		{
			foreach ( $this->roomModel['whispers'] as $key => $whisper )
			{
				if ( $whisper['time'] < $accessTime )
				{
					unset($this->roomModel['whispers'][$key]);
					continue;
				}

				$whisperUserId = $whisper['uid'];
				$whisperRecipientId = $whisper['rid'];

				if ( $whisperUserId != $userId && $whisperRecipientId != $userId )
				{
					unset($this->roomModel['whispers'][$key]);
				}
			}
			$this->roomModel['whispers'] = array_values($this->roomModel['whispers']);

			// $i = count($this->roomModel->whispers);
			// for ( $i--; $i >= 0; $i-- )
			// {
			// 	if ( $this->roomModel->whispers[$i]->time < $accessTime )
			// 	{
			// 		unset($this->roomModel->whispers[$i]);
			// 	}
			// }
		}

		// Room limit possible values
		// Possibly cause slowness due to large file checks
		// Remove room limit change feature?
		$this->_computeRoomLimit();

		$this->roomModel['min'] = $this->minLimit;
		$this->roomModel['max'] = $this->maxLimit;

		unset($this->roomModel['password']);

		$this->roomModel['access'] = $currentTime;

		header('Content-Type: application/json; charset=UTF-8');
		die((string) $this->roomModel);
	}

	protected function _password()
	{
		if ( Dura_Class_RoomSession::isCreated() )
		{
			Dura::redirect('room');
		}

		$roomPassword = $this->roomModel['password'];

		if ( empty($roomPassword) )
		{
			$this->_login();
		}

		$error = null;
		$inputPassword = Dura::post('password');

		if ( Dura::post('submit') )
		{
			try
			{
				if ( $inputPassword == '' )
				{
					throw new Exception(t("Please input password."));
				}

				if ( mb_strlen($inputPassword) > 25 )
				{
					throw new Exception(t("Password should be less than 25 characters."));
				}

				if ( $roomPassword != $inputPassword )
				{
					throw new Exception(t("Password provided is incorrect."));
				}

				if ( DURA_USE_RECAPTCHA )
				{
					$recaptchaResponse = Dura::post('g-recaptcha-response');

					if ( !Dura_Class_Recaptcha::verify(Dura::user()->getIP(), $recaptchaResponse) )
					{
						throw new Exception(t("CAPTCHA challenge failed."));
					}
				}

				$this->checkPassword = false;
				$this->_login();
			}
			catch ( Exception $e )
			{
				$error = $e->getMessage();
			}
		}

		$this->output['password'] = $inputPassword;
		$this->output['error'] = $error;

		die($this->_view());
	}

	protected function _default()
	{
		$room = $this->roomModel;

		$userId = Dura::user()->getId();
		$userName = Dura::user()->getName();
		$userIcon = Dura::user()->getIcon();
		$userCode = Dura::user()->getCode();
		$userIP = Dura::user()->getIP();
		$userAvatar = Dura_Class_Icon::getIconUrl($userIcon);

		$room['id'] = $this->id;
		$room['talks'] = array_reverse($room['talks']);

		// Fetch only the neccessary whispers
		if ( !empty($room['whispers']) )
		{
			foreach ( $room['whispers'] as $key => $whisper )
			{
				if ( $whisper['uid'] != $userId && $whisper['rid'] != $userId )
				{
					unset($room['whispers'][$key]);
					continue;
				}
			}

			$room['whispers'] = array_reverse($room['whispers']);
		}

		foreach ( $room['talks'] as $key => &$talk )
		{
			if ( $talk['uid'] == '' )
			{
				$name = $talk['name'];
				$talk['message'] = t($talk['message'], $name);
			}
		}
		unset($talk);

		$this->output['room'] = $room();

		$this->output['limit'] = array(
			'min'  => $this->minLimit,
			'max'  => $this->maxLimit
		);

		$this->output['languages'] = $this->languages;

		$this->output['user'] = array(
			'id'     => $userId,
			'name'   => $userName,
			'icon'   => $userIcon,
			'code'   => $userCode,
			'ip'     => $userIP,
			'avatar' => $userAvatar
		);

		$url = Dura::url(null, null, array('id' => $room['id'], 'invite' => $room['invite']));
		$url = urlencode($url);
		$title = t("I'm now chatting at room '{1}'!", $room['name']);
		$title = urlencode($title);
		$description = t("The {1} ({2}) is a chat site that uses DLC script.", DURA_TITLE, DURA_SUBTITLE);
		$description = urlencode($description);

		$this->output['social'] = array(
			'facebook' => 'http://www.facebook.com/sharer.php?u='.$url.'&t='.$title,
			'twitter'  => 'https://twitter.com/intent/tweet?source=mushoku&text='.$title.'&url='.$url,
			'google'   => 'https://plus.google.com/share?url='.$url,
			'tumblr'   => 'http://www.tumblr.com/share/link?url='.$url.'&name='.$title.'&description='.$description,
			'stumbleupon' => 'http://www.stumbleupon.com/submit?url='.$url.'&title='.$title,
			'digg'     => 'http://digg.com/submit?url='.$url.'&title='.$title
		);

		$this->_view();
	}

	protected function _isLogin()
	{
		$users = $this->roomModel['users'];
		$id = Dura::user()->getId();

		foreach ( $users as $user )
		{
			if ( $id == $user['id'] )
			{
				return true;
			}
		}

		return false;
	}

	protected function _moveHostRight()
	{
		foreach ( $this->roomModel['users'] as $user )
		{
			$this->roomModel['host'] = $user['id'];
			$nextHost = $user['name'];
			break;
		}

		$this->_npcNewHost($nextHost);
	}

	protected function _changeRoomDetail()
	{
		if ( !$this->_isHost() )
		{
			die(t("You are not host."));
		}

		$roomName = Dura::post('room_name');
		$roomName = Dura::trim($roomName, false);
		$roomLanguage = Dura::post('room_language');

		if ( $roomName === '' )
		{
			die(t("Room name is blank."));
		}

		if ( mb_strlen($roomName) > 12 )
		{
			die(t("Name should be less than 12 letters."));
		}

		if ( Dura_Class_NgWord::isNG($roomName) )
		{
			die(t("Name should contain appropriate words."));
		}

		$this->_languages();

		if ( !in_array($roomLanguage, array_keys($this->languages)) )
		{
			die(t("The language is not in the option."));
		}

		$this->roomModel['name'] = $roomName;
		$this->roomModel['language'] = $roomLanguage;

		$this->roomHandler->save($this->id, $this->roomModel);

		die(t("Room detail is modified."));
	}

	protected function _changeRoomLimit()
	{
		if ( !$this->_isHost() )
		{
			die(t("You are not host."));
		}

		$roomLimit = (int) Dura::post('room_limit');

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

		if ( $usedCapacity - $this->roomModel['limit'] >= DURA_SITE_USER_CAPACITY )
		{
			die(t("Cannot update room limit anymore."));
		}

		if ( $roomLimit < DURA_USER_MIN )
		{
			die(t("Member should be more than {1}.", DURA_USER_MIN));
		}

		if ( $roomLimit < count($this->roomModel['users']) )
		{
			die(t("Member should be more than {1}.", count($this->roomModel['users'])));
		}

		$userMax = DURA_SITE_USER_CAPACITY - ($usedCapacity - $this->roomModel['limit']);

		if ( $roomLimit > $userMax && $userMax <= DURA_USER_MAX )
		{
			die(t("Member should be less than {1}.", $userMax));
		}

		if ( $roomLimit > DURA_USER_MAX )
		{
			die(t("Member should be less than {1}.", DURA_USER_MAX));
		}

		$this->roomModel['limit'] = $roomLimit;

		$this->roomHandler->save($this->id, $this->roomModel);

		die(t("Room limit is modified."));
	}

	protected function _changeRoomPassword()
	{
		if ( !$this->_isHost() )
		{
			die(t("You are not host."));
		}

		$roomPassword = Dura::post('room_password');
		$roomPassword = Dura::trim($roomPassword);

		if ( mb_strlen($roomPassword) > 25 )
		{
			die(t("Password should be less than 25 characters."));
		}

		$this->roomModel['password'] = $roomPassword;

		$this->roomHandler->save($this->id, $this->roomModel);

		die(t("Room password is modified."));
	}

	protected function _computeRoomLimit()
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

		$usedCapacity = ($usedCapacity - $this->roomModel['limit']);

		$this->minLimit = 0;

		if ( (DURA_SITE_USER_CAPACITY - $usedCapacity) >= DURA_USER_MIN )
		{
			$this->minLimit = DURA_USER_MIN;
		}

		$this->maxLimit = 0;

		if ( (DURA_SITE_USER_CAPACITY - $usedCapacity) >= DURA_USER_MAX )
		{
			$this->maxLimit = DURA_USER_MAX;
		}
		elseif ( (DURA_SITE_USER_CAPACITY - $usedCapacity) >= DURA_USER_MIN && (DURA_SITE_USER_CAPACITY - $usedCapacity) < DURA_USER_MAX )
		{
			$this->maxLimit = DURA_SITE_USER_CAPACITY - $usedCapacity;
		}
	}

	protected function _languages()
	{
		$this->languages = Dura::getLanguages();
	}

	protected function _removeBlock()
	{
		if ( !$this->_isHost() )
		{
			die(t("You are not host."));
		}

		$blockId = Dura::post('blocked_ip');

		if ( $blockId === '' )
		{
			die(t("IP is invalid."));
		}

		$blockFound = false;

		if ( !empty($this->roomModel['bans']) )
		{
			foreach ( $this->roomModel['bans'] as $key => $ban )
			{
				if ( $blockId == $ban['id'] )
				{
					$blockFound = true;
					$blockIP = $ban['ip'];

					unset($this->roomModel['bans'][$key]);
					break;
				}
			}
			$this->roomModel['bans'] = array_values($this->roomModel['bans']);
		}

		if ( !$blockFound )
		{
			die(t("IP not found."));
		}

		$this->roomHandler->save($this->id, $this->roomModel);

		die(t("Removed {1}.", Dura::maskIP($blockIP)));
	}

	protected function _handoverHostRight()
	{
		if ( !$this->_isHost() )
		{
			die(t("You are not host."));
		}

		if ( $this->roomModel['permanent'] )
		{
			die(t("Permanent rooms cannot be handed over."));
		}

		$nextHostId = Dura::post('new_host');

		if ( $nextHostId === '' || $this->_isHost($nextHostId) )
		{
			die(t("Host is invaild."));
		}

		$userFound = false;

		foreach ( $this->roomModel['users'] as $user )
		{
			if ( $nextHostId == $user['id'] )
			{
				$userFound = true;
				$nextHost  = $user['name'];
				break;
			}
		}

		if ( !$userFound )
		{
			die(t("User not found."));
		}

		$this->roomModel['host'] = $nextHostId;

		$this->_npcNewHost($nextHost);

		$this->_weepTalk();

		$this->roomHandler->save($this->id, $this->roomModel);

		die(t("Gave host rights to {1}.", Dura::decodeHtml($nextHost)));
	}

	protected function _banUser()
	{
		if ( !$this->_isHost() )
		{
			die(t("You are not host."));
		}

		$userId = Dura::post('ban_user');

		if ( $userId === '' || $this->_isHost($userId) )
		{
			die(t("User is invaild."));
		}

		$userFound = false;

		foreach ( $this->roomModel['users'] as $key => $user )
		{
			if ( $userId == $user['id'] )
			{
				$userFound = true;
				$userName  = $user['name'];
				$userIP    = $user['ip'];

				unset($this->roomModel['users'][$key]);
				break;
			}
		}
		$this->roomModel['users'] = array_values($this->roomModel['users']);

		if ( !$userFound )
		{
			die(t("User not found."));
		}

		if ( !empty($this->roomModel['bans']) )
		{
			foreach ( $this->roomModel['bans'] as $ban )
			{
				if ( Dura::hash($userIP) == $ban['id'] )
				{
					die(t("IP already banned."));
				}
			}
		}

		$ban = array();
		$ban['id'] = Dura::hash($userIP);
		$ban['ip'] = $userIP;

		if ( empty($this->roomModel['bans']) )
		{
			$this->roomModel['bans'] = array();
		}

		$this->roomModel['bans'][] = $ban;

		$this->_removeWhisper($userId);

		$this->_npcDisconnect($userName);

		$this->_weepTalk();

		$this->roomHandler->save($this->id, $this->roomModel);

		die(t("Banned {1}.", Dura::decodeHtml($userName)));
	}

	protected function _kickUser()
	{
		if ( !$this->_isHost() )
		{
			die(t("You are not host."));
		}

		$userId = Dura::post('kick_user');

		if ( $userId === '' || $this->_isHost($userId) )
		{
			die(t("User is invaild."));
		}

		$userFound = false;

		foreach ( $this->roomModel['users'] as $key => $user )
		{
			if ( $userId == $user['id'] )
			{
				$userFound = true;
				$userName  = $user['name'];

				unset($this->roomModel['users'][$key]);
				break;
			}
		}
		$this->roomModel['users'] = array_values($this->roomModel['users']);

		if ( !$userFound )
		{
			die(t("User not found."));
		}

		$this->_removeWhisper($userId);

		$this->_npcDisconnect($userName);

		$this->_weepTalk();

		$this->roomHandler->save($this->id, $this->roomModel);

		die(t("Kicked {1}.", Dura::decodeHtml($userName)));
	}

	protected function _isHost($userId = null)
	{
		if ( $userId === null )
		{
			$userId = Dura::user()->getId();
		}

		return ( $userId == $this->roomModel['host'] );
	}

	protected function _npcLogin($userName)
	{
		$talk = array();
		$talk['id'] = md5(microtime().mt_rand());
		$talk['uid'] = '';
		$talk['name'] = $userName;
		$talk['message'] = "{1} logged in.";
		$talk['icon'] = '';
		$talk['time'] = microtime(true);

		$this->roomModel['talks'][] = $talk;
	}

	protected function _npcLogout($userName)
	{
		$talk = array();
		$talk['id'] = md5(microtime().mt_rand());
		$talk['uid'] = '';
		$talk['name'] = $userName;
		$talk['message'] = "{1} logged out.";
		$talk['icon'] = '';
		$talk['time'] = microtime(true);

		$this->roomModel['talks'][] = $talk;
	}

	protected function _npcDisconnect($userName)
	{
		$talk = array();
		$talk['id'] = md5(microtime().mt_rand());
		$talk['uid'] = '';
		$talk['name'] = $userName;
		$talk['message'] = "{1} lost the connection.";
		$talk['icon'] = '';
		$talk['time'] = microtime(true);

		$this->roomModel['talks'][] = $talk;
	}

	protected function _npcNewHost($userName)
	{
		$talk = array();
		$talk['id'] = md5(microtime().mt_rand());
		$talk['uid'] = '';
		$talk['name'] = $userName;
		$talk['message'] = "{1} is a new host.";
		$talk['icon'] = '';
		$talk['time'] = microtime(true);

		$this->roomModel['talks'][] = $talk;
	}

	protected function _npcRoomEmpty()
	{
		$talk = array();
		$talk['id'] = md5(microtime().mt_rand());
		$talk['uid'] = '';
		$talk['name'] = '';
		$talk['message'] = "No users in chat.";
		$talk['icon'] = '';
		$talk['time'] = microtime(true);

		$this->roomModel['talks'][] = $talk;
	}

	protected function _weepTalk()
	{
		while ( count($this->roomModel['talks']) > DURA_LOG_LIMIT )
		{
			array_shift($this->roomModel['talks']);
		}
	}

	protected function _weepWhisper($id = null)
	{
		if ( $id === null )
		{
			$id = Dura::user()->getId();
		}

		// Unset only the user's whisper messages
		// Every user is limited to the value of DURA_LOG_LIMIT (Total = DURA_LOG_LIMIT * DURA_USER_MAX)
		/*
		$whisperCount = 0;
		foreach ( $this->roomModel['whispers'] as $whisper )
		{
			if ( $whisper['uid'] == $id )
			{
				$whisperCount++;
			}
		}
		if ( $whisperCount > DURA_LOG_LIMIT )
		{
			$i = 0;
			$excessCount = ($whisperCount - DURA_LOG_LIMIT);
			foreach ( $this->roomModel['whispers'] as $key => $whisper )
			{
				if ( $whisper['uid'] == $id )
				{
					unset($this->roomModel['whispers'][$key]);
					$i++;

					if ( $i >= $excessCount ) break;
				}
			}
			$this->roomModel['whispers'] = array_values($this->roomModel['whispers']);
		}
		*/

		// Delete based on a per user private mode basis
		// User pairs are limited to DURA_LOG_LIMIT (Total = DURA_LOG_LIMIT * (DURA_USER_MAX * (DURA_USER_MAX - 1) / 2))
		$whisperCount = array();
		foreach ( $this->roomModel['users'] as $user )
		{
			$userId = $user['id'];
			if ( $userId != $id )
			{
				$whisperCount[$userId] = 0;
				foreach ( $this->roomModel['whispers'] as $whisper )
				{
					$whisperUserId = $whisper['uid'];
					$whisperRecipientId = $whisper['rid'];

					if ( ( $whisperUserId == $id && $whisperRecipientId == $userId ) || ( $whisperUserId == $userId && $whisperRecipientId == $id ) )
					{
						$whisperCount[$userId]++;
					}
				}
			}
		}

		if ( count($whisperCount) )
		{
			foreach ( $whisperCount as $userId => $privateCount )
			{
				if ( $privateCount > DURA_LOG_LIMIT )
				{
					$i = 0;
					$excessCount = ($privateCount - DURA_LOG_LIMIT);
					foreach ( $this->roomModel['whispers'] as $key => $whisper )
					{
						$whisperUserId = $whisper['uid'];
						$whisperRecipientId = $whisper['rid'];

						if ( ( $whisperUserId == $id && $whisperRecipientId == $userId ) || ( $whisperUserId == $userId && $whisperRecipientId == $id ) )
						{
							unset($this->roomModel['whispers'][$key]);
							$i++;

							if ( $i >= $excessCount ) break;
						}
					}
				}
				// echo "{$userId} = {$privateCount}" . PHP_EOL;
			}
			$this->roomModel['whispers'] = array_values($this->roomModel['whispers']);
		}
	}

	protected function _removeWhisper($userId = null)
	{
		if ( $userId === null )
		{
			$userId = Dura::user()->getId();
		}

		foreach ( $this->roomModel['whispers'] as $key => $whisper )
		{
			if ( $whisper['uid'] == $userId || $whisper['rid'] == $userId )
			{
				unset($this->roomModel['whispers'][$key]);
			}
		}
		$this->roomModel['whispers'] = array_values($this->roomModel['whispers']);
	}
}
