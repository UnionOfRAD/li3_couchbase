<?php
/**
 * li3_couchbase: A Couchbase datasource for Lithium.
 *
 * @copyright     Copyright 2012, Michael Nitschinger
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_couchbase\tests\cases\data\source;

use li3_couchbase\data\source\Couchbase;

/**
 *
 */
class CouchbaseTest extends \lithium\test\Unit {

	public function testEnabled() {
		$this->assertTrue(Couchbase::enabled());
		$this->assertFalse(Couchbase::enabled('transactions'));
	}

	public function testConnect() {

	}

}

?>