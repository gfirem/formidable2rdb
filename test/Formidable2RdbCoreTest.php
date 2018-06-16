<?php
require_once '../classes/Formidable2RdbCore.php';
require_once 'Formidable2RdbTestBase.php';

class Formidable2RdbCoreTest extends Formidable2RdbTestBase {
	
	public $conf = array(
		"driver" => "mysql",
		"dbname" => "wordpress",
		"user"   => "wordpress",
		"pass"   => "wordpress",
		"debug"   => true,
	);
	
	/**
	 * @cover Formidable2RdbCore::get_instance
	 * @expectException Exception
	 */
	public function testInstance() {
		$db   = Formidable2RdbCore::get_instance( $this->conf );

		$this->assertNotEmpty( $db, "Not return instance" );
		$this->assertNotEmpty( $db->getMbd() );
		$this->assertNotEmpty( $db->getHandler() );
		$this->assertInstanceOf( "PDO", $db->getMbd() );
		$this->assertInstanceOf( "Formidable2" . $this->conf["driver"], $db->getHandler() );
	}
	
}
