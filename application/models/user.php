<?php defined('SYSPATH') OR die('No direct access allowed.');

class User_Model extends Auth_User_Model
{
	// Relationships
	/**
	 * Has many relationships
	 *
	 * @var array
	 * @access protected
	 */
	protected $has_many = array('user_tokens', 'posts', 'comments');

	/**
	 * Has and belongs to many relationships
	 *
	 * @var array
	 * @access protected
	 */
	protected $has_and_belongs_to_many = array('roles', 'tags');

	// Ignored columns
	/**
	 * POST columns to ignore when saving
	 *
	 * @var array
	 * @access protected
	 */
	protected $ignored_columns = array('password_confirm', 'auth_key');


	/**
	 * Defines the hash method to use
	 *
	 * @var string
	 * @access protected
	 */
	protected $hash_method = 'sha512';

	/**
	 * Overloads the save method to generate a new auth key on each login
	 *
	 * @return boolean
	 * @access public
	 * @author Sam Clark
	 */
	public function save()
	{
		if (array_key_exists('logins', $this->changed))
			$this->auth_key = self::generate_key($this->username, $this->logins, $this->name);

		if ( ! isset($this->date_created))
			$this->date_created = time();

		return parent::save();
	}

	/**
	 * Generates a new key based on n attributes
	 *
	 * @return string        The hashed key
	 * @return void          Nothing if fail
	 * @access public
	 * @author Sam Clark
	 */
	public static function generate_key()
	{
		$result = NULL;
		$vectors = func_get_args();

		if ($vectors)
		{
			$string = '';
			foreach ($vectors as $component)
				$string .= $component;

			$result = hash($this->hash_method, $string);
		}

		return $result;
	}

} // End User Model