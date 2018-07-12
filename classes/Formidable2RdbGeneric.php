<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbGeneric {

	function __construct() {
		add_filter( F2M_PREFIX . 'plugin_action_links_' . F2M_BASE_NAME, array( $this, 'add_formidable_key_field_setting_link' ), 9, 2 );
		add_action( 'admin_footer', array( $this, 'enqueue_js' ) );
		//add_action( 'wp_footer', array( $this, 'enqueue_js' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ) );
		//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ) );
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
			Formidable2RdbManager::handle_exception( "Error loading the notification view." );
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
			"map_column"          => self::get_granted_column_type(),
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
		$url = admin_url( 'admin.php?page=formidable2rdb' );

		return sprintf( '<a href="%s">%s</a>', esc_attr( $url ), Formidable2RdbManager::t( "Settings" ) );
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
	 * Get all types mapped to rdb
	 *
	 * @return Formidable2RdbColumnType[][]
	 */
	public static function get_granted_column_type() {
		$bit       = new Formidable2RdbColumnType( "BIT", "Bit", true, true, false, "number" );
		$tinyint   = new Formidable2RdbColumnType( "TINYINT", "TinyInt", true, true, false, "number" );
		$smallint  = new Formidable2RdbColumnType( "SMALLINT", "SmallInt", true, true, false, "number" );
		$mediumint = new Formidable2RdbColumnType( "MEDIUMINT", "MediumInt", true, true, false, "number" );
		$integer   = new Formidable2RdbColumnType( "INTEGER", "Integer", true, true, false, "number" );
		$bigint    = new Formidable2RdbColumnType( "BIGINT", "BigInt", true, true, false, "number" );
		$float     = new Formidable2RdbColumnType( "FLOAT", "Float", true, true, true, "number", 10, 2 );
		$decimal   = new Formidable2RdbColumnType( "DECIMAL", "Decimal", true, true, true, "number" );
		$double    = new Formidable2RdbColumnType( "DOUBLE", "Double", true, true, true, "number" );

		$date      = new Formidable2RdbColumnType( "DATE", "Date", false, false, false, "date" );
		$datetime  = new Formidable2RdbColumnType( "DATETIME", "DateTime", false, false, false, "date" );//The length is used to store the fraction second part fsp
		$timestamp = new Formidable2RdbColumnType( "TIMESTAMP", "TimeStamp", false, false, false, "date" );//The length is used to store the fraction second part fsp
		$time      = new Formidable2RdbColumnType( "TIME", "Time", false, false, false, "date" );//The length is used to store the fraction second part fsp

		$char       = new Formidable2RdbColumnType( "CHAR", "Char", true, true, false, "text", 20 );
		$varchar    = new Formidable2RdbColumnType( "VARCHAR", "Varchar", true, true, false, "text", 20 );
		$binary     = new Formidable2RdbColumnType( "BINARY", "Binary", false, true, false, "text", 20 );
		$varbinary  = new Formidable2RdbColumnType( "VARBINARY", "VarBinary", false, true, false, "text", 20 );
		$tinyblob   = new Formidable2RdbColumnType( "TINYBLOB", "TinyBlob", false, false, false );
		$tinytext   = new Formidable2RdbColumnType( "TINYTEXT", "TinyText", false, false, false );
		$blob       = new Formidable2RdbColumnType( "BLOB", "Blob", false, false, false );
		$text       = new Formidable2RdbColumnType( "TEXT", "Text", false, false, false );
		$mediumblob = new Formidable2RdbColumnType( "MEDIUMBLOB", "MediumBlob", false, false, false );
		$mediumtext = new Formidable2RdbColumnType( "MEDIUMTEXT", "MediumText", false, false, false );
		$longblob   = new Formidable2RdbColumnType( "LONGBLOB", "LongBlob", false, false, false );
		$longtext   = new Formidable2RdbColumnType( "LONGTEXT", "LongText", false, false, false );

		$text_group = array( $varchar, $char, $binary, $varbinary, $tinyblob, $tinytext, $blob, $text, $mediumblob, $mediumtext, $longblob, $longtext );
		$date_group = array( $datetime, $date, $timestamp, $time );
		$int_group  = array( $integer, $tinyint, $smallint, $mediumint, $bit, $bigint, $float, $decimal, $double );

		return apply_filters( "formidable2rdb_grant_map_fields", array(
			'text'     => $text_group,
			'textarea' => $text_group,
			'checkbox' => array_merge( $text_group, $int_group ),
			'radio'    => array_merge( $text_group, $int_group ),
			'select'   => array_merge( $text_group, $int_group ),
			'email'    => array( $varchar, $longtext ),
			'url'      => array( $varchar, $longtext ),
			'file'     => array( $varchar, $longtext ),
			'rte'      => array( $varchar, $longtext ),
			'number'   => $int_group,
			'phone'    => array_merge( $text_group, $int_group ),
			'date'     => $date_group,
			'time'     => $date_group,
			'image'    => array( $longtext ),
			'scale'    => array_merge( $text_group, $int_group ),
			'data'     => array_merge( $text_group, $int_group ),
			'lookup'   => array_merge( $text_group, $int_group ),
			'hidden'   => array_merge( $text_group, array_merge( $int_group, $date_group ) ),
			'user_id'  => array( $integer ),
			'password' => array( $varchar, $longtext ),
			'html'     => array( $blob, $text, $mediumblob, $mediumtext, $longblob, $longtext ),
			'tag'      => array_merge( $text_group, $int_group ),
			'address'  => array( $varchar, $longtext ),
			'divider'  => array( $longtext ),
		) );
	}

	/**
	 * Get rdb column type mapped to formidable field type
	 *
	 * @param $field_type
	 *
	 * @return Formidable2RdbColumnType[]
	 */
	public static function get_granted_column_type_for_field( $field_type ) {
		$mapped = self::get_granted_column_type();

		return $mapped[ $field_type ];
	}

	/**
	 * Get rdb column type by Type
	 *
	 * @param $type
	 *
	 * @return Formidable2RdbColumnType
	 */
	public static function get_granted_column_by_type( $type ) {
		$mapped = self::get_granted_column_type();

		foreach ( $mapped as $key => $items ) {
			foreach ( $items as $item ) {
				if ( strpos( $type, $item->getType() ) !== false ) {
					return $item;
				}
			}
		}

		return null;
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

		return $wpdb->prefix . strtolower( $table_name );
	}
}