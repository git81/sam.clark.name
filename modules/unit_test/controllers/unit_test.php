<?php
/**
 * Unit_Test controller.
 *
 * $Id: unit_test.php 3700 2008-11-22 20:35:48Z Shadowhand $
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Unit_test_Controller extends Controller {

	const ALLOW_PRODUCTION = FALSE;

	public function index()
	{
		// Run tests and show results!
		echo new Unit_Test;
	}

}