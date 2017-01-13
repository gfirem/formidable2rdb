<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbManager {
	
	private static $plugin_slug;
	private static $plugin_short = 'Formidable2Rdb';
	protected static $version;
	
	public function __construct() {
		add_action( 'init', array( $this, "session_start" ) );
		
		self::$plugin_slug = 'formidable2rdb';
		self::$version     = '1.0.0';
		
		//Load resources
		require_once 'Formidable2RdbLog.php';
		new Formidable2RdbLog();
		
		require_once 'Formidable2RdbTrackTables.php';
		new Formidable2RdbTrackTables();
		
		require_once 'Formidable2RdbAdmin.php';
		new Formidable2RdbAdmin();
		
		require_once 'Formidable2RdbSettings.php';
		new Formidable2RdbSettings();
		
		add_action( 'frm_registered_form_actions', array( $this, 'register_action' ) );
	}
	
	/**
	 * Start session
	 */
	public function session_start() {
		$sid = session_id();
		if ( empty( $sid ) ) {
			session_start();
		}
	}
	
	/**
	 * Register action
	 *
	 * @param $actions
	 *
	 * @return mixed
	 */
	public function register_action( $actions ) {
		$actions['formidable2rdb'] = 'Formidable2RdbAction';
		require_once 'Formidable2RdbAction.php';
		
		return $actions;
	}
	
	static function getShort() {
		return self::$plugin_short;
	}
	
	static function getVersion() {
		return self::$version;
	}
	
	static function getSlug() {
		return self::$plugin_slug;
	}
	
	/**
	 * Translate string to main Domain
	 *
	 * @param $str
	 *
	 * @return string
	 */
	public static function t( $str ) {
		return __( $str, 'formidable2rdb' );
	}
}