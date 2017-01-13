<?php

class Formidable2RdbException extends Exception {
	
	protected $body;
	
	public function __construct( $message = "", $body = null ) {
		if ( ! empty( $body ) ) {
			$this->body = $body;
		}
		parent::__construct( $message, 0, null );
	}
	
	/**
	 * @return array|null
	 */
	public function getBody() {
		return $this->body;
	}
	
	
}