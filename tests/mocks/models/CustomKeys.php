<?php

namespace li3_couchbase\tests\mocks\models;

class CustomKeys extends \lithium\data\Model {

	protected $_meta = array(
		'connection' => 'test-couchbase',
		'key' => 'my_identifier'
	);

	protected $_schema = array(
		'my_identifier' => array('type' => 'id')
	);
}
