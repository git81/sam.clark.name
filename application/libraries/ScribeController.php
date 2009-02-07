<?php defined('SYSPATH') OR die('No direct access allowed.');

class ScribeController_Core extends Controller
{
	public function __construct()
	{
		parent::__construct();
		$firephp = new Fire_Profiler;
	}

} // End