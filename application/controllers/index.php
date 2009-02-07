<?php defined('SYSPATH') OR die('No direct access allowed.');

class Index_Controller extends ScribeController
{
	public function index()
	{
		var_dump(Router::$routed_uri);
	}

} // End Index_Controller