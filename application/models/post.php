<?php defined('SYSPATH') OR die('No direct access allowed.');

class Post_Model extends ORM_Tree
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
	protected $has_and_belongs_to_many = array('tags');

	/**
	 * Belongs to relationships
	 *
	 * @var array
	 * @access protected
	 */
	protected $belongs_to = array('user', 'tags');

	// Ignored columns
	/**
	 * POST columns to ignore when saving
	 *
	 * @var array
	 * @access protected
	 */
	protected $ignored_columns = array('auth_key', 'tags');

	/**
	 * Overloads the save method to generate a new auth key on each login
	 *
	 * @return boolean
	 * @access public
	 * @author Sam Clark
	 */
	public function save()
	{
		if ($this->changed)
			$this->date_modified = time();

		if ( ! isset($this->date_created))
			$this->date_created = time();

		if ($tags = Input::instance()->post('tags', FALSE))
			Tag_Model::factory()->parse($tags, $this);

		return parent::save();
	}

	/**
	 * Validates and optionally saves a new user record from an array.
	 *
	 * @param  array    values to check
	 * @param  boolean  save the record when validation succeeds
	 * @return boolean
	 */
	public function validate(array & $array, $save = FALSE)
	{
		$array = Validation::factory($array)
			->pre_filter('trim')
			->add_rules('slug', 'required', 'length[2,50]', 'chars[a-zA-Z0-9_.]', array($this, 'slug_exists'))
			->add_rules('title', 'required', 'length[4,150]');

		return parent::validate($array, $save);
	}

	/**
	 * Checks if the slug supplied exists already
	 *
	 * @param string         name  the slug to test against
	 * @return boolean
	 * @access public
	 * @author Sam Clark
	 */
	public function slug_exists($name)
	{
		return (bool) $this->unique_value_exists('slug', $name);
	}

	/**
	 * Overloads the unique key setting
	 *
	 * @param mixed          id  the id of this model in the database
	 * @return string
	 * @access public
	 * @author Sam Clark
	 */
	public function unique_key($id)
	{
		if ( ! empty($id) AND is_string($id) AND ! ctype_digit($id))
		{
			return 'slug';
		}

		return parent::unique_key($id);
	}
} // End