<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Core
 *
 * Sets the default route to "index"
 */
$config['_default'] = 'index';

/**
 * Routes http://host/yyyy/mm/dd/slug to the default handler
 *
 * @author Sam Clark
 */
$config['([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([A-Za-z0-9_\- ]+)'] = 'index/index/$4';