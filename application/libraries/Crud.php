<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Interface for the CRUD methodology
 *
 * @package default
 * @author Sam Clark
 */
interface Crud
{
	/**
	 * The creating method
	 *
	 * @return void
	 * @access public
	 * @author Sam Clark
	 */
	public function create();
	
	/**
	 * The viewing method, or read in this case
	 *
	 * @param integer $id id of the record
	 * @param string $id unique key of the record
	 * @param integer $page the page to show (if applicable)
	 * @return void
	 * @access public
	 * @author Sam Clark
	 */
	public function read($id = NULL, $page = NULL);
	
	/**
	 * Updating method
	 *
	 * @param id $id Integer id of the record
	 * @param id $id String unique key of the record
	 * @return void
	 * @author Sam Clark
	 */
	public function update($id = NULL);
	
	/**
	 * Delete method for the supplied record
	 *
	 * @param id $id Integer id of the record
	 * @param id $id String unique key of the record
	 * @access public
	 * @return void
	 * @author Sam Clark
	 */
	public function delete($id = NULL);
} // End Crud Interface