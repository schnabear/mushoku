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

class Dura_Controller_Error extends Dura_Abstract_Controller
{
	public function __construct()
	{
		parent::__construct();

		if ( Dura::$action == 'default' )
		{
			Dura::$Action = Dura::$action = '404';
		}
	}

	public function main()
	{
		if ( !$this->template )
		{
			$this->template = DURA_TEMPLATE_PATH.'/'.Dura::$controller.'.'.Dura::$action.'.php';
		}

		if ( !file_exists($this->template) )
		{
			die("Not Found");
		}

		require $this->template;
		die;
	}
}
