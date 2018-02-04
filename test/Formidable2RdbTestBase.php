<?php

class Formidable2RdbTestBase extends PHPUnit_Framework_TestCase {
	public function __construct( $name = null, array $data = [], $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );
		define("F2RDB_TEST", true);
	}
	
	
}