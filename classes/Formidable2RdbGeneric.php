<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbGeneric {
	
	function __construct() {
		require_once 'GManagerFactory.php';
		
		add_filter( F2M_PREFIX . 'plugin_action_links_' . F2M_BASE_NAME, array( $this, 'add_formidable_key_field_setting_link' ), 9, 2 );
		
		add_action( 'admin_footer', array( $this, 'enqueue_js' ) );
		add_action( 'wp_footer', array( $this, 'enqueue_js' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ) );
		
		add_action( "wp_ajax_get_add_columns", array( $this, "get_add_columns" ) );
		add_action( "wp_ajax_test_credential", array( $this, "test_credential" ) );
	}
	
	
	/**
	 * Generate GUID
	 *
	 * @return string
	 */
	public static function GUID() {
		if ( function_exists( 'com_create_guid' ) === true ) {
			return trim( com_create_guid(), '{}' );
		}
		
		return sprintf( '%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand( 0, 65535 ), mt_rand( 0, 65535 ), mt_rand( 0, 65535 ), mt_rand( 16384, 20479 ), mt_rand( 32768, 49151 ), mt_rand( 0, 65535 ), mt_rand( 0, 65535 ), mt_rand( 0, 65535 ) );
	}
	
	public function test_credential() {
		if ( ! ( is_array( $_GET ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}
		
		$result = array(
			"value" => ":(",
			"data"  => - 1,
		);
		
		if ( ! check_ajax_referer( 'f2r_security_code' ) ) {
			$this->print_result( $result );
		}
		
		if ( ! empty( $_POST["user"] ) && ! empty( $_POST["host"] ) && ! empty( $_POST["db_name"] ) ) {
			$args  = array(
				"driver" => "mysql",
				"host"   => $_POST["host"],
				"dbname" => $_POST["db_name"],
				"user"   => $_POST["user"],
				"pass"   => $_POST["pass"],
				"debug"  => false,
			);
			$error = true;
			try {
				$rdb_core    = new Formidable2RdbCore( $args );
				$db_instance = $rdb_core->getHandler();
				if ( ! empty( $db_instance ) ) {
					$error = false;
				}
				
			} catch ( Formidable2RdbException $ex ) {
			} catch ( Exception $ex ) {
			}
			$result["value"] = "test_credential";
			$result["data"]  = $error;
		}
		
		$this->print_result( $result );
	}
	
	public function get_add_columns() {
		if ( ! ( is_array( $_GET ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}
		
		$result = array(
			"value" => ":(",
			"data"  => - 1,
		);
		
		if ( ! check_ajax_referer( 'f2r_security_code' ) ) {
			$this->print_result( $result );
		}
		
		if ( ! empty( $_POST["table_name"] ) ) {
			$rdb_core    = new Formidable2RdbCore();
			$db_instance = $rdb_core->getHandler();
			
			$result["value"] = "exist_table";
			$result["data"]  = $db_instance->exist_table( Formidable2RdbGeneric::get_table_name( $_POST["table_name"] ) );
		}
		
		$this->print_result( $result );
	}
	
	private function print_result( $result ) {
		$str = json_encode( $result );
		echo "$str";
		wp_die();
	}
	
	/**
	 * Show a new notification. The array message need [message, type, title],
	 * where type can be [success|info|warning|danger] by default info
	 *
	 * @param array $message
	 */
	public static function setMessage( $message ) {
		if ( empty( $message["type"] ) ) {
			$message["type"] = "info";
		}
		$_SESSION["message"] = $message;
	}
	
	/**
	 * Include styles in admin and front
	 */
	public function enqueue_style() {
		wp_enqueue_style( 'jquery' );
		wp_enqueue_style( 'animate.min', F2M_CSS_PATH . 'animate.min.css', array(), Formidable2RdbManager::getVersion() );
		wp_enqueue_style( 'formidable2rdb', F2M_CSS_PATH . 'formidable2rdb.css', array(), Formidable2RdbManager::getVersion() );
		wp_enqueue_style( 'formidable2rdb_notification', F2M_CSS_PATH . 'formidable2rdb_notification.css', array(), Formidable2RdbManager::getVersion() );
	}
	
	/**
	 * Include script in admin and front
	 */
	public function enqueue_js() {
		wp_enqueue_script( 'formidable2rdb', F2M_JS_PATH . 'formidable2rdb.js', array( "jquery" ), Formidable2RdbManager::getVersion(), true );
		wp_enqueue_script( 'bootstrap-notify', F2M_JS_PATH . 'bootstrap-notify.min.js', array( "jquery" ), Formidable2RdbManager::getVersion(), true );
		wp_enqueue_script( 'formidable2rdb_notification', F2M_JS_PATH . 'formidable2rdb_notification.js', array( "jquery" ), Formidable2RdbManager::getVersion(), true );
		wp_enqueue_script( 'formidable2rdb_action', F2M_JS_PATH . 'formidable2rdb_action.js', array( "jquery" ), Formidable2RdbManager::getVersion(), true );
		
		$notification_view = $this->load_notification_string();
		if ( $notification_view === false ) {
			//TODO Add to log error not load the view of the notification
		}
		
		$args = array(
			"message"             => ( ! empty( $_SESSION["message"] ) ) ? $_SESSION["message"] : "",
			"view"                => $notification_view,
			"security"            => wp_create_nonce( 'f2r_security_code' ),
			"admin_url"           => admin_url( 'admin-ajax.php' ),
			"table_name_required" => Formidable2RdbManager::t( "Please provide the name for the table." ),
			"general_error"       => Formidable2RdbManager::t( "Error, please contact the admin." ),
			"table_already_exist" => Formidable2RdbManager::t( "Already exist a table with the provided name." ),
			"f2r_auto_map"        => true,
			"credential_fail"     => Formidable2RdbManager::t( "FAIL!" ),
			"credential_invalid"  => Formidable2RdbManager::t( "Invalid Credential, please review it." ),
		);
		
		wp_localize_script( 'formidable2rdb', 'formidable2rdb', $args );
		
		if ( isset( $_SESSION["message"] ) ) {
			$_SESSION["message"] = array();
		}
	}
	
	private function load_notification_string() {
		$base_template_path = self::load_field_template( "notification" );
		
		return file_get_contents( $base_template_path );
	}
	
	public static function load_field_template( $part ) {
		$template = locate_template( array( 'templates/' . $part . '.php' ) );
		if ( ! $template ) {
			return F2M_TEMPLATES_PATH . $part . ".php";
		} else {
			return $template;
		}
	}
	
	/**
	 * Setting link to add in the plugins list
	 *
	 * @return string
	 */
	public static function get_setting_link() {
		return sprintf( '<a href="%s">%s</a>', esc_attr( admin_url( 'admin.php?page=formidable-settings&t=formidable2rdb_settings' ) ), Formidable2RdbManager::t( "Settings" ) );
	}
	
	/**
	 * Add a "Settings" link to the plugin row in the "Plugins" page.
	 *
	 * @param $links
	 * @param string $pluginFile
	 *
	 * @return array
	 * @internal param array $pluginMeta Array of meta links.
	 */
	public function add_formidable_key_field_setting_link( $links, $pluginFile ) {
		if ( $pluginFile == 'formidable2rdb/formidable2rdb.php' ) {
			array_unshift( $links, self::get_setting_link() );
		}
		
		return $links;
	}
	
	
	/**
	 * Get Generic types mapped to Rdb
	 *
	 * @return array
	 */
	public static function get_generic_type() {
		$map = apply_filters( "formidable2rdb_map_field", array(
				"VARCHAR"   => "String",
				"INTEGER"   => "Integer",
				"FLOAT"     => "Float",
				"LONGTEXT"  => "LongText",
				"DATE"      => "Date",
				"DATETIME"  => "DateTime",
				"TIMESTAMP" => "TimeStamp"
			)
		);
		
		
		return array_merge( array( "none" => Formidable2RdbManager::t( "Select type" ) ), $map );
	}
	
	/**
	 * Get all formidable fields
	 *
	 * @return array
	 */
	public static function get_all_formidable_fields() {
		return array_merge( FrmField::field_selection(), FrmField::pro_field_selection() );
	}
	
	/**
	 * Get all rdb column type mapped to formidable field type
	 *
	 * @param $field_type
	 *
	 * @return mixed
	 */
	public static function get_granted_column_type_for_field( $field_type ) {
		$mapped = apply_filters( "formidable2rdb_grant_map_fields", array(
			'text'     => array( "VARCHAR" => "String" ),
			'textarea' => array( "VARCHAR" => "String", "LONGTEXT" => "LongText" ),
			'checkbox' => array( "VARCHAR" => "String", "INTEGER" => "Integer" ),
			'radio'    => array( "VARCHAR" => "String", "INTEGER" => "Integer" ),
			'select'   => array( "VARCHAR" => "String", "INTEGER" => "Integer" ),
			'email'    => array( "VARCHAR" => "String" ),
			'url'      => array( "VARCHAR" => "String" ),
			'file'     => array( "VARCHAR" => "String" ),
			'rte'      => array( "VARCHAR" => "String", "LONGTEXT" => "LongText" ),
			'number'   => array( "INTEGER" => "Integer", "FLOAT" => "Float" ),
			'phone'    => array( "VARCHAR" => "String", "INTEGER" => "Integer" ),
			'date'     => array( "DATETIME" => "DateTime", "TIMESTAMP" => "TimeStamp", "INTEGER" => "Integer" ),
			'time'     => array( "INTEGER" => "Integer", "TIMESTAMP" => "TimeStamp" ),
			'image'    => array( "VARCHAR" => "String" ),
			'scale'    => array( "VARCHAR" => "String", "INTEGER" => "Integer" ),
			'data'     => array( "VARCHAR" => "String", "INTEGER" => "Integer" ),
			'lookup'   => array( "VARCHAR" => "String", "INTEGER" => "Integer" ),
			'hidden'   => array( "VARCHAR" => "String", "INTEGER" => "Integer", "DATETIME" => "DateTime", "TIMESTAMP" => "TimeStamp", "LONGTEXT" => "LongText" ),
			'user_id'  => array( "INTEGER" => "Integer" ),
			'password' => array( "VARCHAR" => "String" ),
			'html'     => array( "LONGTEXT" => "LongText" ),
			'tag'      => array( "VARCHAR" => "String", "INTEGER" => "Integer" ),
			'address'  => array( "VARCHAR" => "String", "LONGTEXT" => "LongText" ),
		) );
		
		return $mapped[ $field_type ];
	}
	
	/**
	 * List of exclude fields
	 *
	 * @return array
	 */
	public static function exclude_fields() {
		return apply_filters( "formidable2rdb_exclude_fields",
			array(
				"captcha",
				"divider",
				"end_divider",
				"break",
				"form",
				"credit_card",
			)
		);
	}
	
	/**
	 * Get table name with prefix
	 *
	 * @param $table_name
	 *
	 * @return string
	 */
	public static function get_table_name( $table_name ) {
		global $wpdb;
		
		return $wpdb->prefix . $table_name;
	}
}