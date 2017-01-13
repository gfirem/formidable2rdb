<?php
//if ( ! defined( 'ABSPATH' ) ) {
//	exit;
//}

class Formidable2RdbCore {
	
	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;
	
	/**
	 * PDO instance
	 *
	 * @var PDO
	 */
	public $mbd;
	
	/**
	 * @var Object implements Formidable2RdbInterface
	 */
	public $handler;
	
	/**
	 * @param $cong_array array parameters to config the core array with keys driver, host, dbname, user, pass. The option "driver" can be one of [mysql|pgsql|sqlite]
	 *
	 * @throws Exception
	 */
	function __construct( $cong_array ) {
		if ( ! is_array( $cong_array ) ) {
			throw new Exception( 'Parameter need to be an array' );
		}
		
		$this->handler = $this->load_handler( $cong_array );
		$this->mbd     = $this->handler->getMbd();
		
	}
	
	private function load_handler( $cong_array ) {
		if ( ! is_array( $cong_array ) && empty( $cong_array["driver"] ) ) {
			throw new Exception( "No driver name detect to load the Handler file." );
		}
		
		$handler        = 'Formidable2' . $cong_array["driver"];
		$handlerColumn  = 'Formidable2' . $cong_array["driver"] . 'Column';
		$handlerColFact = 'Formidable2' . $cong_array["driver"] . 'ColumnFactory';
		require_once 'core/' . $handler . '.php';
		require_once 'core/' . $handlerColumn . '.php';
		require_once 'core/' . $handlerColFact . '.php';
		
		$class = new $handler( $cong_array );
		
		return $class;
	}
	
	/**
	 * Return an instance of this class.
	 *
	 * @param $cong_array
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance( $cong_array ) {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new Formidable2RdbCore( $cong_array );
		}
		
		return self::$instance;
	}
	
	/**
	 * @return PDO
	 */
	public function getMbd() {
		return $this->mbd;
	}
	
	/**
	 * @return Object
	 */
	public function getHandler() {
		return $this->handler;
	}
	
	
}