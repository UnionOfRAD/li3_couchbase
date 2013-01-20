<?php
/**
 * li3_couchbase: A Couchbase data source for Lithium.
 *
 * @copyright     Copyright 2013, Union Of RAD
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_couchbase\tests\integration\data;

use lithium\data\Connections;
use li3_couchbase\extensions\data\source\Couchbase;
use li3_couchbase\tests\mocks\models\Companies;
use li3_couchbase\tests\mocks\models\CustomKeys;

class CrudExtendedTest extends \lithium\test\Integration {

	protected $db = null;

	protected $_database = null;

	protected $_key = null;

	public $data = array(
		array('name' => 'StuffMart', 'active' => true),
		array('name' => 'Ma \'n Pa\'s Data Warehousing & Bait Shop', 'active' => false)
	);

	/**
	 * Skip the test if a Couchbase adapter configuration is unavailable.
	 */
	public function skip() {
		$connection = "test-couchbase";
		$this->skipIf(!Couchbase::enabled(), 'The Couchbase extension is not loaded!');

		$this->config = Connections::get($connection, array('config' => true));

		$hasDb = (isset($this->config['type']) && $this->config['type'] == 'Couchbase');
		$message = 'Test database is either unavailable, or not a Couchbase connection!';
		$this->skipIf(!$hasDb, $message);

		$isAvailable = Connections::get($connection)->isConnected(array('autoConnect' => true));
		$this->skipIf(!$isAvailable, "No {$connection} connection available.");

		$this->db = Connections::get($connection);
		$this->_database = $this->config['database'];

		$this->_key = Companies::key();
	}

	/**
	 * Creating the test database
	 */
	public function setUp() {
		//$this->db->connection->put($this->_database);
	}

	/**
	 * Dropping the test database
	 */
	public function tearDown() {
		//$this->db->connection->delete($this->_database);
	}


	public function testCreateType() {
		$company = Companies::create(array(
			'gimme' => 'type'
		));
		$this->assertTrue($company->save());
		$this->assertEqual('companies', $company->_source);
		$this->assertTrue($company->delete());
	}

	public function testCreateNoId() {
		$company = Companies::create(array(
			'who' => 'am i?'
		));

		$this->assertTrue($company->save());
		$this->assertTrue(!empty($company->id));
		$this->assertTrue($company->delete());
	}

	public function testCreateNoIdCustomKey() {
		$custom = CustomKeys::create(array(
			'how' => 'am I not myself?'
		));
		$this->assertTrue($custom->save());
		$this->assertTrue(!empty($custom->my_identifier));
		$this->assertTrue($custom->delete());
	}

	public function testCreateId() {
		$company = Companies::create(array(
			'id' => 'supercool'
		));

		$this->assertTrue($company->save());
		$this->assertEqual('supercool', $company->id);
		$this->assertTrue($company->delete());
	}

	public function testCreateIdCustomKey() {
		$custom = CustomKeys::create(array(
			'my_identifier' => 'my_key'
		));
		$this->assertTrue($custom->save());
		$this->assertEqual('my_key', $custom->my_identifier);
		$this->assertTrue($custom->delete());
	}

	public function testCreateNoIdCollisionless() {
		$company = Companies::create(array(
			'name' => 'Acme'
		));
		$this->assertTrue($company->save());

		$second = Companies::create(array(
			'name' => 'Acme'
		));
		$this->assertTrue($second->save());

		$this->assertNotEqual($company->id, $second->id);
		$this->assertTrue($company->delete());
		$this->assertTrue($second->delete());
	}
}