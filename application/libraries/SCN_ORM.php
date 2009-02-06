<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * [Object Relational Mapping][ref-orm] (ORM) is a method of abstracting database
 * access to standard PHP calls. All table rows are represented as model objects,
 * with object properties representing row data. ORM in Kohana generally follows
 * the [Active Record][ref-act] pattern.
 *
 * [ref-orm]: http://wikipedia.org/wiki/Object-relational_mapping
 * [ref-act]: http://wikipedia.org/wiki/Active_record
 *
 * $Id: ORM.php 3909 2009-01-16 18:54:10Z jheathco $
 *
 * @package    ORM
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class ORM extends ORM_Core {

	/**
	 * Alias function for validate... maintains CRUD
	 *
	 * @param array          array  an array of Key Value pairs, usually POST
	 * @return boolean
	 * @access public
	 * @author Sam Clark
	 */
	public function create(array & $array)
	{
		return $this->validate($array, TRUE);
	}

	/**
	 * Checks whether a value is unique within the ORM model
	 *
	 * @param string         fieldname  the fieldname to search within
	 * @param string         value  the value to test against
	 * @return boolean TRUE if exists, FALSE if fails
	 * @author Sam Clark
	 */
	public function unique_value_exists($fieldname, $value)
	{
		$result = FALSE;
		
		if (array_key_exists($fieldname, $this->object))
		{
			$result = (bool) Database::instance()->from($this->table_name)
												 ->where($fieldname, $value)
												 ->count_records();
		}
		
		return $result;
	}
} // End ORM extension