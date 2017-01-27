<?php

class WooSltBase {
	
	public $controller;
	public $Slug;
	public $Version;
	public $Instance;
	public $Api;
	
	public function __construct( $controller ) {
		$this->controller = $controller;
		
		$this->Slug     = $this->exec_controller( "getSlug" );
		$this->Version  = $this->exec_controller( "getVersion" );
		$this->Instance = $this->exec_controller( "getInstance" );
		$this->Api      = $this->exec_controller( "getApi" );
	}
	
	public function exec_controller( $fnc, $params = null ) {
		return call_user_func( array( $this->controller, $fnc ), $params );
	}
	
	public function get_license_option() {
		return get_site_option( $this->Slug . '_slt_license' );
	}
	
	public function update_license_option( $license_data ) {
		return update_site_option( $this->Slug . '_slt_license', $license_data );
	}
	
	public function prepare_request( $action, $args = array() ) {
		global $wp_version;
		$license_data = $this->get_license_option();
		
		return array(
			'woo_sl_action'     => $action,
			'version'           => $this->Version,
			'product_unique_id' => $this->Slug,
			'licence_key'       => $license_data['key'],
			'domain'            => $this->Instance,
			'wp-version'        => $wp_version,
		);
	}
}