<?php
//if ( ! defined( 'ABSPATH' ) ) {
//	exit;
//}

require_once 'Formidable2RdbGeneric.php';

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

	public $debug = false;


	function __construct( $args = array(), $debug = false ) {
		$this->handler = $this->load_handler( $args );
		$this->mbd     = $this->getMbd();
		$this->debug = $debug;
	}

	/**
	 * @internal $credential array $cong_array parameters to config the core array with keys driver, host, dbname, user, pass. The option "driver" can be one of [mysql|pgsql|sqlite]
	 *
	 * @param array $args array $cong_array parameters to config the core array with keys driver, host, dbname, user, pass. The option "driver" can be one of [mysql|pgsql|sqlite]
	 *
	 * @return mixed
	 */
	private function load_handler( $args = array() ) {
		try {
			$options = array();

			if ( ! empty( $args ) ) {
				$options = $args;
			} else {
				//Get the correct credential
				$general_option = get_option( Formidable2RdbManager::getSlug() );

				$db_credential = array(
					"driver" => "mysql",
					"host"   => DB_HOST,
					"dbname" => DB_NAME,
					"user"   => DB_USER,
					"pass"   => DB_PASSWORD,
					"debug"  => $this->debug,
				);

				if ( ! empty( $general_option ) ) {
					if ( ! empty( $general_option['debug_data'] ) ) {
						$this->debug            = true;
						$db_credential["debug"] = true;
					}
					if ( empty( $general_option['use_system_credentials'] ) ) {
						//Get connection from setting
						if ( ! empty( $general_option['connection_user'] ) && ! empty( $general_option['connection_host'] ) && ! empty( $general_option['connection_db_name'] ) ) {
							//Use credential form the settings
							$options = array(
								"driver" => "mysql",
								"host"   => $general_option['connection_host'],
								"dbname" => $general_option['connection_db_name'],
								"user"   => $general_option['connection_user'],
								"pass"   => isset( $general_option['connection_pass'] ) ? $general_option['connection_pass'] : "",
								"debug"  => ! empty( $general_option['debug_data'] ) ? true : false,
							);
						} else {
							//Use credential from WP
							$options = $db_credential;
						}
					} else {
						//Get connection from the WP
						$options = $db_credential;
					}
				} else {
					//Get connection from the WP
					$options = $db_credential;
				}
			}


			if ( ! is_array( $options ) && empty( $options["driver"] ) ) {
				throw new Exception( "No driver name detect to load the Handler file." );
			}

			//Load different handles. This enable de plugin to  use different Relational Database Providers
			$handler        = 'Formidable2' . $options["driver"];
			$handlerColumn  = 'Formidable2' . $options["driver"] . 'Column';
			$handlerColFact = 'Formidable2' . $options["driver"] . 'ColumnFactory';
			require_once 'core/' . $handler . '.php';
			require_once 'core/' . $handlerColumn . '.php';
			require_once 'core/' . $handlerColFact . '.php';
			$class = new $handler( $options );

			return $class;
		} catch ( Exception $ex ) {
			Formidable2RdbGeneric::setMessage( array(
				"message" => "Formidable2RdbManager->__construct()::" . $ex->getMessage(),
				"type"    => "danger"
			) );
		}

		return false;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @param $cong_array
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance( $cong_array = array() ) {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new Formidable2RdbCore( $cong_array = array() );
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