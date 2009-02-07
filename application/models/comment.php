<?php defined('SYSPATH') OR die('No direct access allowed.');

class Comment_Model extends ORM
{
	// Relationships
	/**
	 * Defines belong to relationships
	 *
	 * @var array
	 */
	protected $belongs_to = array('post', 'user');

	// Ignored columns
	/**
	 * POST columns to ignore when saving
	 *
	 * @var array
	 * @access protected
	 */
	protected $ignored_columns = array('auth_key');

	/**
	 * Overloads the save method to generate a new auth key on each login
	 *
	 * @return boolean
	 * @access public
	 * @author Sam Clark
	 */
	public function save()
	{
		if ( ! isset($this->object['date_created']))
			$this->date_created = time();

		return parent::save();
	}

} // End Comment Model