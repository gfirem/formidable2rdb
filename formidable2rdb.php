<?php
/**
 * @package           Formidable2Rdb
 *
 * @wordpress-plugin
 * Plugin Name:       Formidable2Rdb
 * Description:       Formidable action to push data to MySQL Table.
 * Version:           1.2.2
 * Author:            gfirem
 * License:           Apache License 2.0
 * License URI:       http://www.apache.org/licenses/
 * Network:           True
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Formidable2Rdb' ) ) :
	
	$sid = session_id();
	if ( empty( $sid ) ) {
		session_start();
	}
	
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Formidable2RdbFreemius.php';
	Formidable2RdbFreemius::start_freemius();
	
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
			define( 'F2M_ABSPATH', trailingslashit( str_replace( "\\", "/", plugin_dir_path( __FILE__ ) ) ) );
			define( 'F2M_URLPATH', trailingslashit( str_replace( "\\", "/", plugin_dir_url( __FILE__ ) ) ) );
			define( 'F2M_PREFIX', is_network_admin() ? 'network_admin_' : '' );
			define( 'F2M_JS_PATH', F2M_URLPATH . 'assets/js/' );
			define( 'F2M_CSS_PATH', F2M_URLPATH . 'assets/css/' );
			define( 'F2M_IMAGE_PATH', F2M_URLPATH . 'assets/image/' );
			define( 'F2M_VIEW_PATH', F2M_ABSPATH . 'view/' );
			define( 'F2M_TEMPLATES_PATH', F2M_ABSPATH . 'templates/' );
			define( 'F2M_CLASS_PATH', F2M_ABSPATH . 'classes/' );
			define( 'F2M_WOOSL_PATH', F2M_CLASS_PATH . 'wooslt/' );
			$this->load_plugin_textdomain();
			
			require_once F2M_CLASS_PATH . 'Formidable2RdbManager.php';
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
