<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbManager {
	
	private static $plugin_slug;
	private static $plugin_short = 'Formidable2Rdb';
	protected static $version;
	protected static $api_url = 'http://www.gfirem.com/woo-software-license/index.php';
	protected static $instance = 'http://www.gfirem.com/woo-software-license/index.php';
	
	public function __construct() {
		self::$plugin_slug = 'formidable2rdb';
		self::$version     = '1.0.1';
		self::$instance    = str_replace( array( "https://", "http://" ), "", network_site_url() );
		
		try {
			//Load resources
			include_once( F2M_WOOSL_PATH . 'WooSLFactory.php' );
			
			require_once 'model/Formidable2RdbColumnType.php';
			require_once 'Formidable2RdbLog.php';
			new Formidable2RdbLog();
			
			require_once 'Formidable2RdbGeneric.php';
			new Formidable2RdbGeneric();
			
			require_once 'core/TreeWalker.php';
			require_once "Formidable2RdbException.php";
			require_once 'Formidable2RdbCore.php';
			
			require_once 'class-wp-list-table.php';
			require_once 'Formidable2RdbDataTable.php';
			
			require_once 'Formidable2RdbFieldOptions.php';
			new Formidable2RdbFieldOptions();
			
			require_once 'Formidable2RdbAdminView.php';
			new Formidable2RdbAdminView();
			
			$wooslt_license   = WooSLFactory::buildManager( "Formidable2RdbManager", "WooSltLicence" );
			$wooslt_interface = WooSLFactory::buildManager( "Formidable2RdbManager", "WooSltOptionsInterface" );
			add_action( 'after_setup_theme', array( $this, 'run_updater' ) );
			
			require_once 'Formidable2RdbTrackTables.php';
			new Formidable2RdbTrackTables();
			
			add_action( 'frm_registered_form_actions', array( $this, 'register_action' ) );
		} catch ( Exception $ex ) {
			Formidable2RdbGeneric::setMessage( array(
				"message" => "Formidable2RdbManager->__construct()::" . $ex,
				"type"    => "danger"
			) );
		}
	}
	
	/**
	 * Function to handle the plugins update
	 */
	function run_updater() {
		$updater = WooSLFactory::buildManager( "Formidable2RdbManager", "WooSltCodeAutoUpdate", array(
				"api_url" => self::getApi(),
				"slug"    => self::getSlug(),
				"plugin"  => "formidable2rdb/formidable2rdb.php"
			)
		);
		
		// Take over the update check
		add_filter( 'pre_set_site_transient_update_plugins', array( $updater, 'check_for_plugin_update' ) );
		
		// Take over the Plugin info screen
		add_filter( 'plugins_api', array( $updater, 'plugins_api_call' ), 10, 3 );
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
	
	static function getApi() {
		return self::$api_url;
	}
	
	static function getInstance() {
		return self::$instance;
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
	
	/**
	 * Handle exceptions
	 *
	 * @param $message
	 * @param null $body
	 *
	 * @param bool $output
	 *
	 * @return mixed
	 */
	public static function handle_exception( $message, $body = null, $output = true ) {
		if ( ! empty( $body ) && is_array( $body ) ) {
			$error_str = "";
			foreach ( $body as $key => $value ) {
				if ( ! empty( $value ) ) {
					$error_str .= $key . " : " . $value . "<br/>";
				}
			}
			
			Formidable2RdbLog::log( array(
				'action'         => "F2R_Management",
				'object_type'    => Formidable2RdbManager::getShort(),
				'object_subtype' => "detail_error",
				'object_name'    => $message,
			) );
			
			if ( $output ) {
				self::show_error( $message );
			}
			
			return $message;
		} else {
			
			Formidable2RdbLog::log( array(
				'action'         => "F2R_Management",
				'object_type'    => Formidable2RdbManager::getShort(),
				'object_subtype' => "detail_error",
				'object_name'    => $message,
			) );
			
			if ( $output ) {
				self::show_error( $message );
			}
		}
		
		return $message;
	}
	
	/**
	 * Output error
	 *
	 * @param $string
	 * @param string $type
	 */
	public static function show_error( $string, $type = "danger" ) {
		Formidable2RdbGeneric::setMessage( array(
			"message" => $string,
			"type"    => $type
		) );
	}
}