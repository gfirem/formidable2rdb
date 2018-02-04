<?php
// No direct access is allowed
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbRequired {
	
	public function __construct() {
		add_action( 'init', array( $this, 'setup_init' ), 1, 1 );
	}
	
	public function setup_init() {
		// Only Check for requirements in the admin
		if ( ! is_admin() ) {
			return;
		}
		add_action( 'tgmpa_register', array( $this, 'setup_and_check' ) );
	}
	
	public function setup_and_check() {
		$required_plugins = array(
			array(
				'name'             => 'Formidable Forms',
				'slug'             => 'formidable',
				'required'         => true,
				'force_activation' => true,
			)
		);
		
		$config = array(
			'id'           => Formidable2RdbManager::getSlug(),
			'menu'         => Formidable2RdbManager::getSlug() . '-install-plugins',
			'parent_slug'  => 'admin.php',
			'capability'   => 'manage_options',
			'has_notices'  => false,
			'dismissable'  => false,
			'dismiss_msg'  => '',
			'is_automatic' => true,
		);
		
		if ( ! is_multisite() ) {
			$config[0]['menu']        = Formidable2RdbManager::getSlug() . '-install-plugins';
			$config[0]['parent_slug'] = 'admin.php';
		}
		
		// Call the tgmpa function to register the required required_plugins
		tgmpa( $required_plugins, $config );
	}
	
}