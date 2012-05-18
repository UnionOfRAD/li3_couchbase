<?php
/**
 * li3_couchbase: A Couchbase datasource for Lithium.
 *
 * @copyright     Copyright 2012, Michael Nitschinger
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_couchbase\data\source;

/**
 * A data source adapter which allows you to connect to the Couchbase database engine.
 */
class Couchbase extends \lithium\data\Source {

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
	public function connect() {}

	/**
	 *
	 */
	public function disconnect() {}

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