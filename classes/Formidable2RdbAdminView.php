<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbAdminView {
	
	public static $credentials;
	/** @var Formidable2mysql */
	private $rdb_instance;
	
	public function __construct() {
//		if ( is_network_admin() ) {
//			add_action( 'network_admin_menu', array( $this, 'network_menu' ) );
//		} else {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
//		}
		add_action( 'admin_init', array( $this, 'register_admin_settings' ) );
		
		add_action( "wp_ajax_get_add_columns", array( $this, "get_add_columns" ) );
		add_action( "wp_ajax_test_credential", array( $this, "test_credential" ) );
		
		self::$credentials = array(
			"connection_user"    => Formidable2RdbManager::t( '<b>User</b>' ),
			"connection_pass"    => Formidable2RdbManager::t( '<b>Password</b>' ),
			"connection_host"    => Formidable2RdbManager::t( '<b>Host</b>' ),
			"connection_db_name" => Formidable2RdbManager::t( '<b>DB Name</b>' )
		);
		
		try {
			
			$rdb_core = new Formidable2RdbCore();
			
			$this->rdb_instance = $rdb_core->getHandler();
			
		} catch ( Formidable2RdbException $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage(), $ex->getBody() );
		} catch ( Exception $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage() );
		}
	}
	
	public static function get_credentials_array() {
		return self::$credentials;
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
				Formidable2RdbManager::handle_exception( $ex->getMessage(), $ex->getBody(), false );
			} catch ( Exception $ex ) {
				Formidable2RdbManager::handle_exception( $ex->getMessage(), null, false );
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
			if ( ! empty( $this->rdb_instance ) ) {
				try {
					$result["value"] = "exist_table";
					$result["data"]  = $this->rdb_instance->exist_table( Formidable2RdbGeneric::get_table_name( $_POST["table_name"] ) );
				} catch ( Formidable2RdbException $ex ) {
					Formidable2RdbManager::handle_exception( $ex->getMessage(), $ex->getBody(), false );
				} catch ( Exception $ex ) {
					Formidable2RdbManager::handle_exception( $ex->getMessage(), null, false );
				}
			}
		}
		
		$this->print_result( $result );
	}
	
	private function print_result( $result ) {
		$str = json_encode( $result );
		echo "$str";
		wp_die();
	}
	
	private function create_main_menu( $call_back ) {
		add_menu_page( Formidable2RdbManager::t( "Formidable2Rdb" ), Formidable2RdbManager::t( "Formidable2Rdb" ), ( is_network_admin() ) ? 'manage_network' : 'manage_options', Formidable2RdbManager::getSlug(), array( $this, $call_back ), F2M_IMAGE_PATH . "rdb-20.png" );
	}
	
	/**
	 * Create network menu
	 */
	public function network_menu() {
		$this->create_main_menu( 'menu_manage' );
	}
	
	/**
	 * Network menu view implementation
	 */
	public function network_manage() {
		
	}
	
	/**
	 * Site menu
	 */
	public function admin_menu() {
		$this->create_main_menu( 'menu_manage' );
		try {
			//Add a sub page for each table in the system if a single site
			if ( ! is_network_admin() ) {
				if ( ! empty( $this->rdb_instance ) ) {
					$tables = Formidable2RdbTrackTables::get_tables( get_current_blog_id() );
					foreach ( $tables as $table ) {
						if ( $this->rdb_instance->exist_table( $table["full_table"] ) ) {
							$menu_name_raw = Formidable2RdbManager::t( "Table: " ) . $table["table"];
							$menu_name     = ( strlen( $menu_name_raw > 15 ) ) ? trim( substr( Formidable2RdbManager::t( "Table: " ) . $table["table"], 0, 12 ) ) . "..." : $menu_name_raw;
							add_submenu_page( Formidable2RdbManager::getSlug(), Formidable2RdbManager::t( "Table: " ) . $table["table"], $menu_name, 'manage_options', Formidable2RdbManager::getSlug() . '_' . strtolower( $table["full_table"] ), array( $this, 'show_table' ) );
						} else {
							Formidable2RdbTrackTables::delete_table( $table["full_table"], "full_table" );
						}
					}
				}
			}
		} catch ( Formidable2RdbException $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage(), $ex->getBody() );
		} catch ( Exception $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage() );
		}
	}
	
	/**
	 * Menu view implementation
	 */
	public function menu_manage() {
		try {
			include( F2M_VIEW_PATH . '/admin.php' );
		} catch ( Formidable2RdbException $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage(), $ex->getBody() );
		} catch ( Exception $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage(), null );
		}
	}
	
	public function show_table() {
		try {
			$error = false;
			if ( ! empty( $_GET["page"] ) ) {
				$table_name = str_replace( Formidable2RdbManager::getSlug() . "_", "", $_GET["page"] );
				
				if ( ! empty( $table_name ) ) {
					if ( ! empty( $this->rdb_instance ) ) {
						if ( $this->rdb_instance->exist_table( $table_name ) ) {
							$db_column = $this->rdb_instance->get_columns( $table_name );
							
							$columns = array();
							
							foreach ( $db_column as $item ) {
								$columns[ strtolower( $item->Field ) ] = strtolower( $item->Field );
							}
							
							$table = new Formidable2RdbDataTable( $table_name, $columns );
							
							$table->prepare_items();
							
							?>
                            <div class="wrap">

                                <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
                                <h2><?php echo Formidable2RdbManager::t( "Table: " ) . $table_name ?></h2>
                                <p><?php echo Formidable2RdbManager::t( "Columns <b>rdb_id, create, entry_id</b> belong to the system." ); ?></p>
								<?php $this->echo_table_details( $table_name, $db_column ) ?>
                                <form id="persons-table" method="GET">
                                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
									<?php $table->display() ?>
                                </form>

                            </div>
							<?php
						} else {
							Formidable2RdbTrackTables::delete_table( $table_name, "full_table" );
						}
					} else {
						$error = true;
					}
				} else {
					$error = true;
				}
			} else {
				$error = true;
			}
			if ( $error ) {
				echo Formidable2RdbManager::t( "Not table detected." );
			}
		} catch ( Formidable2RdbException $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage(), $ex->getBody() );
		} catch ( Exception $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage() );
		}
	}
	
	public function register_admin_settings() {
		try {
			register_setting( Formidable2RdbManager::getSlug(), Formidable2RdbManager::getSlug(), array( $this, "process_settings" ) );
			
			if ( is_network_admin() ) {
				add_settings_section( 'section_key', '', '', Formidable2RdbManager::getSlug() );
				
				add_settings_field( Formidable2RdbManager::getShort() . 'version', Formidable2RdbManager::t( "Version: " ), array( $this, 'version' ), Formidable2RdbManager::getSlug(), 'section_key' );
				add_settings_field( Formidable2RdbManager::getShort() . 'licence_key', Formidable2RdbManager::t( "Order key: " ), array( $this, 'order_key' ), Formidable2RdbManager::getSlug(), 'section_key' );
				add_settings_field( Formidable2RdbManager::getShort() . 'status', Formidable2RdbManager::t( "Key status: " ), array( $this, 'status' ), Formidable2RdbManager::getSlug(), 'section_key' );
				
			} else {
				add_settings_section( 'debug_section', '', '', Formidable2RdbManager::getSlug() );
				
				add_settings_field( 'debug_data', Formidable2RdbManager::t( '<b>Enable Debug Mode</b>' ), array( $this, 'debug_data' ), Formidable2RdbManager::getSlug(), 'debug_section' );
				
				add_settings_section( 'section_connection', Formidable2RdbManager::t( "Connection Data" ), array( $this, "connection_wp_data" ), Formidable2RdbManager::getSlug() );
				
				foreach ( self::get_credentials_array() as $credential_key => $credential_name ) {
					add_settings_field( $credential_key, $credential_name, array( $this, $credential_key ), Formidable2RdbManager::getSlug(), 'section_connection' );
				}
				
				add_settings_section( 'save_data', '', array( $this, "save_data" ), Formidable2RdbManager::getSlug() );
				
				add_settings_section( 'section_overview', Formidable2RdbManager::t( "Tables overview" ), array( $this, 'section_overview_tables' ), Formidable2RdbManager::getSlug() );
			}
		} catch ( Formidable2RdbException $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage(), $ex->getBody() );
		} catch ( Exception $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage() );
		}
		
	}
	
	public function process_settings( $input ) {
		try {
			$new_input = array();
			if ( isset( $input['connection_wp_data'] ) ) {
				$new_input['connection_wp_data'] = absint( $input['connection_wp_data'] );
			}
			
			if ( isset( $input['debug_data'] ) ) {
				$new_input['debug_data'] = absint( $input['debug_data'] );
			}
			
			if ( isset( $input[ Formidable2RdbManager::getShort() . 'licence_key' ] ) ) {
				$gManager = GManagerFactory::buildManager( 'Formidable2RdbManager', 'formidable2rdb', Formidable2RdbManager::getShort() );
				$gManager->activate( $input[ Formidable2RdbManager::getShort() . 'licence_key' ] );
				update_option( Formidable2RdbManager::getShort() . 'licence_key', sanitize_text_field( $input[ Formidable2RdbManager::getShort() . 'licence_key' ] ) );
			} else {
				delete_option( Formidable2RdbManager::getShort() . 'licence_key' );
			}
			
			foreach ( self::get_credentials_array() as $credential_key => $credential_name ) {
				if ( isset( $input[ $credential_key ] ) ) {
					$new_input[ $credential_key ] = sanitize_text_field( $input[ $credential_key ] );
				}
			}
			
			if ( isset( $_SESSION["message"] ) ) {
				$_SESSION["message"] = array();
			}
			
			return $new_input;
		} catch ( Exception $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage() );
		}
		
		return $input;
	}
	
	public function debug_data() {
		$this->get_view_for( "debug_data", "checkbox" );
	}
	
	public function connection_wp_data() {
		echo "<b>" . Formidable2RdbManager::t( 'By default the Formidable2Rdb use the WP credential to connect to the database. In case of error with the provided credential the system keep using it.' ) . "</b>";
	}
	
	public function version() {
		echo "<span>" . Formidable2RdbManager::getVersion() . "</span>";
	}
	
	public function order_key() {
		$key = get_option( Formidable2RdbManager::getShort() . 'licence_key' );
		echo "<input name='formidable2rdb[" . Formidable2RdbManager::getShort() . 'licence_key' . "]' type='text' value='" . $key . "'/>";
	}
	
	public function status() {
		try {
			$gManager = GManagerFactory::buildManager( 'Formidable2RdbManager', 'formidable2rdb', Formidable2RdbManager::getShort() );
			echo "<p>" . $gManager->getStatus() . "</p>";
			
			
		} catch ( Exception $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage() );
		}
	}
	
	public function connection_user() {
		$this->get_view_for( "connection_user" );
	}
	
	public function connection_pass() {
		$this->get_view_for( "connection_pass", "password" );
	}
	
	public function connection_host() {
		$this->get_view_for( "connection_host" );
	}
	
	public function connection_db_name() {
		$this->get_view_for( "connection_db_name" );
		?> <p class="submit"><input type="button" name="test" id="f2r_test_credential" class="button" value="Test Credential"> <?php
		echo '<img class="f2r_loading" id="f2r_loading_<?php echo $this->number ?>" src="/wp-content/plugins/formidable/images/ajax_loader.gif"/>';
		echo "</p>";
	}
	
	public function save_data(){
		submit_button( null, "primary", "f2r_submit", false );
    }
	
	private function get_view_for( $setting, $type = "text" ) {
		$general_option = get_option( Formidable2RdbManager::getSlug() );
		$data           = '';
		if ( ! empty( $general_option[ $setting ] ) ) {
			$data = $general_option[ $setting ];
		}
		
		switch ( $type ) {
			case "checkbox":
				$value = checked( $data, 1, false). " value='1' ";
				break;
			default:
				$value = "value='" . $data . "'";
		}
		echo "<input name='formidable2rdb[" . $setting . "]' id='f2r_admin_" . $setting . "' type='" . $type . "' " . $value . " />";
	}
	
	public function section_overview_tables() {
		echo "<p>" . Formidable2RdbManager::t( "Show each table specification." ) . "</p>";
		
		try {
			$site_id = ( is_network_admin() ) ? false : get_current_blog_id();
			$tables  = Formidable2RdbTrackTables::get_tables( $site_id );
			
			foreach ( $tables as $table ) {
				if ( $this->rdb_instance->exist_table( $table["full_table"] ) ) {
					$columns = $this->rdb_instance->get_columns( $table["full_table"] );
					$this->echo_table_details( $table["full_table"], $columns );
				}
			}
            
		} catch ( Formidable2RdbException $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage(), $ex->getBody() );
		} catch ( Exception $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage() );
		}
		
	}
	
	/**
	 * Show the table details
	 *
	 * @param $table_name
	 * @param $columns
	 *
	 */
	private function echo_table_details( $table_name, $columns ) {
		
		echo "<hr/><p>" . Formidable2RdbManager::t( "Table: " ) . "<b>$table_name</b></p>";
		echo "<p>" . Formidable2RdbManager::t( "With columns: " ) . "</p><div class='f2r_tables_overview'><ul>";
		foreach ( $columns as $column ) {
			$line = "<li>" . Formidable2RdbManager::t( "<b>Name:</b> " ) . $column->Field . " " . Formidable2RdbManager::t( "<b>Type:</b> " ) . $column->Type . " " .
			        Formidable2RdbManager::t( "<b>Null:</b> " ) . $column->Null . " ";
			
			if ( ! empty( $column->Extra ) ) {
				$line .= Formidable2RdbManager::t( "<b>Extra:</b> " ) . $column->Extra . " ";
			}
			
			if ( ! empty( $column->Key ) ) {
				$line .= Formidable2RdbManager::t( "<b>Key:</b> " ) . $column->Key . " ";
			}
			
			if ( ! empty( $column->Default ) ) {
				$line .= Formidable2RdbManager::t( "<b>Default:</b> " ) . $column->Default . " ";
			}
			
			$line .= "</li>";
			
			echo "$line";
		}
		echo "</ul></div>";
	}
}