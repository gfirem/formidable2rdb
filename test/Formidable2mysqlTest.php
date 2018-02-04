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
			0 => new Formidable2mysqlColumn( "string", "varchar", 45, 0, "NO" ),
			1 => new Formidable2mysqlColumn( "string_2", "varchar", 45 ),
			2 => new Formidable2mysqlColumn( "integer_col", "integer", 11, 0, "NO" ),
			3 => new Formidable2mysqlColumn( "float_col", "float", 11, 2, "NO" )
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
		$this->assertTrue( count( $columns ) == 7 );
	}
	
	/**
	 * @expectException Exception
	 */
	public function testSqlChangeColumns() {
		$mysql = new Formidable2mysql( $this->conf );
		$sql   = $mysql->change_column( "test", array(
			"string_2" => new Formidable2mysqlColumn( "string_55", "BLOB", 0, 0, "NO" ),
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
			1 => new Formidable2mysqlColumn( "column_1", "varchar", 45 ),
			2 => new Formidable2mysqlColumn( "column_2", "varchar", 45 ),
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
			1 => new Formidable2mysqlColumn( "column_1", "varchar", 45 ),
			2 => new Formidable2mysqlColumn( "column_2", "varchar", 45 ),
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
					1 => new Formidable2mysqlColumn( "column_1", "varchar", 45 ),
					2 => new Formidable2mysqlColumn( "column_2", "varchar", 45 )
				),
				"drop"   => array(
					1 => new Formidable2mysqlColumn( "column_1", "varchar", 45 ),
				),
				"change" => array(
					"column_2" => new Formidable2mysqlColumn( "column_22", "varchar", 45 )
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
					$add[] = new Formidable2mysqlColumn( $item["Field"], $item["Type"], 0, 0, $item["Null"], $item["Key"], $item["Default"], $item["Extra"], false, 0, true );
				}
			}
			if ( ! empty( $diff["removed"] ) ) {
				foreach ( $diff["removed"] as $key => $item ) {
					$remove[] = new Formidable2mysqlColumn( $item["Field"], $item["Type"], 0, 0, $item["Null"], $item["Key"], $item["Default"], $item["Extra"], false, 0, true );
				}
			}
			if ( ! empty( $diff["edited"] ) ) {
				foreach ( $diff["edited"] as $key => $item ) {
					$source                     = (array) $column[ $key ];
					$target                     = (array) $changes[ $key ];
					$change[ $source["Field"] ] = new Formidable2mysqlColumn( $target["Field"], $target["Type"], 0, 0, $target["Null"], $target["Key"], $target["Default"], $target["Extra"], false, 0, true );
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
	
	public function testInsert() {
		$this->testSqlCreateTable();
		$mysql = new Formidable2mysql( $this->conf );
		for ( $i = 0; $i <= 10; $i ++ ) {
			$mysql->insert( "test", array(
				"created_at"  => date( "Y-m-d H:i:s" ),
				"entry_id"    => $i,
				"string"      => "sdddfsdf",
				"string_2"    => "sdddfsdf",
				"integer_col" => 122,
				"float_col"   => 12.2
			) );
		}
	}
	
	public function testUpdate() {
		$mysql = new Formidable2mysql( $this->conf );
		for ( $i = 0; $i <= 10; $i ++ ) {
			$mysql->update( "test", array(
				"string"      => "sssssssssssssssss",
				"integer_col" => 111111111,
			
			), $i );
		}
	}
	
	public function testDelete() {
		$mysql = new Formidable2mysql( $this->conf );
		for ( $i = 0; $i <= 10; $i ++ ) {
			$mysql->delete( "test", $i );
		}
		
		$this->testSqlRenameTable();
		$this->testSqlDropTable();
	}
	
	
}
