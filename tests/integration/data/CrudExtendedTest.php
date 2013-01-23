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
use li3_couchbase\tests\mocks\models\Custom;

class CrudExtendedTest extends \lithium\test\Integration {

	protected $db = null;

	protected $_database = null;

	protected $_key = null;

	public $data = array(
		array('name' => 'Marine Store', 'active' => true),
		array('name' => 'Bait Shop', 'active' => false)
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
		$company = Companies::create($this->data[0]);
		$this->assertTrue($company->save());
		$this->assertEqual("Marine Store", $company->name);
		$this->assertEqual('companies', $company->_source);
		$this->assertTrue($company->delete());
	}

	public function testCreateNoId() {
		$company = Companies::create($this->data[0]);
		$this->assertTrue($company->save());
		$this->assertTrue(40 == strlen($company->id));
		$this->assertTrue($company->delete());
	}

	public function testCreateWithId() {
		$company = Companies::create(array('id' => 'my_id'));
		$this->assertTrue($company->save());
		$this->assertEqual('my_id', $company->id);
		$this->assertTrue($company->delete());
	}

	public function testCreateNoIdCollisionless() {
		$company = Companies::create($this->data[0]);
		$this->assertTrue($company->save());

		$second = Companies::create($this->data[0]);
		$this->assertTrue($second->save());
		$this->assertNotEqual($company->id, $second->id);
		$this->assertTrue($company->delete());
		$this->assertTrue($second->delete());
	}

	public function testCreateNoIdCustomKey() {
		$custom = Custom::create();
		$this->assertTrue($custom->save());
		$this->assertTrue(!empty($custom->my_key));
		$this->assertTrue($custom->delete());
	}

	public function testCreateIdCustomKey() {
		$custom = Custom::create(array('my_key' => 'something'));
		$this->assertTrue($custom->save());
		$this->assertEqual('something', $custom->my_key);
		$this->assertTrue($custom->delete());
	}
}