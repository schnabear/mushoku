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

class Dura_Controller_Admin extends Dura_Abstract_Controller
{
	protected $error = null;

	public function __construct()
	{
		parent::__construct();
	}

	public function main()
	{
		if ( Dura::user()->isUser() )
		{
			Dura::redirect('lounge');
		}

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

		$this->_default();
	}

	protected function _login()
	{
		$name = Dura::post('name');
		$name = Dura::trim($name, false);
		$pass = Dura::post('pass');

		if ( $name === '' )
		{
			throw new Exception(t("Please input name."));
		}

		$token = Dura::post('token');

		if ( !Dura_Class_Ticket::check($token) )
		{
			throw new Exception(t("Login error happened."));
		}

		if ( defined('DURA_ADMIN_NAME') && defined('DURA_ADMIN_PASS') )
		{
			if ( $name !== DURA_ADMIN_NAME or $pass !== DURA_ADMIN_PASS )
			{
				throw new Exception(t("ID or password is wrong."));
			}
		}
		else
		{
			$duraAdmin = $GLOBALS['duraAdmin'];
			$verified  = false;

			foreach ( $duraAdmin as $account )
			{
				if ( $account['name'] === $name && $account['pass'] === $pass )
				{
					$verified = true;
					break;
				}
			}

			if ( !$verified )
			{
				throw new Exception(t("ID or password is wrong."));
			}
		}

		$user =& Dura_Class_User::getInstance();
		$user->login($name, 'admin', '', DURA_LANGUAGE, true);

		Dura_Class_Ticket::destory();

		Dura::redirect('lounge');
	}

	protected function _default()
	{
		$this->output['error'] = $this->error;
		$this->output['token'] = Dura_Class_Ticket::issue();
		$this->_view();
	}
}
