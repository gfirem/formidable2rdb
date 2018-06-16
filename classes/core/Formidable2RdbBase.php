<?php
require_once 'Formidable2RdbInterface.php';

abstract class Formidable2RdbBase implements Formidable2RdbInterface {

	/**
	 * PDO instance
	 *
	 * @var PDO
	 */
	public $mbd;

	public $debug = false;

	private $db_name;

	/**
	 * Formidable2RdbBase constructor.
	 *
	 * @param $cong_array
	 *
	 * @throws Exception
	 */
	function __construct( $cong_array ) {
		try {
			$this->db_name = $cong_array['dbname'];

			if ( $cong_array['debug'] ) {
				$this->debug = true;
			}

			$this->mbd = new PDO( $cong_array['driver'] . ':host=' . $cong_array['host'] . ';dbname=' . $cong_array['dbname'], $cong_array['user'], $cong_array['pass'],
				array(
					PDO::ATTR_PERSISTENT               => true,
					PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
					PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
					PDO::MYSQL_ATTR_INIT_COMMAND       => "SET NAMES 'utf8'"
				) );

		} catch ( PDOException $e ) {
			throw new Exception( $e->getMessage() );
		}
	}

	/**
	 * @return PDO
	 */
	public function getMbd() {
		return $this->mbd;
	}

	private function get_value_data( $value ) {
		return is_string( $value ) ? sprintf( "'%s'", $value ) : $value;
	}

	private function get_column_key_data( $key ) {
		return sprintf( '`%s`', $key );
	}

	private function get_table_name( $table ) {
		return sprintf( '`%s`.`%s`', $this->db_name, strtolower( $this->escape( $table ) ) );
	}

	public function build_sql( $action, $args ) {
		if ( empty( $action ) ) {
			return '';
		}
		$sql = '';
		switch ( $action ) {
			case 'insert':
				if ( $this->check_requirements( $args, array( 'table_name', 'columns' ) ) ) {
					$columns = array();
					$data    = array();
					foreach ( $args['columns'] as $key => $value ) {
						$columns[] = $this->get_column_key_data( $key );
						$data[]    = $this->get_value_data( $value );
					}
					$column_string = implode( ', ', $columns );
					$data_string   = implode( ', ', $data );
					$sql           = sprintf( 'INSERT INTO %s (%s) VALUES (%s)', $this->get_table_name( $args['table_name'] ), $column_string, $data_string );

				}
				break;
			case 'update':
				if ( $this->check_requirements( $args, array( 'table_name', 'columns', 'entry_id' ) ) ) {
					$sql     = sprintf( 'UPDATE %s SET ', $this->get_table_name( $args['table_name'] ) );
					$columns = array();
					foreach ( $args['columns'] as $key => $value ) {
						$columns[] = sprintf( '%s.%s=%s', $this->get_table_name( $args['table_name'] ), $this->get_column_key_data( $key ), $this->get_value_data( $value ) );
					}
					$column_string = implode( ', ', $columns );
					$sql           .= sprintf( '%s WHERE entry_id=%s', $column_string, $args['entry_id'] );
				}
				break;
			case 'delete':
				if ( $this->check_requirements( $args, array( 'table_name', 'entry_id' ) ) ) {
					$sql = sprintf( 'DELETE FROM %s WHERE entry_id=%s', $this->get_table_name( $args['table_name'] ), $args['entry_id'] );
				}
				break;
			case 'create':
				if ( $this->check_requirements( $args, array( 'table_name', 'columns' ) ) ) {
					$sql = sprintf( 'CREATE TABLE %s (rdb_id INT NOT NULL AUTO_INCREMENT, created_at TIMESTAMP NULL, entry_id INT NULL, ', $this->get_table_name( $args['table_name'] ) );
					/**
					 * @var int $key
					 * @var Formidable2mysqlColumn $def
					 */
					foreach ( $args['columns'] as $key => $def ) {
						$sql .= sprintf( '%s.`%s` %s %s', $this->get_table_name( $args['table_name'] ), $this->escape( $def->Field ), $this->escape( $def->Type ), $this->escape( $def->Null ) );
						if ( ! empty( $def->Default ) ) {
							$sql .= sprintf( ' DEFAULT `%s`', $this->escape( $def->Default ) );
						}
						$sql .= ', ';
					}
					$sql .= 'PRIMARY KEY (rdb_id), UNIQUE INDEX rdb_id_unique (rdb_id ASC)) ENGINE=InnoDB DEFAULT CHARSET=utf8';
				} else {
					throw new InvalidArgumentException();
				}
				break;

			case 'drop':
				if ( $this->check_requirements( $args, array( 'table_name' ) ) ) {
					$sql = sprintf( 'DROP TABLE %s', $this->get_table_name( $args['table_name'] ) );
				} else {
					throw new InvalidArgumentException();
				}
				break;

			case 'rename':
				if ( $this->check_requirements( $args, array( 'table_name', 'new_table_name' ) ) ) {
					$sql = sprintf( 'RENAME TABLE %s TO %s', $this->get_table_name( $args['table_name'] ), $this->get_table_name( $args['new_table_name'] ) );
				} else {
					throw new InvalidArgumentException();
				}
				break;

			case 'add_column':
				if ( $this->check_requirements( $args, array( 'table_name', 'columns' ) ) ) {
					$sql    = sprintf( 'ALTER TABLE %s ADD COLUMN (', $this->get_table_name( $args['table_name'] ) );
					$column = array();
					foreach ( $args['columns'] as $key => $def ) {
						$sql_row = sprintf( '%s.`%s` %s %s', $this->get_table_name( $args['table_name'] ), $this->escape( $def->Field ), $this->escape( $def->Type ), $this->escape( $def->Null ) );
						if ( ! empty( $def->Default ) ) {
							$sql_row .= sprintf( ' DEFAULT `%s`', $this->escape( $def->Default ) );
						}
						$column[] = $sql_row;
					}
					$sql .= implode( ', ', $column );
					$sql .= ')';
				} else {
					throw new InvalidArgumentException();
				}
				break;

			case 'drop_column':
				if ( $this->check_requirements( $args, array( 'table_name', 'columns' ) ) ) {
					$sql = '';
					foreach ( $args['columns'] as $key => $def ) {
						$sql .= sprintf( 'ALTER TABLE %1$s DROP COLUMN %1$s.`%2$s`;\r\n', $this->get_table_name( $args['table_name'] ), $this->escape( $def->Field ) );
					}
				} else {
					throw new InvalidArgumentException();
				}
				break;

			case 'change_column':
				if ( $this->check_requirements( $args, array( 'table_name', 'columns' ) ) ) {
					foreach ( $args['columns'] as $key => $new_def ) {
						$sql .= sprintf( 'ALTER TABLE %1$s CHANGE COLUMN %1$s.%2$s %1$s.`%3$s` %4$s %5$s', $this->get_table_name( $args['table_name'] ), $this->get_column_key_data( $key ), $this->escape( $new_def->Field ), $this->escape( $new_def->Type ), $this->escape( $new_def->Null ) );
						if ( ! empty( $new_def->Default ) ) {
							$sql .= sprintf( ' DEFAULT `%s`', $this->escape( $new_def->Default ) );
						}
						$sql .= ';\r\n';
					}

				} else {
					throw new InvalidArgumentException();
				}
				break;
		}

		if ( $this->debug ) {
			if ( $this->is_test() ) {
				echo 'action (' . $action . ') SQL: ' . $sql . '\r\n';
			} else {
				Formidable2RdbLog::log( array(
					'action'         => $action,
					'object_type'    => Formidable2RdbManager::getShort(),
					'object_subtype' => 'Execute Query',
					'object_name'    => $sql,
				) );
			}
		}

		return mb_convert_encoding( $sql, 'utf8' );
	}

	/**
	 * Execute the query
	 *
	 * @param $sql
	 * @param null $params
	 *
	 * @return bool|PDOStatement
	 * @throws Formidable2RdbException
	 */
	public function execute( $sql, $params = null ) {
		try {
			$tran = $this->getMbd()->prepare( $sql );
			if ( ! empty( $params ) ) {
				$i = 1;
				foreach ( $params as $value => $type ) {
					$tran->bindValue( $i, $value );
					$i ++;
				}
			}
			$tran->execute();

			return $tran;
		} catch ( Exception $ex ) {
			throw new Formidable2RdbException( $ex->getMessage() );
		}
	}

	/**
	 * @param $args
	 * @param $requirements
	 *
	 * @return bool
	 */
	public function check_requirements( $args, $requirements ) {
		if ( ! is_array( $args ) ) {
			return false;
		}
		foreach ( $requirements as $key => $item ) {
			if ( ! array_key_exists( $item, $args ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Escape string to use in the query
	 *
	 * @param $params
	 *
	 * @return string
	 */
	public function escape( $params ) {
		if ( is_array( $params ) ) {
			foreach ( $params as $k => $v ) {
				if ( is_array( $v ) ) {
					$data[ $k ] = $this->escape( $v );
				} else {
					$data[ $k ] = addslashes( $v );
				}
			}
		} else {
			if ( ! is_float( $params ) ) {
				$params = addslashes( $params );
			}
		}

		return $params;
	}

	public function is_test() {
		return ( php_sapi_name() === 'cli' );
	}

}