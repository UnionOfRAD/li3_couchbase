<?php
/**
 * li3_couchbase: A Couchbase data source for Lithium.
 *
 * @copyright     Copyright 2013, Union Of RAD
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_couchbase\tests\integration\data;

use lithium\data\Connections;
use lithium\util\Set;
use li3_couchbase\extensions\data\source\Couchbase;
use li3_couchbase\tests\mocks\models\Companies;
use li3_couchbase\tests\mocks\models\Custom;

class CrudExtendedTest extends \lithium\test\Integration {

	protected $db = null;

	protected $_database = null;

	protected $_key = null;

	public $data = array(
		array('name' => 'Marine Store', 'active' => true, 'founded' => 2012),
		array('name' => 'Bait Shop', 'active' => false, 'founded' => 2013),
		array('name' => 'Tackle Shack', 'active' => true, 'founded' => 2013)
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
		$this->config = $this->db->_config;
		$this->_database = $this->config['database'];

		$this->_key = Companies::key();
	}

	/**
	 * Creating the test database
	 */
	public function setUp() {
		static $flushed = false;
		if (!$flushed) {
			$this->cluster = new \CouchbaseClusterManager($this->config['host'],
				$this->config['login'],
				$this->config['password']
			);
			if ($this->config['database'] !== 'li3-test') {
				throw new \Exception('Create a new bucket `li3-test` and edit `database` li3_couchbase/config/bootstrap.php');
			}
			if (!in_array('li3-test', $this->_getBuckets())) {
				throw new \Exception('Create a new bucket `li3-test` and edit `database` li3_couchbase/config/bootstrap.php');
			}
			if ($this->config['database'] == 'default') {
				throw new \Exception('Refusing to flush default bucket. Create a different one.');
			}
			$this->db->flush();
			/**
			 * @todo Work out errors in automatic bucket creation/deletion
			 */
			/**
			$buckets = $this->_getBuckets();
			if (in_array('li3-test', $buckets)) {
				$this->cluster->deleteBucket('li3-test');
			}
			$this->cluster->createBucket('li3-test',
				array(
					"type" => "couchbase",
					"quota" => 100,
					"replicas" => 0,
					"enable_flush" => 1,
					"parallel_compaction" => true,
					"auth" => "sasl",
					//"password" => "foobar"
				)
			);
			**/
			$flushed = true;
		}
	}

	/**
	 * Dropping the test database
	 */
	public function tearDown() {
		//$this->db->connection->delete($this->_database);
	}

	/**
	 * Returns an array of bucket names retrieved from cluster
	 *
	 * @return array
	 */
	protected function _getBuckets() {
		$info = json_decode($this->cluster->getInfo());
		$bucketUrl = "http://{$info->nodes[0]->hostname}{$info->buckets->uri}";
		$bucketData = json_decode(file_get_contents($bucketUrl), true);
		$buckets = Set::extract($bucketData, '/name');
		return $buckets;
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

	public function testFindAll() {
		$company = Companies::create($this->data[0]);
		$this->assertTrue($company->save());
		$companies = Companies::find('all');
		$data = $companies->data();
		$this->assertEqual(1, count($data));
		$record = array_shift($data);
		$this->assertEqual('Marine Store', $record['name']);
		$this->assertEqual('1', $record['active']);
		$this->assertEqual('companies', $record['_source']);
		$this->assertTrue($company->delete());
	}

	public function testFindById() {
		$company1 = Companies::create($this->data[0]);
		$company1->save();
		$company = Companies::find($company1->id);
		$data = $company->data();
		$this->assertEqual('Marine Store', $data['name']);
		$company->delete();
	}

	public function testFindByStaticFinder() {
		$company1 = Companies::create($this->data[0]);
		$company1->save();
		$company2 = Companies::create($this->data[1]);
		$company2->save();
		$company = Companies::by_active();
		$this->assertEqual(2, count($company));
		$company1->delete();
		$company2->delete();
	}

	public function testFindByNativeParameters() {
		$company1 = Companies::create($this->data[0]);
		$company1->save();
		$company2 = Companies::create($this->data[1]);
		$company2->save();
		$company3 = Companies::create($this->data[2]);
		$company3->save();

		// when using findAll and filtering by key, special key prepending happens internally
		$companies = Companies::find('all', array('key' => $company3->id));
		$this->assertEqual(1, count($companies));

		$company1->delete();
		$company2->delete();
		$company3->delete();
	}

	public function testFindByView() {
		$company1 = Companies::create($this->data[0]);
		$company1->save();
		$company2 = Companies::create($this->data[1]);
		$company2->save();

		$companies = Companies::find('by_active');
		$this->assertEqual(2, count($companies->data()));

		$companies = Companies::find('all', array('conditions' => array('view' => 'by_active')));
		$this->assertEqual(2, count($companies->data()));

//		$companies = Companies::find('first', array('conditions' => array('view' => 'by_active')));
//		$this->assertEqual(2, count($companies->data()));

		$company1->delete();
		$company2->delete();
	}

	public function testFindByViewAutocreate() {
		$company1 = Companies::create($this->data[0]);
		$company1->save();
		$company2 = Companies::create($this->data[1]);
		$company2->save();

		$companies = Companies::findAllByName('Marine Store');
		$this->assertEqual(1, count($companies->data()));

		$company = Companies::findByName('Marine Store');
		$this->assertEqual('Marine Store', $company['name']);

		$company1->delete();
		$company2->delete();
	}
}