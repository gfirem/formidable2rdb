<?php
/**
 * @package           Formidable2Rdb
 *
 * @wordpress-plugin
 * Plugin Name:       Formidable2Rdb
 * Description:       Formidable action to push data to Relational Data Base.
 * Version:           1.0.1
 * Author:            gfirem
 * License:           Apache License 2.0
 * License URI:       http://www.apache.org/licenses/
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Formidable2Rdb' ) ) :
	
	$sid = session_id();
	if ( empty( $sid ) ) {
		session_start();
	}
	
	class Formidable2Rdb {
		
		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;
		
		/**
		 * Initialize the plugin.
		 */
		private function __construct() {
			define( 'F2M_BASE_NAME', plugin_basename( __FILE__ ) );
			define( 'F2M_PREFIX', is_network_admin() ? 'network_admin_' : '' );
			define( 'F2M_JS_PATH', plugin_dir_url( __FILE__ ) . 'assets/js/' );
			define( 'F2M_CSS_PATH', plugin_dir_url( __FILE__ ) . 'assets/css/' );
			define( 'F2M_IMAGE_PATH', plugin_dir_url( __FILE__ ) . 'assets/image/' );
			define( 'F2M_VIEW_PATH', plugin_dir_path( __FILE__ ) . 'view/' );
			define( 'F2M_TEMPLATES_PATH', plugin_dir_path( __FILE__ ) . 'templates/' );
			define( 'F2M_CLASS_PATH', plugin_dir_path( __FILE__ ) . 'classes/' );
			define( 'F2M_WOOSL_PATH', F2M_CLASS_PATH . 'woosl/' );
			$this->load_plugin_textdomain();
			
			//WOOSL
			define( 'WOO_SLT_URL', plugins_url( '', __FILE__ ) );
			define( 'WOO_SLT_APP_API_URL', 'http://www.gfirem.com/woo-software-license/index.php' );
			
			define( 'WOO_SLT_VERSION', '1.0.9' );
			define( 'WOO_SLT_DB_VERSION', '1.0' );
			
			define( 'WOO_SLT_PRODUCT_ID', 'formidable2rdb' );
			define( 'WOO_SLT_INSTANCE', str_replace( array( "https://", "http://" ), "", network_site_url() ) );
			
			require_once 'classes/Formidable2RdbManager.php';
			new Formidable2RdbManager();
		}
		
		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			
			return self::$instance;
		}
		
		/**
		 * Load the plugin text domain for translation.
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'formidable2rdb', false, basename( dirname( __FILE__ ) ) . '/languages' );
		}
		
	}
	
	add_action( 'plugins_loaded', array( 'Formidable2Rdb', 'get_instance' ) );

endif;
