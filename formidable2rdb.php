<?php

/**
 *
 * @since             1.0.0
 * @package           Formidable2Rdb
 *
 * @wordpress-plugin
 * Plugin Name:       Formidable2Rdb
 * Description:       Formidable action to push data to Relational Data Base.
 * Version:           1.0.0
 * Author:            gfirem
 * License:           Apache License 2.0
 * License URI:       http://www.apache.org/licenses/
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Formidable2Rdb' ) ) :
	
	require_once 'plugin-update-checker/plugin-update-checker.php';
	
	$myUpdateChecker = PucFactory::buildUpdateChecker( 'http://www.gfirem.com/update-services/?action=get_metadata&slug=formidable2rdb', __FILE__ );
	$myUpdateChecker->addQueryArgFilter( 'appendFormidable2RdbQueryArgsCredentials' );
	
	/**
	 * Append the order key to the update server URL
	 *
	 * @param $queryArgs
	 *
	 * @return mixed
	 */
	function appendFormidable2RdbQueryArgsCredentials( $queryArgs ) {
		$queryArgs['order_key'] = get_option( Formidable2RdbManager::getShort() . 'licence_key', '' );
		
		return $queryArgs;
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
			$this->load_plugin_textdomain();
			
			require_once 'classes/Formidable2RdbManager.php';
			$manager = new Formidable2RdbManager();
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
