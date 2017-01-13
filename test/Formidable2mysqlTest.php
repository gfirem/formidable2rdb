<?php
require_once '../classes/core/Formidable2mysql.php';
require_once '../classes/core/Formidable2mysqlColumn.php';
require_once '../classes/core/TreeWalker.php';
require_once 'Formidable2RdbTestBase.php';
require_once "../classes/Formidable2RdbException.php";

class Formidable2mysqlTest extends Formidable2RdbTestBase {
	
	public $conf = array(
		"driver" => "mysql",
		"dbname" => "wp_autocomplete",
		"user"   => "root",
		"pass"   => "",
		"debug"  => true,
	);
	
	/**
	 * @expectException Exception
	 */
	public function testInstance() {
		$mysql = new Formidable2mysql( $this->conf );
		$this->assertNotEmpty( $mysql, "Not return instance" );
	}
	
	/**
	 * @expectException Exception
	 */
	public function testSqlCreateTable() {
		$mysql = new Formidable2mysql( $this->conf );
		$sql   = $mysql->create_table( "test", array(
			0 => new Formidable2mysqlColumn( "string", "varchar(45)", "NO" ),
			1 => new Formidable2mysqlColumn( "string_2", "varchar(45)" ),
			2 => new Formidable2mysqlColumn( "integer_col", "integer", "NO" ),
			3 => new Formidable2mysqlColumn( "float_col", "float", "NO" )
		) );
		$error = $sql->errorInfo();
		$this->assertTrue( $error[0] == 0, $error[2] );
	}
	
	
	/**
	 * @expectException Exception
	 */
	public function testSqlGetColumns() {
		$mysql   = new Formidable2mysql( $this->conf );
		$columns = $mysql->get_columns( "test" );
		$this->assertTrue( count( $columns ) == 6 );
	}
	
	/**
	 * @expectException Exception
	 */
	public function testSqlChangeColumns() {
		$mysql = new Formidable2mysql( $this->conf );
		$sql   = $mysql->change_column( "test", array(
			"string_2" => new Formidable2mysqlColumn( "string_55", "BLOB", "NO" ),
		) );
		$error = $sql->errorInfo();
		$this->assertTrue( $error[0] == 0, $error[2] );
	}
	
	/**
	 * @expectException Exception
	 */
	public function testSqlRenameTable() {
		$mysql = new Formidable2mysql( $this->conf );
		$sql   = $mysql->rename_table( "test", "test12" );
		$error = $sql->errorInfo();
		$this->assertTrue( $error[0] == 0, $error[2] );
	}
	
	/**
	 * @expectException Exception
	 */
	public function testSqlDropTableFake() {
		$mysql = new Formidable2mysql( $this->conf );
		$sql   = $mysql->drop_table( "asfsdfsdfsfwerwrw" );
		$error = $sql->errorInfo();
		$this->assertTrue( $error[0] != 0 );
	}
	
	
	/**
	 * @expectException Exception
	 */
	public function testSqlAddColumnTable() {
		$mysql = new Formidable2mysql( $this->conf );
		$sql   = $mysql->add_column( "test12", array(
			1 => new Formidable2mysqlColumn( "column_1", "varchar(45)" ),
			2 => new Formidable2mysqlColumn( "column_2", "varchar(45)" ),
		) );
		$error = $sql->errorInfo();
		$this->assertTrue( $error[0] == 0, $error[2] );
	}
	
	/**
	 * @expectException Exception
	 */
	public function testSqlDropColumnTable() {
		$mysql = new Formidable2mysql( $this->conf );
		$sql   = $mysql->drop_columns( "test12", array(
			1 => new Formidable2mysqlColumn( "column_1", "varchar(45)" ),
			2 => new Formidable2mysqlColumn( "column_2", "varchar(45)" ),
		) );
		$error = $sql->errorInfo();
		$this->assertTrue( $error[0] == 0, $error[2] );
	}
	
	/**
	 * @expectException Exception
	 */
	public function testSqlDropTable() {
		$mysql = new Formidable2mysql( $this->conf );
		$sql   = $mysql->drop_table( "test12" );
		$error = $sql->errorInfo();
		$this->assertTrue( $error[0] == 0, $error[2] );
	}
	
	/**
	 * @expectException Exception
	 */
	public function testAlterTable() {
		$this->testSqlCreateTable();
		$mysql = new Formidable2mysql( $this->conf );
		$mysql->alter_table( "test", array(
				"add"    => array(
					1 => new Formidable2mysqlColumn( "column_1", "varchar(45)" ),
					2 => new Formidable2mysqlColumn( "column_2", "varchar(45)" )
				),
				"drop"   => array(
					1 => new Formidable2mysqlColumn( "column_1", "varchar(45)" ),
				),
				"change" => array(
					"column_2" => new Formidable2mysqlColumn( "column_22", "varchar(45)" )
				),
			)
		);
		$this->testSqlRenameTable();
		$this->testSqlDropTable();
	}
	
	/**
	 * @expectException Exception
	 */
	public function testTreeWalkerAlterTable() {
		$tree_walker = new TreeWalker( array(
				"debug"      => false,
				"returntype" => "array"
			)
		);
		
		$this->testSqlCreateTable();
		$mysql   = new Formidable2mysql( $this->conf );
		$column  = $mysql->get_columns( "test" );
		$changes = $mysql->get_columns( "test" );
		//edit
		$changes[5]->Field = "newtypeinteger";
		$changes[5]->Type  = "int(11)";
		//remove
		unset( $changes[3] );
		//add
		$changes[6]        = clone $changes[5];
		$changes[6]->Field = "newfield";
		$changes[6]->Type  = "varchar(45)";
		
		$diff = $tree_walker->getdiff( $changes, $column, true );
		if ( ! empty( $diff ) ) {
			$add    = array();
			$remove = array();
			$change = array();
			if ( ! empty( $diff["new"] ) ) {
				foreach ( $diff["new"] as $key => $item ) {
					$add[] = new Formidable2mysqlColumn( $item["Field"], $item["Type"], $item["Null"] );
				}
			}
			if ( ! empty( $diff["removed"] ) ) {
				foreach ( $diff["removed"] as $key => $item ) {
					$remove[] = new Formidable2mysqlColumn( $item["Field"], $item["Type"], $item["Null"] );
				}
			}
			if ( ! empty( $diff["edited"] ) ) {
				foreach ( $diff["edited"] as $key => $item ) {
					$source                     = (array) $column[ $key ];
					$origin                     = (array) $changes[ $key ];
					$change[ $source["Field"] ] = new Formidable2mysqlColumn( $origin["Field"], $origin["Type"], $origin["Null"] );
				}
			}
			
			$mysql->alter_table( "test", array(
					"add"    => $add,
					"drop"   => $remove,
					"change" => $change,
				)
			);
		}
		$this->testSqlRenameTable();
		$this->testSqlDropTable();
		
	}
	
	/**
	 * @expectException Exception
	 */
	public function testNotExistTable() {
		$mysql  = new Formidable2mysql( $this->conf );
		$result = $mysql->exist_table( "test12" );
		$this->assertTrue( $result == false );
	}
	
	/**
	 * @expectException Exception
	 */
	public function testExistTable() {
		$this->testSqlCreateTable();
		$mysql  = new Formidable2mysql( $this->conf );
		$result = $mysql->exist_table( "test" );
		$this->assertTrue( $result == true );
		$this->testSqlRenameTable();
		$this->testSqlDropTable();
	}
	
	/**
	 * @expectException Exception
	 */
	public function testExistColumn() {
		$this->testSqlCreateTable();
		$mysql  = new Formidable2mysql( $this->conf );
		$result = $mysql->exist_column( "test", "string_2" );
		$this->assertTrue( $result !== false );
		$this->testSqlRenameTable();
		$this->testSqlDropTable();
	}
	
	/**
	 * @expectException Exception
	 */
	public function testNotExistColumn() {
		$mysql  = new Formidable2mysql( $this->conf );
		$result = $mysql->exist_column( "test", "string_2" );
		$this->assertTrue( $result === false );
	}
	
	public function testQuestion1() {
		$a = array( "1" => "1", "2" => "2", "3" => "3", );
		$b = array( "1" => "1", "2" => "2", "3" => "3", );
		$c = array_diff_key( $a, $b );
		$this->assertEmpty($c);
	}
	
	public function testQuestion2() {
		$a = array( "1" => "1", "a" => "2", "3" => "3", );
		$b = array( "1" => "1", "2" => "2", "3" => "3", );
		$c = array_diff_key( $a, $b );
		$this->assertEmpty($c);
	}
	
	public function testQuestion3() {
		$a = array( "1" => "1", "a" => "2", "3" => "3", );
		$b = array( "1" => "1", "2" => "2", "3" => "3", );
		$c = array_diff_key( $a, $b );
		$this->assertEmpty($c);
	}
}
