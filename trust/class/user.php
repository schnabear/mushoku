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

class Dura_Class_User
{
	protected $name = null;
	protected $icon = null;
	protected $id   = null;
	protected $code = null;
	protected $expire = null;
	protected $admin = false;
	protected $language = null;
	protected $ip   = null;

	protected function __construct()
	{
	}

	public static function &getInstance()
	{
		static $instance = null;

		if ( $instance === null )
		{
			$instance = new self();
		}

		return $instance;
	}

	public function login($name, $icon, $code, $language, $admin = false)
	{
		if ( $admin )
		{
			$id = Dura::hash($name, DURA_SECURE_KEY);
		}
		else
		{
			// Proxy users with same name and ip has the same id
			$id = Dura::hash($name.Dura::fetchIP(), DURA_SECURE_KEY);
		}

		$this->name     = $name;
		$this->icon     = $icon;
		$this->id       = $id;
		$this->code     = $code;
		$this->language = $language;
		$this->admin    = $admin;
		$this->ip       = Dura::fetchIP();

		$_SESSION['user'] = $this;
	}

	public function loadSession()
	{
		if ( isset($_SESSION['user']) && $_SESSION['user'] instanceof self )
		{
			$user = $_SESSION['user'];
			$this->name   = $user->name;
			$this->icon   = $user->icon;
			$this->id     = $user->id;
			$this->code   = $user->code;
			$this->expire = $user->expire;
			$this->language = $user->language;
			$this->admin  = $user->admin;
			$this->ip     = $user->ip;
		}
	}

	public function isUser()
	{
		return ( $this->id !== null );
	}

	public function isAdmin()
	{
		if ( $this->isUser() )
		{
			return $this->admin;
		}

		return false;
	}

	public function getName()
	{
		if ( !$this->isUser() ) return false;

		return $this->name;
	}

	public function getIcon()
	{
		if ( !$this->isUser() ) return false;

		return $this->icon;
	}

	public function getId()
	{
		if ( !$this->isUser() ) return false;

		return $this->id;
	}

	public function getCode()
	{
		if ( !$this->isUser() ) return false;

		return $this->code;
	}

	public function hasCode()
	{
		if ( !$this->isUser() ) return false;

		return $this->code !== '';
	}

	public function getIP()
	{
		if ( !$this->isUser() ) return false;

		return $this->ip;
	}

	public function getLanguage()
	{
		return $this->language;
	}

	public function getExpire()
	{
		if ( !$this->isUser() ) return false;

		return $this->expire;
	}

	public function updateExpire()
	{
		$this->expire = time() + DURA_TIMEOUT;

		if ( isset($_SESSION['user']) && $_SESSION['user'] instanceof self )
		{
			$_SESSION['user']->expire = $this->expire;
		}
	}
}
