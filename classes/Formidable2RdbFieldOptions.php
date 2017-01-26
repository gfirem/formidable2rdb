<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbFieldOptions {
	
	public function __construct() {
		add_action( 'frm_field_options_form', array( $this, 'field_option_form' ), 10, 3 );
	}
	
	/**
	 * Display the additional options
	 *
	 * @param $field
	 * @param $display
	 * @param $values
	 */
	public function field_option_form( $field, $display, $values ) {
		if ( ! FrmField::is_option_true( $field, "formidable2rdb" ) ) {
			return;
		}
		
		$map = FrmField::get_option( $field, "formidable2rdb" );
		
		if ( ! empty( $map ) ) {
			/** @var Formidable2mysqlColumn $map */
			$map    = maybe_unserialize( $map );
			$map_ui = Formidable2RdbGeneric::get_granted_column_by_type( $map->Type );
			if ( ! empty( $map_ui ) ) {
				include F2M_VIEW_PATH . 'field_options.php';
			}
		}
	}
	
	private function load_script() {
		wp_enqueue_style( '', F2M_CSS_PATH . 'jquery.switchButton.css', array(), Formidable2RdbManager::getVersion() );
		wp_enqueue_script( 'aa', F2M_JS_PATH . 'asdas.js', array( "jquery" ), Formidable2RdbManager::getVersion(), true );
	}
}