<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_couchbase\tests\integration\data;

use li3_couchbase\extensions\data\source\Couchbase;
use li3_couchbase\tests\mocks\models\Companies;
use lithium\data\Connections;

class CrudTest extends \lithium\test\Integration {

	protected $db = null;

	protected $_database = null;

	protected $_key = null;

	public $companyData = array(
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

	/**
	 * Tests that a single record with a manually specified primary key can be created, persisted
	 * to an arbitrary data store, re-read and updated.
	 *
	 * @return void
	 */
	public function testCreate() {
		$new = Companies::create(array('name' => 'Acme, Inc.', 'active' => true));
		$expected = array('name' => 'Acme, Inc.', 'active' => true);
		$result = $new->data();
		$this->assertEqual($expected, $result);

		$this->assertEqual(
			array(false, true, true),
			array($new->exists(), $new->save(), $new->exists())
		);
		$this->id = $new->id;
	}

	public function testRead() {
		$existing = Companies::find($this->id);

		foreach (Companies::key($existing) as $val) {
			$this->assertTrue($val);
		}
		$this->assertEqual('Acme, Inc.', $existing->name);
		$this->assertTrue($existing->active);
		$this->assertTrue($existing->exists());
	}

	public function testUpdate() {
		$existing = Companies::find($this->id);
		$this->assertEqual($existing->name, 'Acme, Inc.');
		$existing->name = 'Big Brother and the Holding Company';
		$result = $existing->save();
		$this->assertTrue($result->id);

		$existing = Companies::find($this->id);

		foreach (Companies::key($existing) as $val) {
			$this->assertTrue($val);
		}
		$this->assertTrue($existing->active);
		$this->assertEqual('Big Brother and the Holding Company', $existing->name);
	}

	public function testDelete() {
		$existing = Companies::find($this->id);
		$this->assertTrue($existing->exists());
		$this->assertTrue($existing->delete() == true);
	}

	public function testUpdateWithNewProperties() {
		$new = Companies::create(array('name' => 'Acme, Inc.', 'active' => true));

		$expected = array('name' => 'Acme, Inc.', 'active' => true);
		$result = $new->data();
		$this->assertEqual($expected, $result);

		$new->foo = 'bar';
		$expected = array('name' => 'Acme, Inc.', 'active' => true, 'foo' => 'bar');
		$result = $new->data();
		$this->assertEqual($expected, $result);

		$this->assertTrue($new->save());

		$updated = Companies::find($new->id);
		$expected = 'bar';
		$result = $updated->foo;
		$this->assertEqual($expected, $result);
		$this->assertTrue($updated->delete() == true);
	}


	public function testSomeThingsDoNotWork() {
		$this->assertEqual(0, Companies::count());
		$this->assertNull(Companies::first());
		$this->assertNull(Companies::all());
	}
}

?>