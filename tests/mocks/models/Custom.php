<?php

namespace li3_couchbase\tests\mocks\models;

class Custom extends \lithium\data\Model {

	protected $_meta = array(
		'connection' => 'test-couchbase',
		'key' => 'my_key'
	);

	protected $_schema = array(
		'my_key' => array('type' => 'id')
	);
}
