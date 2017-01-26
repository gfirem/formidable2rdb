<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbManager {
	
	private static $plugin_slug;
	private static $plugin_short = 'Formidable2Rdb';
	protected static $version;
	
	public function __construct() {
		self::$plugin_slug = 'formidable2rdb';
		self::$version     = '1.0.1';
		
		try {
			//Load resources
			include_once( F2M_WOOSL_PATH . 'class.wooslt.php' );
			include_once( F2M_WOOSL_PATH . 'class.licence.php' );
			include_once( F2M_WOOSL_PATH . 'class.options.php' );
			include_once( F2M_WOOSL_PATH . 'class.updater.php' );
			
			
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
			
			global $WOO_SLT;
			$WOO_SLT = new WOO_SLT();
			
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