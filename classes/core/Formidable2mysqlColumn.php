<?php

/**
 * Class Formidable2mysqlColumn Map Relational system Column to the system
 */
class Formidable2mysqlColumn {
	public $Field;
	public $Type;
	public $Null;
	public $Key;
	public $Default;
	public $Extra;
	public $Enabled;
	public $Length;
	public $Precision;
	public $Id;
	
	/**
	 * Formidable2mysqlColumn constructor.
	 *
	 * @param $Field
	 * @param $Type
	 * @param $Length
	 * @param int $Precision
	 * @param string $Null
	 * @param string $Key
	 * @param $Default
	 * @param string $Extra
	 * @param bool $Enabled
	 * @param int $Id
	 * @param bool $raw
	 */
	public function __construct( $Field, $Type, $Length, $Precision = 0, $Null = "YES", $Key = "", $Default = null, $Extra = "", $Enabled = false, $Id = 0, $raw = false ) {
		$this->Field     = $Field;
		$this->Type      = ( ! $raw ) ? $this->process_type( $Type, $Length, $Precision ) : $Type;
		$this->Null      = ( $Null == "YES" || $Null == "NULL" ) ? "NULL" : "NOT NULL";
		$this->Key       = $Key;
		$this->Default   = ( ! $raw ) ? $this->process_default( $Type, $Default ) : $Default;
		$this->Extra     = $Extra;
		$this->Enabled   = $Enabled;
		$this->Length    = $Length;
		$this->Precision = $Precision;
		$this->Id        = $Id;
	}
	
	/**
	 * Process the default type of column in base of the type
	 *
	 * @param $type
	 * @param $default
	 *
	 * @return mixed
	 */
	public function process_default( $type, $default ) {
		switch ( strtoupper( $type ) ) {
			case "LONGBLOB":
			case "TINYBLOB":
			case "TINYTEXT":
			case "BLOB":
			case "TEXT":
			case "MEDIUMBLOB":
			case "MEDIUMTEXT":
			case "LONGTEXT":
			case "DATETIME":
			case "TIMESTAMP":
			case "TIME":
			case "BINARY":
			case "VARBINARY":
				if ( ! empty( $default ) ) {
					$default = "";
				}
				break;
		}
		
		return $default;
	}
	
	/**
	 * Process the type to crete the string used in the query
	 *
	 * @param $type
	 * @param $length
	 * @param $precision
	 *
	 * @return string
	 */
	public function process_type( $type, $length, $precision ) {
		switch ( strtoupper( $type ) ) {
			case "BIT":
			case "TINYINT":
			case "SMALLINT":
			case "MEDIUMINT":
			case "INT":
			case "INTEGER":
			case "BIGINT":
			case "CHAR":
			case "VARCHAR":
				if ( ! empty( $length ) ) {
					$type = $type . "(" . $length . ")";
				} else {
					$type = $type . "(5)";
				}
				break;
			case "FLOAT":
			case "DECIMAL":
			case "DOUBLE":
				if ( ! empty( $length ) ) {
					$precision = ( ! empty( $precision ) ) ? $precision : 0;
					$type      = $type . "(" . $length . ", " . $precision . ")";
				} else {
					$type = $type . "(5, 2)";
				}
				break;
			
			case "TINYBLOB":
			case "TINYTEXT":
			case "BLOB":
			case "TEXT":
			case "MEDIUMBLOB":
			case "MEDIUMTEXT":
			case "LONGTEXT":
			case "LONGBLOB":
			case "DATE":
			case "DATETIME":
			case "TIMESTAMP":
			case "TIME":
				break;
			case "BINARY":
			case "VARBINARY":
			default:
				if ( ! empty( $length ) ) {
					$type = $type . "(" . $length . ")";
				}
				break;
		}
		
		return $type;
	}
}