<?php
use lithium\data\Connections;

Connections::add('test-couchbase', array(
	'type' => 'Couchbase',
	'login' => '',
	'password' => '',
	'database' => 'default'
));