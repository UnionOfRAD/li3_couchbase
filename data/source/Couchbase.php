<?php
/**
 * li3_couchbase: A Couchbase datasource for Lithium.
 *
 * @copyright     Copyright 2012, Michael Nitschinger
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_couchbase\data\source;

use Exception;
use Couchbase as CouchbaseExt;
use lithium\core\NetworkException;

/**
 * A data source adapter which allows you to connect to the Couchbase database engine.
 */
class Couchbase extends \lithium\data\Source {

	/**
	 *
	 */
	public function __construct(array $config = array()) {
		$defaults = array(
			'host'       => 'localhost',
			'port'       => '8091',
			'login'      => null,
			'password'   => null,
			'bucket'     => 'default',
			'persistent' => false
		);
		parent::__construct($config + $defaults);
	}

	/**
	 * With no parameter, checks to see if the `couchbase` extension is installed. With a
	 * parameter, queries for a specific supported feature.
	 *
	 * @param string $feature Test for support for a specific feature, i.e. `"arrays"`.
	 * @return boolean Returns `true` if the particular feature support is enabled (or `false`).
	 */
	public static function enabled($feature = null) {
		if (!$feature) {
			return extension_loaded('couchbase');
		}
		$features = array(
			'arrays' => true,
			'transactions' => false,
			'booleans' => true,
			'relationships' => false
		);
		return isset($features[$feature]) ? $features[$feature] : null;
	}

	/**
	 *
	 */
	public function connect() {
		$config = $this->_config;
		$this->_isConnected = false;

		$host = $config['host'].':'.$config['port'];
		$login = $config['login'];
		$password = $config['password'];
		$bucket = $config['bucket'];
		$persistent = $config['persistent'];

		try {
			$this->connection = new CouchbaseExt($host, $login, $password, $bucket, $persistent);
		} catch(Exception $e) {
			throw new NetworkException("Could not connect to the database: " . $e->getMessage(), 503);
			return false;
		}

		return $this->_isConnected = true;
	}

	/**
	 *
	 */
	public function disconnect() {
		$this->_isConnected = false;
		unset($this->connection);
		return true;
	}

	/**
	 *
	 */
	public function sources($class = null) {}

	/**
	 *
	 */
	public function describe($entity, array $meta = array()) {}

	/**
	 *
	 */
	public function relationship($class, $type, $name, array $options = array()) {}

	/**
	 *
	 */
	public function create($query, array $options = array()) {}

	/**
	 *
	 */
	public function read($query, array $options = array()) {}

	/**
	 *
	 */
	public function update($query, array $options = array()) {}

	/**
	 *
	 */
	public function delete($query, array $options = array()) {}

}

?>