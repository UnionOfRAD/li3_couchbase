<?php
/**
 * li3_couchbase: A Couchbase data source for Lithium.
 *
 * @copyright     Copyright 2013, Union Of RAD
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_couchbase\extensions\data\source;

use Couchbase as Couch;
use lithium\core\NetworkException;
use lithium\core\Environment;
use lithium\util\Inflector;
use lithium\util\String;

/**
 * A data source adapter which allows you to connect to the Couchbase database engine.
 */
class Couchbase extends \lithium\data\Source {

	/**
	 * The Couchbase HTTP Service class instance.
	 *
	 * @var object
	 */
	public $service = null;

	/**
	 * The Couchbase object instance.
	 *
	 * @var object
	 */
	public $connection = null;

	/**
	 * Classes used by this class.
	 *
	 * @var array
	 */
	protected $_classes = array(
		'entity'       => 'lithium\data\entity\Document',
		'set'          => 'lithium\data\collection\DocumentSet',
		'result'       => 'lithium\data\source\Result',
		'schema'       => 'li3_couchbase\extensions\data\source\couchbase\Schema',
		'relationship' => 'lithium\data\model\Relationship'
	);

	/**
	 * A closure or anonymous function which receives an instance of this class, a collection name
	 * and associated meta information, and returns an array defining the schema for an associated
	 * model, where the keys are field names, and the values are arrays defining the type
	 * information for each field. At a minimum, type arrays must contain a `'type'` key. For more
	 * information on schema definitions, and an example schema callback implementation, see the
	 * `$_schema` property of the `Model` class.
	 *
	 * @see lithium\data\Model::$_schema
	 * @var Closure
	 */
	protected $_schema = null;

	/**
	 * List of configuration keys which will be automatically assigned to their corresponding
	 * protected class properties.
	 *
	 * @var array
	 */
	protected $_autoConfig = array('schema', 'classes' => 'merge');

	/**
	 * List of views indexed by design document
	 *
	 * @var array
	 */
	protected $_views = array();

	/**
	 * Map of sources to classes
	 *
	 * @var array
	 */
	protected $_classMap = array();

	/**
	 * Design document prefix
	 *
	 * @var string
	 */
	public $prefix = '';

	/**
	 * Constructor.
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		$defaults = array(
			'host'        => 'localhost:8091',
			'login'       => null,
			'password'    => null,
			'database'    => 'default',
			'persistent'  => true,
			'autoViews'   => true,
			'createViews' => true,
			'prefix'      => 'dev_'
		);
		parent::__construct($config + $defaults);
		$this->prefix = $this->_config['prefix'];
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
	 * Configures a model class by overriding the default dependencies for `'set'` and
	 * `'entity'` , and sets the primary key to `'_id'`, in keeping with Mongo's conventions.
	 *
	 * @see lithium\data\Model::$_meta
	 * @see lithium\data\Model::$_classes
	 * @param string $class The fully-namespaced model class name to be configured.
	 * @return Returns an array containing keys `'classes'` and `'meta'`, which will be merged with
	 *         their respective properties in `Model`.
	 */
	public function configureClass($class) {
		$pieces = explode('\\', $class);
		$model  = array_pop($pieces);
		$source = Inflector::tableize($model);
		$this->_classMap[$source] = $class;
		$this->registerViews($source);
		return array('schema' => array(), 'meta' => array('key' => 'id', 'locked' => false));
	}

	/**
	 * Introspects Couchbase for views, and automatically registers Model finders
	 *
	 * @param $source Couchbase view name
	 */
	public function registerViews($source) {
		$class = $this->_classMap[$source];
		$this->_views[$source] = json_decode($this->getDesignDoc("{$this->prefix}{$source}"),
			true);
		if (isset($this->_views[$source])) {
			$this->_views[$source] = $this->_views[$source]['views'];
			if ($this->_views[$source]) {
				foreach ($this->_views[$source] as $k => $v) {
					$class::finder($k, array('conditions' => array('view' => $k)));
				}
			}
		}
	}

	/**
	 * Connects to the database using the options provided to the class constructor.
	 *
	 * @return boolean Returns `true` if a database connection could be established, otherwise
	 *         `false`.
	 */
	public function connect() {
		$config = $this->_config;
		$this->_isConnected = false;
		extract($config);
		try {
			$this->connection = new Couch($host, $login, $password, $database, $persistent);
		} catch(Exception $e) {
			throw new NetworkException("Could not connect to {$database} on {$host}.");
		}
		return $this->_isConnected = true;
	}

	/**
	 * Disconnects the adapter from the database.
	 *
	 * @return boolean Returns `true` on success, else `false`.
	 */
	public function disconnect() {
		$this->_isConnected = false;
		unset($this->connection);
		return true;
	}

	/**
	 * Returns an array of object types accessible through this database.
	 *
	 * @param object $class
	 * @return void
	 */
	public function sources($class = null) {
		return array();
	}

	/**
	 * Describe database
	 *
	 * @param object $collection
	 * @param array $schema Any schema data pre-defined by the model.
	 * @param array $meta
	 * @return void
	 */
	public function describe($collection, $fields = array(), array $meta = array()) {
		if (!$fields && ($func = $this->_schema)) {
			$fields = $func($this, $collection, $meta);
		}
		return $this->_instance('schema', compact('fields'));
	}


	/**
	 * Executes calculation-related queries, such as those required for `count`.
	 *
	 * @param string $type Only accepts `count`.
	 * @param mixed $query The query to be executed.
	 * @param array $options Optional arguments for the `read()` query that will be executed
	 *        to obtain the calculation result.
	 * @return integer Result of the calculation.
	 */
	public function calculation($type, $query, array $options = array()) {
		switch ($type) {
			case 'count':
				return 0;
		}
	}

	/**
	 * Defines or modifies the default settings of a relationship between two models.
	 *
	 * @param string $class the primary model of the relationship
	 * @param string $type the type of the relationship (hasMany, hasOne, belongsTo)
	 * @param string $name the name of the relationship
	 * @param array $config relationship options
	 * @return array Returns an array containing the configuration for a model relationship.
	 */
	public function relationship($class, $type, $name, array $options = array()) {}

	/**
	 * Create new document.
	 *
	 * @param string $query
	 * @param array $options
	 * @return boolean
	 * @filter
	 */
	public function create($query, array $options = array()) {
		$this->_checkConnection();
		$defaults = array('expiry' => 0);
		$options += $defaults;

		$params = compact('query', 'options');
		$_config = $this->_config;

		return $this->_filter(__METHOD__, $params, function($self, $params) use ($_config) {
			$query   = $params['query'];
			$options = $params['options'];
			extract($query->export($self, array('keys' => array(
				'source', 'model', 'key'
			))));
			$data = $query->data();
			$entity = $query->entity();
			$keys = $model::key($entity);

			$data['_source'] = $source;
			$data[$key] = !empty($keys) ? current($keys)
				: !empty($data[$key]) ? $data[$key]
				: sha1(json_encode($data) + microtime());
			$id = "{$source}:{$data[$key]}";
			$result = $self->connection->add($id, json_encode($data), $options['expiry']);

			if ($result) {
				if ($entity) {
					$entity->sync($data[$key], $data);
				}
				return true;
			}
			return false;
		});
	}

	/**
	 * Returns an array of views for a given source
	 *
	 * @param string $source
	 * @return array
	 */
	public function views($source = '') {
		return ($source) ? $this->_views[$source] : $this->_views;
	}

	/**
	 * Creates a new Couchbase view and re-registers Model finders
	 *
	 * @param string $source
	 * @param string $field
	 */
	public function createView($source, $field = '') {
		$views = json_decode($this->getDesignDoc("{$this->prefix}{$source}"), true);
		if ($field == '') {
			$views['views']['all'] = array(
				'map' =>
				"function (doc, meta) { if(doc._source == '{$source}') { emit(meta.id, doc) }}"
			);
		} else{
			$views['views']["by_{$field}"] = array(
				'map' =>
				"function (doc, meta) { if(doc._source == '{$source}') { emit(doc.{$field}, doc) }}",
			);
		}
		$this->setDesignDoc("{$this->prefix}{$source}", json_encode($views));
		$this->registerViews($source);
	}

	/**
	 * Handle conditions.
	 *
	 * @param string $conditions
	 * @param string $context
	 * @return array
	 */
	public function conditions($conditions, $context) {
		return $conditions;
	}

	/**
	 * Desperately tries to determine what view should be used and/or created
	 *
	 * @param $source
	 * @param $conditions
	 * @param $options
	 * @return array
	 */
	public function autoView($params = array()) {
		extract($params);
		$viewOptionsDefaults = array(
			'debug' => null,
			'descending' => null,
			'endkey' => null,
			'endkey_docid' => null,
			'full_set' => null,
			'group' => null,
			'group_level' => null,
			'inclusive_end' => null,
			'key' => null,
			'keys' => null,
			'limit' => null,
			'on_error' => null,
			'reduce' => null,
			'skip' => null,
			'stale' => false,
			'startkey' => null,
			'startkey_docid' => null
		);
		$viewOptions = array_intersect_key($options, $viewOptionsDefaults);
		$viewOptions = array_filter($viewOptions + $viewOptionsDefaults, function ($v) {
			return !is_null($v);
		});

		/**
		 * If filtering by the key in the 'all' view, prepend the _source name until this lib
		 * can be refactored to not need such a hack
		 */
		if (isset($conditions['view']) && $conditions['view'] == 'all') {
			if (isset($viewOptions['key'])) {
				$viewOptions['key'] = "{$source}:{$viewOptions['key']}";
			}
		}

		$viewName = '';
		$field = '';

		/**
		 * Removes 'all' view from conditions until it can be determined if there are filterable
		 * conditions and the 'all' view is appropriate. All 'all' view becomes irrelevant in
		 * situations like findAllBy$Field, since that it now the actual view that will be
		 * queried.
		 */
		if (isset($conditions['view']) && $conditions['view'] == 'all') {
			unset($conditions['view']);
		}

		/**
		 * If there are no other conditions, then clearly the user is looking for all records
		 * and no filtering. Here's we'll set the view to 'all' and it will be caught below.
		 */
		if (empty($conditions)) {
			$conditions['view'] = 'all';
		}

		/**
		 * Matches Model::find($id);
		 */
		if (!empty($conditions[$key])) {
			/**
			 * Matches Model::find('by_field'), in that lithium believes this to be an id
			 * because the view has not been created or registered as a finder.
			 */
			if (strstr($conditions[$key], 'by_')) {
				$field = str_replace('by_', '', $conditions[$key]);
				$viewName = $conditions[$key];
				unset($conditions[$key]);
			}
		} elseif (empty($conditions['view'])) {
			$field = key($conditions);
			$viewName = "by_{$field}";
			$viewOptions['key'] = array_shift($conditions);
		} else {
			$viewName = $conditions['view'];
		}

		$viewExists = array_key_exists($viewName, $this->views($source));
		if ($_config['createViews'] && $viewName && !$viewExists) {
			$this->createView($source, $field);
		}

		return compact('conditions', 'viewName', 'viewOptions');
	}

	/**
	 * Read from document.
	 *
	 * @param string $query
	 * @param array $options
	 * @return object
	 * @filter
	 */
	public function read($query, array $options = array()) {
		$this->_checkConnection();
		$defaults = array(
			'expiry' => 0,
		);
		$options += $defaults;

		$params = compact('query', 'options');
		$_config = $this->_config;

		return $this->_filter(__METHOD__, $params, function($self, $params) use ($_config) {
			$query   = $params['query'];
			$options = $params['options'];
			extract($query->export($self, array('keys' => array(
				'source', 'model', 'conditions'
			))));

			$key = $model::key();

			$viewName = '';
			$viewOptions = '';

			if ($_config['autoViews']) {
				extract($self->autoView(compact('_config', 'source', 'key', 'conditions', 'options')));
			}

			if ($viewName) {
				$view = $self->connection->view("{$self->prefix}{$source}", $viewName, $viewOptions);
				$records = array();
				if (!empty($view['rows'])) {
					foreach ($view['rows'] as $r) {
						$records[$r['id']] = $r['value'];
					}
				}
				return $self->item($model, $records, array('class' => 'set', 'exists' => true));
			}

			$conditions += array('callback' => null, 'cas' => null);
			$key = "{$source}:{$conditions[$key]}";
			$data = $self->connection->get($key, $conditions['callback'], $conditions['cas']);

			if ($result = json_decode($data, true)) {
				$config = compact('query') + array('exists' => true);
				return $self->item($model, array('data' => $result), $config);
			}
			return false;
		});
	}

	/**
	 * Update document.
	 *
	 * @param string $query
	 * @param array $options
	 * @return boolean
	 * @filter
	 */
	public function update($query, array $options = array()) {
		$this->_checkConnection();
		$defaults = array('expiry' => 0);
		$options += $defaults;
		$params = compact('query', 'options');
		$_config = $this->_config;

		return $this->_filter(__METHOD__, $params, function($self, $params) use ($_config) {
			$query   = $params['query'];
			$options = $params['options'];
			extract($query->export($self, array('keys' => array(
				'source', 'model', 'key'
			))));
			$data = $query->data();
			$entity = $query->entity();
			$id = "{$source}:{$data[$key]}";
			$data['_source'] = $source;
			$result = $self->connection->set($id, json_encode($data), $options['expiry']);

			if ($result) {
				if ($entity) {
					$entity->sync($data[$key], $data);
					return $entity;
				}
				return true;
			}
			return false;
		});
	}

	/**
	 * Delete document.
	 *
	 * @param string $query
	 * @param array $options
	 * @return boolean
	 * @filter
	 */
	public function delete($query, array $options = array()) {
		$params = compact('query', 'options');
		$_config = $this->_config;

		return $this->_filter(__METHOD__, $params, function($self, $params) use ($_config) {
			$query   = $params['query'];
			$options = $params['options'];
			extract($query->export($self, array('keys' => array(
				'source', 'model'
			))));
			$key = $model::key();
			$data = $query->data();
			$entity = $query->entity();

			if (empty($data[$key])) {
				return null;
			}
			$id = "{$source}:{$data[$key]}";

			if ($result = $self->connection->delete($id)) {
				if ($entity) {
					$entity->sync(null, array(), array('dematerialize' => true));
					return $entity;
				}
				return true;
			}
			return false;
		});
	}

	/**
	 * A method dispatcher that allows direct calls to native methods in PHP's `Couchbase` object.
	 *
	 * For example (assuming this instance is stored in `Connections` as `'couchbase'`):
	 * {{{// Update the expiry time of an item
	 * Connections::get('couchbase')->touch('key', strtotime('+1 week')); // returns null
	 * }}}
	 * @see See li3_couchbase\tests\mocks\MockCouchbase
	 * @param string $method The name of native method to call. See the link above for available
	 *        class methods.
	 * @param array $params A list of parameters to be passed to the native method.
	 * @return mixed The return value of the native method specified in `$method`.
	 */
	public function __call($method, $params) {
		if ((!$this->connection) && !$this->connect()) {
			return null;
		}
		if (method_exists($this->connection, $method)) {
			return call_user_func_array(array(&$this->connection, $method), $params);
		}
		if ((!$this->service)) {
			return null;
		}
		return call_user_func_array(array(&$this->service, $method), $params);
	}

	/**
	 * Check whether we are connected to the database and throw exception if not.
	 *
	 * @return void
	 */
	protected function _checkConnection() {
		if (!$this->_isConnected && !$this->connect()) {
			throw new NetworkException("Could not connect to the database.");
		}
	}
}

?>