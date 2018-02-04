<?php

/**
 * Class Formidable2RdbColumnType Define the column type need to integrate into the plugins
 */
class Formidable2RdbColumnType {
	/** @var  String Type used to insert into the rdb system */
	public $type;
	/** @var  String Name to show in to the user */
	public $name;
	/** @var  Boolean This is handle the front validation */
	public $need_default;
	/** @var  Boolean This is handle the front validation */
	public $need_length;
	/** @var  Integer Default length value */
	public $default_length;
	/** @var  Boolean This is handle the front validation */
	public $need_precision;
	/** @var  Integer Default precision value */
	public $default_precision;
	/** @var  String Defined group for validation as [number|text] */
	public $group;
	
	/**
	 * Generate new Type of column to manage the front validation, user UI and the type to use in the internal query
	 *
	 * @param String $type Type to use in the internals query
	 * @param String $name Name to show to the user
	 * @param Boolean $need_default True if the type need a default value in the UI
	 * @param Boolean $need_length True if the type need a length value in the UI
	 * @param Boolean $need_precision True if the type need a precision value in the UI
	 * @param String [number|text] $group Defined group for validation as [number|text]
	 * @param int $default_length Default length value
	 * @param int $default_precision Default precision value
	 */
	public function __construct( $type, $name, $need_default, $need_length, $need_precision, $group = "text", $default_length = 5, $default_precision = 0 ) {
		$this->type              = $type;
		$this->name              = $name;
		$this->need_default      = $need_default;
		$this->need_length       = $need_length;
		$this->need_precision    = $need_precision;
		$this->default_length    = $default_length;
		$this->default_precision = $default_precision;
		$this->group             = $group;
	}
	
	/**
	 * @return String
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * @return String
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @return bool
	 */
	public function isNeedDefault() {
		return $this->need_default;
	}
	
	/**
	 * @return bool
	 */
	public function isNeedLength() {
		return $this->need_length;
	}
	
	/**
	 * @return bool
	 */
	public function isNeedPrecision() {
		return $this->need_precision;
	}
	
	
	/**
	 * @return int
	 */
	public function getDefaultLength() {
		return $this->default_length;
	}
	
	/**
	 * @return int
	 */
	public function getDefaultPrecision() {
		return $this->default_precision;
	}
	
	/**
	 * @return String
	 */
	public function getGroup() {
		return $this->group;
	}
	
}