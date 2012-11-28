<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_couchbase\extensions\data\source\couchbase;

class Schema extends \lithium\data\DocumentSchema {

	protected $_handlers = array();

	protected $_types = array(
		'datetime'     => 'date',
		'timestamp'    => 'date',
		'int'          => 'integer'
	);

	public function __construct(array $config = array()) {
		$defaults = array('fields' => array('id' => array('type' => 'id')));
		parent::__construct(array_filter($config) + $defaults);
	}

	protected function _init() {
		$this->_autoConfig[] = 'handlers';
		parent::_init();

		$this->_handlers += array(
			'integer' => function($v) { return (integer) $v; },
			'float'   => function($v) { return (float) $v; },
			'boolean' => function($v) { return (boolean) $v; }
		);
	}
}

?>