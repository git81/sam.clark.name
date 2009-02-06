<?php defined('SYSPATH') OR die('No direct access allowed.');

class Tag_Model extends ORM
{
	// Relationships
	/**
	 * Has and belongs to many relationships
	 *
	 * @var array
	 * @access protected
	 */
	protected $has_and_belongs_to_many = array('posts', 'users');

	/**
	 * Parses a string of tags and assigns individual tags to the supplied model. Will also remove any tags not
	 *
	 * @param string         tags  a string of tags
	 * @param ORM            model  model to assign the tags to
	 * @param boolean        clean  clean models existing tags not defined in the tag string
	 * @param string         separator  the separator search string to split the string by
	 * @return boolean
	 * @access public
	 * @author Sam Clark
	 */
	public function parse($tags, ORM & $model, $clean = FALSE, $separator = ',')
	{
		$result = FALSE;

		// Test model for tag relationship
		if ($this->related_object($model)
		{
			// Format the new tags
			$tags = self::split_tags($tags, $separator);

			// Clean from the model existing tags missing from the tag string
			if ($clean)
			{
				// Load any existing tags
				$existing_tags = $model->tags->select_list();

				foreach ($existing_tags as $tag)
				{
					$tag = new Tag_Model($tag);

					if ( ! in_array($tag->tag, $tags))
						$tag->remove($model);

					$tag->save();
				}
			}

			// Process new tags
			foreach ($tags as $tag)
			{
				$tag = new Tag_Model($tag);

				if ( ! $tag->has($model))
					$tag->add($model);

				$tag->save();
			}

			$result = TRUE;
		}

		return $result;
	}

	/**
	 * Splits a tag string into an array
	 *
	 * @param string         tags  a string of tags separated
	 * @param string         separator  the separator used to split the string [Default = ,]
	 * @return array
	 * @access public
	 * @author Sam Clark
	 */
	public static function split_tags($tags, $separator = ',')
	{
		$tags = trim(strtolower($tags));

		if (strstr($tags, $separator) !== FALSE)
			$tags = explode($separator, $tags);
		else
			$tags = array($tags);

		return $tags;
	}

	/**
	 * Creates a tag string from the tags relating to the supplied model
	 *
	 * @param ORM            model  the model to examine
	 * @param string         separator  the separator used to split the string [Default = ' ,']
	 * @return string
	 * @access public
	 * @author Sam Clark
	 */
	public static function generate_tag_string(ORM & $model, $separator = ', ')
	{
		$tags = $model->tags;

		$result = '';

		if ($tags->count())
		{
			$result = implode($separator, $tags->select_list());
		}

		return $result;
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
			return 'tag';
		}

		return parent::unique_key($id);
	}
}