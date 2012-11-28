<?php

namespace li3_couchbase\tests\mocks;

/**
 * Class-representation of the native Couchbase client for PHP
 *
 * Deprecated methods are currently not part of the official API
 */
class MockCouchbase {

	public function __construct(
		$host,
		$user = '',
		$password = '',
		$bucket = '',
		$persistent = ''
	) {}

	/**
     * Add a server to the connection pool
     *
     * @param string $host Addresses a server by hostname or numeric IP address
     * @param integer $port Specifies a TCP port number
     * @param integer $weight Defines the relative weight of a server in the connection pool.
     * @return object
     */
    public function addServer($host, $port, $weight = 0) {}

	/**
     * Returns the list of servers in the server pool
     *
     * @return array
     */
    public function getServerList() {}

	/**
	 * Retrieve an option
	 *
	 * @param integer $option Option controlling connection or server behavior
	 * @return mixed
	 */
	public function getOption($option) {}

	/**
	 * Specify an option
	 *
	 * @param integer $option Option controlling connection or server behavior
	 * @param mixed $value
	 * @return bool
	 */
	public function setOption($option, $value) {}

	/**
	 * Add a value with the specified key that does not already exist.
	 * Will fail if the key/value pair already exist.
	 *
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param mixed $value Value to be stored
	 * @param integer $expiry Expiry time for key. Values larger than 30*24*60*60 seconds
	 *                        (30 days) are interpreted as absolute times (from the epoch).
	 * @return mixed
	 */
	public function add($key, $value, $expiry = 0) {}

	/**
	 * Update an existing key with a new value
	 *
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param mixed $value Value to be stored
	 * @param integer $expiry Expiry time for key. Values larger than 30*24*60*60 seconds
	 *                        (30 days) are interpreted as absolute times (from the epoch).
	 * @param float $cas
	 * @return mixed
	 */
	public function replace($key, $value, $expiry = 0, $cas = null) {}

	/**
	 * Store a value using the specified key, whether the key already exists or not.
	 * Will overwrite a value if the given key/value already exists.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param integer $expiry
	 * @param float $cas
	 * @return mixed
	 */
	public function set($key, $value, $expiry = 0, $cas = null) {}

	/**
	 * Store a value with a server key.
	 *
	 * @param string $master Master key used for consistent server references
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param mixed $value Value to be stored
	 * @param integer $expiry Expiry time for key. Values larger than 30*24*60*60 seconds
	 *                        (30 days) are interpreted as absolute times (from the epoch).
	 * @return mixed
	 */
	public function addByKey($master, $key, $value, $expiry = 0) {}

	/**
	 * Store a value using the specified key, whether the key already exists or not.
	 * Will overwrite a value if the given key/value already exists.
	 *
	 * @param string $master Master key used for consistent server references
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param mixed $value Value to be stored
	 * @param integer $expiry Expiry time for key. Values larger than 30*24*60*60 seconds
	 *                        (30 days) are interpreted as absolute times (from the epoch).
	 * @return mixed
	 */
	public function setByKey($master, $key, $value, $expiry = 0) {}

	/**
	 * Set Multiple key/value items at once on a specific server
	 *
	 * @param array $array List of things
	 * @param integer $expiry
	 * @return mixed
	 */
	public function setMultiByKey(array $values, $expiry = 0) {}

	/**
	 * Set Multiple key/value items at once
	 *
	 * @param array $values Associative array of key/value pairs
	 *						string $key Key used to reference the value. The key cannot contain
	 *                      control characters or whitespace.
	 *						mixed $value Value to be stored
	 * @param integer $expiry
	 * @return mixed
	 */
	public function setMulti(array $values, $expiry = 0) {}

	/**
	 * Deletes all values from a server
	 *
	 * @return boolean
	 */
	public function flush() {}

	/**
	 * Get one or more key values
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param string $callback Callback function or method to be called
	 * @param float $cas_token CAS token for conditional operations
	 * @return mixed
	 */
	public function get(
		$key,
		$callback = '',
		&$cas_token = null
	) {}

	/**
	 * Get one or more key values
	 *
	 * @param array $keys One or more keys used to reference a value
	 * @param array $cas_tokens CAS token for conditional operations
	 * @param string $flags Flags for storage options. Flags are ignored by the server byte
	 *                      preserved for use by the client.
	 * @param string $expiry
	 * @param string $lock
	 * @return mixed
	 */
	public function getMulti(
		array $keys,
		array &$cas_tokens = array(),
		$flags = ''
	) {}

	/**
	 * Get one or more key values
	 *
	 * @param string[] $keys One or more keys used to reference a value
	 * @param bool $with_cas Whether to return the CAS value for a key
	 * @param string $callback Callback function or method to be called
	 * @param string $expiry
	 * @param string $lock
	 * @return mixed
	 */
	public function getDelayed(
		array $keys,
		$with_cas = false,
		$callback = ''
	) {}

	/**
	 * Fetch a single delayed result set row
	 *
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param string $keyn One or more keys used to reference a value
	 * @return array
	 */
	public function fetch($key, $keyn = null) {}

	/**
	 * Fetch a full single delayed result set
	 *
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param string $keyn One or more keys used to reference a value
	 * @return array
	 */
	public function fetchAll($key, $keyn = null) {}

	/**
	 * Get one or more key values
	 *
	 * @param string $master Master key used for consistent server references
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param function $callback Callback function or method to be called
	 * @param string $casunique Unique value used to identify a key/value combination
	 * @return object
	 */
	public function getByKey($master, $key, $callback = null, $casunique = null) {}

	/**
	 * Get one or more key values
	 *
	 * @param string $master Master key used for consistent server references
	 * @param mixed $keyn One or more keys used to reference a value
	 * @param boolean $with_cas Whether to return the CAS value for a key
	 * @param function $cache_callback Callback function or method to be called
	 * @return object
	 */
	public function getDelayedByKey($master, $keyn, $with_cas = false, $callback = null) {}

	/**
	 * Get one or more key values
	 *
	 * @param string $master Master key used for consistent server references
	 * @param array $keys One or more keys used to reference a value
	 * @param array $cas_tokens CAS token for conditional operations
	 * @param string $flags Flags for storage options. Flags are ignored by the server byte
	 *                      preserved for use by the client.
	 * @param string $expiry
	 * @param string $lock
	 * @return mixed
	 */
	public function getMultiByKey(
		$master,
		array $keys,
		array &$cas_tokens = array(),
		$flags = ''
	) {}

	/**
	 * Returns the result code for the last operation
	 *
	 * @return string
	 */
	public function getResultCode() {}

	/**
	 * Returns the result message for the last operation
	 *
	 * @return string
	 */
	public function getResultMessage() {}

	/**
	 * Returns the versions of all servers in the server pool
	 *
	 * @param $resource
	 * @return array
	 */
	public function getVersion($resource) {}

	/**
	 * Append a value to an existing key
	 *
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param mixed $value Value to be stored
	 * @return mixed
	 */
	public function append($key, $value) {}

	/**
	 * Compare and set a value providing the supplied CAS key matches
	 *
	 * @param float $cas Unique value used to identify a key/value combination
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param mixed $value Value to be stored
	 * @param integer $expiry Expiry time for key. Values larger than 30*24*60*60 seconds
	 *                        (30 days) are interpreted as absolute times (from the epoch).
	 * @return mixed
	 */
	public function cas($cas, $key, $value, $expiry = 0) {}

	/**
	 * Decrement the value of an existing numeric key. The Couchbase Server stores numbers as
	 * unsigned values. Therefore the lowest you can decrement is to zero.
	 *
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param integer $offset Integer offset value to increment/decrement (default 1)
	 * @param string $create
	 * @param string $expiry
	 * @param string $initial
	 * @return mixed
	 */
	public function decrement(
		$key,
		$offset = 1,
		$create = '',
		$expiry = '',
		$initial = ''
	) {}

	/**
	 * Delete a key/value
	 *
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param float $cas
	 * @return mixed
	 */
	public function delete($key, $cas = null) {}

	/**
	 * Increment the value of an existing numeric key. The Couchbase Server stores numbers as
	 * unsigned numbers, therefore if you try to increment an existing negative number, it will
	 * cause an integer overflow and return a non-logical numeric result. If a key does not exist,
	 * this method will initialize it with the zero or a specified value.
	 *
	 * @param string $key Key used to reference the value. The key cannot contain
	 *                    control characters or whitespace.
	 * @param integer $offset Integer offset value to increment/decrement (default 1)
	 * @param string $create
	 * @param string $expiry
	 * @param string $initial
	 * @return mixed
	 */
	public function increment(
		$key,
		$offset = 1,
		$create = '',
		$expiry = '',
		$initial = ''
	) {}

	/**
	 * Prepend a value to an existing key
	 *
	 * @param string $key Key used to reference the value. The key cannot contain
	 *                    control characters or whitespace.
	 * @param mixed $value Value to be stored
	 * @param integer $expiry
	 * @param float $cas
	 * @return mixed
	 */
	public function prepend($key, $value, $expiry = 0, $cas = null) {}

	/**
	 * Update the expiry time of an item
	 *
	 * @param string $key Key used to reference the value. The key cannot contain control
	 *                    characters or whitespace.
	 * @param integer $expiry Expiry time for key. Values larger than 30*24*60*60 seconds
	 *                        (30 days) are interpreted as absolute times (from the epoch).
	 * @return bool
	 */
	public function touch($key, $expiry) {}

	/**
	 * @deprecated
	 */
	public function touchMulti(array $keys, $expiry) {}

	/**
	 * Get the database statistics
	 *
	 * @return array
	 */
	public function getStats() {}

	/**
	 * Get result of a view
	 *
	 * @see http://www.couchbase.com/docs/couchbase-manual-2.0/couchbase-views-querying-rest-api.html
	 * @param string $doc_name Name of the document the views are grouped in
	 * @param string $view_name Viewname
	 * @param array $options Options like "startkey", "endkey" and so on. See doc.
	 * @param string $return_errors
	 * @return mixed
	 */
	public function view(
		$doc_name,
		$view_name = '',
		array $options = array(),
		$return_errors = ''
	) {}

}