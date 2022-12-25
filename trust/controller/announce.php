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

class Dura_Controller_Announce extends Dura_Abstract_Controller
{
	protected $roomHandler = null;
	protected $roomModels  = null;

	public function __construct()
	{
		parent::__construct();

		$this->_validateAdmin();

		$this->roomHandler = new Dura_Model_RoomHandler;
		$this->roomModels = $this->roomHandler->loadAll();
	}

	public function main()
	{
		if ( Dura::post('message') )
		{
			$this->_message();
		}

		$this->_default();
	}

	protected function _message()
	{
		$message = Dura::post('message');
		$message = Dura::trim($message, false);
		$messageId = md5(microtime().mt_rand());

		if ( !$message ) return;

		if ( mb_strlen($message) > DURA_MESSAGE_MAX_LENGTH )
		{
			$message = mb_substr($message, 0, DURA_MESSAGE_MAX_LENGTH).'...';
		}

		$name = Dura::user()->getName();
		$code = Dura::user()->getCode();

		$talk = array();
		$talk['id']   = $messageId;
		$talk['uid']  = Dura::user()->getId();
		$talk['name'] = $name;
		$talk['code'] = $code;
		$talk['message'] = $message;
		$talk['icon'] = Dura::user()->getIcon();
		$talk['time'] = microtime(true);

		foreach ( $this->roomModels as $roomId => $roomModel )
		{
			$roomModel['talks'][] = $talk;

			$id = Dura::user()->getId();

			foreach ( $roomModel['users'] as &$user )
			{
				if ( $id == $user['id'] )
				{
					$user['update'] = time();
					break;
				}
			}
			unset($user);

			while ( count($roomModel['talks']) > DURA_LOG_LIMIT )
			{
				array_shift($roomModel['talks']);
			}

			$this->roomHandler->save($roomId, $roomModel);
		}

		Dura::redirect('announce');
	}

	protected function _default()
	{
		$talks = array();
		$userId = Dura::user()->getId();

		foreach ( $this->roomModels as $roomModel )
		{
			foreach ( $roomModel['talks'] as $talk )
			{
				if ( $talk['uid'] == '' )
				{
					$talk['message'] = t($talk['message'], $talk['name']);
				}

				$time = (string) $talk['time'];
				$id   = (string) $talk['id'];

				if ( isset($talks[$time][$id]) ) continue;

				$talks[$time][$id] = $talk;
			}
		}

		krsort($talks);

		$talksSlice = array();
		$talksCount = 0;
		foreach ( $talks as $time => $talk )
		{
			foreach ( $talk as $id => $content )
			{
				if ( $talksCount < DURA_LOG_LIMIT )
				{
					$talksSlice[$time][$id] = $content;
				}
				else
				{
					break 2;
				}

				$talksCount++;
			}
		}

		unset($talks);

		$this->output['talks'] = $talksSlice;

		$this->_view();
	}
}
