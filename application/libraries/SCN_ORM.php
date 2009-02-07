<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    ORM
 * @author     Sam Clark
 * @copyright  (c) 2009 Polaris Digital
 * @license    http://kohanaphp.com/license.html
 */
class ORM extends ORM_Core {

	/**
	 * List of tables that are currently locked by this model
	 *
	 * @access public
	 * @author Sam Clark
	 */
	public static $table_locks = array();

	/**
	 * List of transactions currently operating
	 *
	 * @access public
	 * @author Sam Clark
	 */
	public static $in_transaction = array();

	/**
	 * Runs an appropriate event upon saving the model
	 *
	 * @return boolean
	 * @access public
	 * @author Sam Clark
	 */
	public function save()
	{
		if (isset($this->id))
			$event_name = $this->object_name.'.save';
		else
			$event_name = $this->object_name.'.create';
		
		Event::run($event_name, $this);
		
		return parent::save();
	}

	/**
	 * Adds a delete event before deletion
	 *
	 * @return void
	 * @access public
	 * @author Sam Clark
	 */
	public function delete()
	{
		Event::run($this->object_name.'.delete', $this);
		return parent::delete();
	}

	/**
	 * Discovers if this session is in a transaction
	 *
	 * @return boolean  TRUE if in transaction, FALSE otherwise
	 * @author Sam Clark
	 */
	public function in_transaction()
	{
		return (bool) in_array($this->session->id(), self::$in_transaction);
	}

	/**
	 * Discovers if this session has any table locks operating
	 *
	 * @return boolean
	 * @author Sam Clark
	 */
	public function in_table_lock()
	{
		return (bool) self::$table_locks;
	}

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

	/**
	 * Starts a new transaction for this session. If the consistent_snapshot argument is supplied all reads in the transaction will be isolated from other events in the system
	 *
	 * @param boolean            consistent_snapshot  if TRUE, includes 'WITH CONSISTENT TRANSACTION' with the query
	 * @return boolean
	 * @author Sam Clark
	 */
	public function start_transaction($consistent_snapshot = FALSE)
	{
		$result = FALSE;

		// This happens in MySQL by default, but better to be in control of ones own destiny
		if ($this->in_table_lock())
			$this->unlock_tables();

		if ( ! $this->in_transaction())
		{
			// Turn off autocommit
			$this->_set_autocommit(FALSE);

			$query = 'START TRANSACTION';

			if ($consistent_snapshot)
				$query .= ' WITH CONSISTENT SNAPSHOT';

			$this->db->query($query);

			self::$in_transaction += array($this->session->id());
		}
		return $result;
	}

	/**
	 * Commit the changes to the database
	 *
	 * @param array $sanity_check 
	 * @return void
	 * @author Sam Clark
	 */
	public function commit(array $sanity_check = NULL)
	{
		$result = FALSE;

		// If in a transaction
		if ($this->in_transaction())
		{
			// Run the sanity check if supplied
			$test = (isset($sanity_check)) ? $this->_run_sanity_check($sanity_check) : TRUE;

			// If the test was successful
			if ($test)
			{
				// Commit all changes to the DB
				$this->db->query('COMMIT');
				$this->_release_transaction_lock();
				$result = TRUE;
			}
			else // Else rollback baby!
				$this->rollback();
		}

		return (bool) $result;
	}

	/**
	 * Rollback the current transaction
	 *
	 * @return boolean
	 * @author Sam Clark
	 */
	public function rollback()
	{
		$result = FALSE;

		// If in a transaction
		if ($this->in_transaction())
		{
			$this->db->query('ROLLBACK');
			$this->_release_transaction_lock();
			$result = TRUE;
		}

		return (bool) $result;
	}

	/**
	 * Runs a sanity check on the current transaction. The tests stop as soon as a test fails.
	 *
	 * Here is an example of a valid sanity_check array
	 *
	 *	$sanity_check = array
	 *	(
	 *		'users.id'		=> array
	 *		(
	 *			'value'			=> 4,
	 *			'where'			=> array('id' => 4),
	 *		),
	 *	);
	 *
	 * The above array will test that the value of `users`.`id` = 4 where the id is 4... destined to pass in this instance
	 *
	 * @param array              sanity_check  an associative array of tests to run
	 * @return boolean           TRUE if all data is correct, FALSE otherwise.
	 * @author Sam Clark
	 */
	protected function _run_sanity_check(array & $sanity_check)
	{
		$result = FALSE;

		if ($this->in_transaction() AND $sanity_check)
		{
			$result = TRUE;

			foreach ($sanity_check as $table_field => $test)
			{
				list($table, $field) = explode('.', $table_field);

				$result = $this->db
							->select($field)
							->where($test['where'])
							->limit(1)
							->get($table);

				$result = (bool) ($test['value'] == $result->current()->$field);

				if ( ! $result )
					break;
			}
		}

		return (bool) $result;
	}

	/**
	 * Releases the transaction lock for this session
	 *
	 * @return boolean
	 * @author Sam Clark
	 */
	protected function _release_transaction_lock()
	{
		return (bool) $this->in_transaction() ? array_splice(self::$in_transaction, array_search($this->session->id(), self::$in_transaction), 1) : FALSE;
	}

	/**
	 * Locks tables with either READ or WRITE lock. All locks are recorded to avoid existing conflicts.
	 *
	 * @param array            args  array of table => locktype pairs, e.g. 'currencies' => 'read', 'users' => 'write'
	 * @return boolean
	 * @author Sam Clark
	 */
	public function lock_tables(array $args = NULL)
	{
		$result = FALSE;

		if ( ! self::$table_locks OR ! $this->in_transaction())
		{
			$lock_statement = 'LOCK TABLES ';
			$tables = '';
			$i = FALSE;

			foreach ($args as $table => $lock)
			{
				if ( ! array_key_exists($table, self::$table_locks))
				{
					self::$table_locks += array($table => $lock);
					$tables .= $i ? (', '.strtolower($table).' '.strtoupper($lock)) : (strtolower($table).' '.strtoupper($lock));
				}
				else
					throw new Kohana_Database_Exception('Lock conflict found on '.$table.' with lock '.$lock, self::$table_locks);

				if ( ! $i)
					$i = TRUE;
			}

			$lock_statement .= $tables;

			$this->db->query($lock_statement);

			$result = TRUE;
		}
		return (bool) $result;
	}

	/**
	 * Unlocks locked tables, if there are any
	 *
	 * @return boolean
	 * @author Sam Clark
	 */
	public function unlock_tables()
	{
		$result = FALSE;

		if (count(self::$table_locks) > 0)
		{
			$this->db->query('UNLOCK TABLES');
			self::$table_locks = array();
			$result = TRUE;
		}

		return (bool) $result;
	}

	/**
	 * Sets the autocommit setting if it isn't ready as set. This should only be used with transactions. Autocommit is set to 1 by default.
	 *
	 * @param boolean       mode TRUE to turn autocommit on, FALSE to turn it off
	 * @return void
	 * @author Sam Clark
	 */
	protected function _set_autocommit($mode = FALSE)
	{
		if( $mode !== (bool) $this->db->query('SELECT @@autocommit as autocommit')->current()->autocommit )
		{
			$mode = $mode ? 1 : 0;

			$this->db
				->instance()
				->query('SET autocommit='.$mode);
		}
		return;
	}

	/**
	 * Sets the transaction isolation level for either the current session or globally. By default, MySQL will be set to repeatable read.
	 * Please read the link supplied to understand what this does and why
	 *
	 * @link http://dev.mysql.com/doc/refman/5.0/en/set-transaction.html
	 * @param string         scope  The scope to set, either 'session' or 'global'
	 * @param string         isolation  The isolation level to set
	 * @return boolean       TRUE on success, FALSE on failure
	 * @author Sam Clark
	 */
	public function set_transaction_isolation($scope = 'session', $isolation = 'repeatable read')
	{
		$result = FALSE;

		if (in_array($scope, array('session', 'global')) AND in_array($isolation, array('read uncommited', 'read commited', 'repeatable read', 'serializable')))
		{
			$query = 'SET '.strtoupper($scope).' TRANSACTION ISOLATION LEVEL '.strtoupper($isolation);

			$this->db->query($query);

			// Check that it was set
			if ($this->get_transaction_isolation($scope) == $isolation)
				$result = TRUE;
		}

		return $result;
	}

	/**
	 * Gets the current isolation setting for transactions, either globally or for this session [default]
	 *
	 * @param string         scope  the scope of the transaction isolation to get, either 'session' or 'global'
	 * @return boolean       FALSE if there was a problem
	 * @return string        the current transaction isolation for the supplied scope
	 * @author Sam Clark
	 */
	public function get_transaction_isolation($scope = 'session')
	{
		$result = FALSE;

		if (in_array($scope, array('session', 'global')))
		{
			$query = 'SELECT @@'.strtoupper($scope).'.tx_isolation AS isolation';
			$result = $this
						->db
						->query($query)
						->current()
						->isolation;
			$result = strtolower(str_replace('-', ' ', $result));
		}

		return $result;
	}
} // End ORM extension