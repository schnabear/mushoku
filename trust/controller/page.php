<?php
/**
 * A simple description for this script
 *
 * PHP Version 5.3.0 or Upper version
 *
 * @package    Dura
 * @author     schnabear
 * @copyright  2012 schnabear
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 *
 */

class Dura_Controller_Page extends Dura_Abstract_Controller
{
	public function __construct()
	{
		parent::__construct();

		if ( Dura::$action == 'default' )
		{
			Dura::$Action = Dura::$action = 'about';
		}
	}

	public function main()
	{
		$this->_redirectToRoom();

		$this->_default();
	}

	protected function _redirectToRoom()
	{
		if ( Dura_Class_RoomSession::isCreated() )
		{
			Dura::redirect('room');
		}
	}

	protected function _default()
	{
		$this->_view();
	}
}
