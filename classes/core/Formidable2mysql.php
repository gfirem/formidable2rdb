<?php
require_once 'Formidable2RdbBase.php';
require_once 'Formidable2mysqlColumn.php';
require_once "TreeWalker.php";


class Formidable2mysql extends Formidable2RdbBase {
	
	public function __construct( $cong_array ) {
		parent::__construct( $cong_array );
		if ( ! $this->is_test() ) {
			add_filter( "formidable2rdb_map_field", array( $this, "map_field" ), 10, 1 );
		}
	}
	
	/**
	 * Add custom types to mysql
	 *
	 * @param $map
	 *
	 * @return mixed
	 */
	public function map_field( $map ) {
		
		return $map;
	}
	
	public function create_table( $table_name, $columns ) {
		if ( ! empty( $table_name ) ) {
			$sql = $this->build_sql( "create", array(
				"table_name" => $table_name,
				"columns"    => $columns,
			) );
			
			$result = $this->execute( $sql );
			$result->closeCursor();
			
			$error = $result->errorInfo();
			if ( $error[0] == 0 ) {
				if ( ! $this->is_test() ) {
					do_action( "formidable2rdb_add_table", $table_name, $columns );
				}
			} else {
				throw new Formidable2RdbException( $error[2], array( Formidable2RdbManager::t( "Error creating the table." ) ) );
			}
			
			return $result;
		} else {
			throw new Exception( "The table_name param is empty." );
		}
	}
	
	public function drop_table( $table_name ) {
		if ( ! empty( $table_name ) ) {
			$sql = $this->build_sql( "drop", array(
				"table_name" => $table_name
			) );
			
			$result = $this->execute( $sql );
			$result->closeCursor();
			
			$error = $result->errorInfo();
			if ( $error[0] == 0 ) {
				if ( ! $this->is_test() ) {
					do_action( "formidable2rdb_drop_table", $table_name );
				}
			} else {
				throw new Formidable2RdbException( $error[2], array( Formidable2RdbManager::t( "Error when drop the table." ) ) );
			}
			
			return $result;
		} else {
			throw new Exception( "The table_name param is empty." );
		}
	}
	
	public function rename_table( $table_name, $new_table_name ) {
		if ( ! empty( $table_name ) && ! empty( $new_table_name ) ) {
			$sql = $this->build_sql( "rename", array(
				"table_name"     => $table_name,
				"new_table_name" => $new_table_name,
			) );
			
			$result = $this->execute( $sql );
			$result->closeCursor();
			
			$error = $result->errorInfo();
			if ( $error[0] != 0 ) {
				throw new Formidable2RdbException( $error[2], array( Formidable2RdbManager::t( "Error renaming the table." ) ) );
			}
			
			return $result;
		} else {
			throw new Exception( "The table_name or new_table_name param is empty." );
		}
	}
	
	public function add_column( $table_name, $columns ) {
		if ( ! empty( $table_name ) ) {
			$sql = $this->build_sql( "add_column", array(
				"table_name" => $table_name,
				"columns"    => $columns,
			) );
			
			$result = $this->execute( $sql );
			$result->closeCursor();
			
			$error = $result->errorInfo();
			if ( $error[0] != 0 ) {
				throw new Formidable2RdbException( $error[2], array( Formidable2RdbManager::t( "Error adding columns to the table." ) ) );
			}
			
			return $result;
		} else {
			throw new Exception( "The table_name param is empty." );
		}
	}
	
	public function change_column( $table_name, $columns ) {
		if ( ! empty( $table_name ) ) {
			$sql = $this->build_sql( "change_column", array(
				"table_name" => $table_name,
				"columns"    => $columns,
			) );
			
			$result = $this->execute( $sql );
			$result->closeCursor();
			
			$error = $result->errorInfo();
			if ( $error[0] != 0 ) {
				throw new Formidable2RdbException( $error[2], array( Formidable2RdbManager::t( "Error when alter columns." ) ) );
			}
			
			return $result;
		} else {
			throw new Exception( "The table_name param is empty." );
		}
	}
	
	public function get_columns( $table_name ) {
		if ( ! empty( $table_name ) ) {
			$tran = $this->execute( "DESCRIBE `" . $this->escape( $table_name )."`" );
			
			$result = $tran->fetchAll( PDO::FETCH_OBJ );
			$tran->closeCursor();
			$columns = array();
			foreach ( $result as $key => $item ) {
				$columns[] = new Formidable2mysqlColumn( $item->Field, $item->Type, $item->Null, $item->Key, $item->Default, $item->Extra );
			}
			
			return $result;
		} else {
			throw new Exception( "The table_name param is empty." );
		}
	}
	
	public function drop_columns( $table_name, $columns ) {
		if ( ! empty( $table_name ) ) {
			$sql    = $this->build_sql( "drop_column", array(
				"table_name" => $table_name,
				"columns"    => $columns,
			) );
			$result = $this->execute( $sql );
			$result->closeCursor();
			
			$error = $result->errorInfo();
			if ( $error[0] != 0 ) {
				throw new Formidable2RdbException( $error[2], array( Formidable2RdbManager::t( "Error when drop column(s)." ) ) );
			}
			
			return $result;
		} else {
			throw new Exception( "The table_name param is empty." );
		}
	}
	
	/**
	 * This function is to alter table column, like change column, rename, drop, add
	 *
	 * @param $table_name String Table name
	 * @param $args array Actions to perform into the table. The syntax will be array("add" => array(), "change" => array(), "drop" => array() )
	 *
	 * @throws Exception
	 */
	public function alter_table( $table_name, $args ) {
		if ( ! empty( $table_name ) ) {
			if ( ! empty( $args["add"] ) ) {
				$trans = $this->add_column( $table_name, $args["add"] );
				$error = $trans->errorInfo();
			}
			if ( ! empty( $args["change"] ) ) {
				$trans = $this->change_column( $table_name, $args["change"] );
			}
			if ( ! empty( $args["drop"] ) ) {
				$trans = $this->drop_columns( $table_name, $args["drop"] );
				$error = $trans->errorInfo();
			}
		} else {
			throw new Exception( "The table_name param is empty." );
		}
	}
	
	public function exist_table( $table_name ) {
		if ( ! empty( $table_name ) ) {
			try {
				$result = $this->execute( "SELECT 1 FROM `" . $this->escape( $table_name ) . "` LIMIT 1" );
				$result->closeCursor();
			} catch ( Exception $e ) {
				return false;
			}
			
			return $result !== false;
		} else {
			throw new Exception( "The table_name param is empty." );
		}
	}
	
	public function exist_column( $table_name, $column_name ) {
		if ( ! empty( $table_name ) ) {
			
			try {
				if ( ! empty( $table_name ) ) {
					if ( ! empty( $column_name ) ) {
						$result = $this->execute( 'show columns from `' . $this->escape( $table_name ) . '` where Field="' . $this->escape( $column_name ) . '"' );
						$result->closeCursor();
					} else {
						throw new Exception( "The column_name param is empty." );
					}
				} else {
					throw new Exception( "The table_name param is empty." );
				}
			} catch ( Exception $e ) {
				return false;
			}
			
			return $result !== false;
		} else {
			throw new Exception( "The table_name param is empty." );
		}
	}
}