<?php

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
		$this->Default   = $Default;
		$this->Extra     = $Extra;
		$this->Enabled   = $Enabled;
		$this->Length    = $Length;
		$this->Precision = $Precision;
		$this->Id = $Id;
	}
	
	public function process_type( $type, $length, $precision ) {
		switch ( $type ) {
			case "VARCHAR":
				if ( ! empty( $length ) ) {
					$type = "VARCHAR(" . $length . ")";
				} else {
					$type = "VARCHAR(100)";
				}
				break;
			case "FLOAT":
				if ( ! empty( $length ) && ! empty( $precision ) ) {
					$type = "FLOAT(" . $length . ", " . $precision . ")";
				} else {
					$type = "FLOAT(20, 2)";
				}
				break;
			case "DATETIME":
			case "TIMESTAMP":
			case "LONGTEXT":
				break;
			default:
				if ( ! empty( $length ) ) {
					$type = $type . "(" . $length . ")";
				}
				break;
		}
		
		return $type;
	}
}