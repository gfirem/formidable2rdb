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
		self::$version     = '1.1.0';
		self::$instance    = str_replace( array( "https://", "http://" ), "", network_site_url() );
		
		try {
			require_once 'class-tgm-plugin-activation.php';
			require_once 'Formidable2RdbRequired.php';
			new Formidable2RdbRequired();
			$this->for_fs();
			if ( self::is_formidable_active() ) {
				
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
				
				
				require_once 'Formidable2RdbTrackTables.php';
				new Formidable2RdbTrackTables();
				
				add_action( 'frm_registered_form_actions', array( $this, 'register_action' ) );
			}
		} catch ( Exception $ex ) {
			Formidable2RdbGeneric::setMessage( array(
				"message" => "Formidable2RdbManager->__construct()::" . $ex,
				"type"    => "danger"
			) );
		}
	}
	
	public static function load_plugins_dependency() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	public static function is_formidable_active() {
		self::load_plugins_dependency();
		
		return is_plugin_active( 'formidable/formidable.php' );
	}
	
	public function for_fs() {
		/** @var Freemius $for_fs */
		global $for_fs;
		
		if ( ! isset( $for_fs ) ) {
			// Include Freemius SDK.
			require_once dirname( __FILE__ ) . '/freemius/start.php';
			
			$for_fs = fs_dynamic_init( array(
				'id'                  => '723',
				'slug'                => Formidable2RdbManager::getSlug(),
				'type'                => 'plugin',
				'public_key'          => 'pk_dc6ce49acae620ba0bc501baaebe6',
				'is_premium'          => true,
				'has_premium_version' => false,
				'has_addons'          => false,
				'has_paid_plans'      => true,
				'is_org_compliant'    => false,
				'menu'                => array(
					'slug'       => Formidable2RdbManager::getSlug(),
					'first-path' => 'admin.php?page=' . Formidable2RdbManager::getSlug(),
					'support'    => false,
				),
				// Set the SDK to work in a sandbox mode (for development & testing).
				// IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
				'secret_key'          => 'sk_{w=^Dogkm9ou=Derl#t]$luqo6Y2o',
			) );
		}
		
		return $for_fs;
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