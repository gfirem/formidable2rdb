<?php

class WOO_SLT_options_interface {
	
	var $licence;
	private $my_account;
	
	function __construct() {
		$this->my_account = "http://www.gfirem.com/en/my-account-en/";
		if ( get_locale() == 'es_ES' ) {
			$this->my_account = "http://www.gfirem.com/es/my-account/";
		}
		$this->licence = new WOO_SLT_licence();
		
		if ( isset( $_GET['page'] ) && ( $_GET['page'] == Formidable2RdbManager::getSlug() . '_license' || $_GET['page'] == Formidable2RdbManager::getSlug() ) ) {
			add_action( 'init', array( $this, 'options_update' ), 1 );
		}
		
		if ( ! is_network_admin() ) {
			add_action( 'admin_menu', array( $this, 'menu' ) );
		} else {
			add_action( 'network_admin_menu', array( $this, 'menu' ) );
		}
		
		if ( ! $this->licence->licence_key_verify() ) {
			add_action( 'admin_notices', array( $this, 'admin_no_key_notices' ) );
			add_action( 'network_admin_notices', array( $this, 'admin_no_key_notices' ) );
		}
		
	}
	
	function __destruct() {
		
	}
	
	function menu() {
		if ( ! $this->licence->licence_key_verify() ) {
			$call_back = 'licence_form';
		} else {
			$call_back = 'licence_deactivate_form';
		}
		
		if ( ! is_network_admin() ) {
			$hookID = add_submenu_page( Formidable2RdbManager::getSlug(), __( "License", 'formidable2rdb' ), __( "License", 'formidable2rdb' ), 'manage_options', Formidable2RdbManager::getSlug() . '_license', array( $this, $call_back ) );
		} else {
			$hookID = add_menu_page( __( "Formidable2Rdb", 'formidable2rdb' ), __( "Formidable2Rdb", 'formidable2rdb' ), 'manage_network', Formidable2RdbManager::getSlug(), array( $this, $call_back ), F2M_IMAGE_PATH . "rdb-20.png" );
		}
		
		add_action( 'load-' . $hookID, array( $this, 'admin_notices' ) );
		add_action( 'admin_print_styles-' . $hookID, array( $this, 'admin_print_styles' ) );
	}
	
	
	function options_interface() {
		if ( ! $this->licence->licence_key_verify() && ! is_multisite() ) {
			$this->licence_form();
			
			return;
		}
		
		if ( ! $this->licence->licence_key_verify() && is_multisite() ) {
			$this->licence_multisite_require_nottice();
			
			return;
		}
	}
	
	function options_update() {
		if ( isset( $_POST['slt_licence_form_submit'] ) ) {
			$this->licence_form_submit();
			
			return;
		}
	}
	
	/**
	 * Show notice if the key are not set
	 */
	function admin_notices() {
		global $slt_form_submit_messages;
		
		if ( $slt_form_submit_messages == '' ) {
			return;
		}
		
		$messages = $slt_form_submit_messages;
		
		if ( count( $messages ) > 0 ) {
			echo "<div id='notice' class='updated fade'><p>" . implode( "</p><p>", $messages ) . "</p></div>";
		}
	}
	
	/**
	 * Include license view styles
	 */
	function admin_print_styles() {
		wp_register_style( 'wooslt_admin', F2M_CSS_PATH . 'woosl.css' );
		wp_enqueue_style( 'wooslt_admin' );
	}
	
	/**
	 * Define is where and when show the notification of no license
	 */
	function admin_no_key_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$screen = get_current_screen();
		
		if ( is_multisite() ) {
			if ( isset( $screen->id ) && $screen->id == 'toplevel_page_formidable2rdb-network' ) {
				return;
			}
			?>
            <div class="updated fade"><p><?php _e( "WooCommerce Software Licence - Plugin Example is inactive, please enter your", 'formidable2rdb' ) ?> <a href="<?php echo network_admin_url() ?>admin.php?page=formidable2rdb"><?php _e( "Licence Key", 'formidable2rdb' ) ?></a></p></div><?php
		} else {
			if ( isset( $screen->id ) && $screen->id == 'formidable2rdb_page_formidable2rdb_license' ) {
				return;
			}
			?>
            <div class="updated fade"><p><?php _e( "WooCommerce Software Licence - Plugin Example is inactive, please enter your", 'formidable2rdb' ) ?> <a href="<?php echo admin_url() ?>admin.php?page=formidable2rdb_license'"><?php _e( "Licence Key", 'formidable2rdb' ) ?></a></p></div><?php
		}
	}
	
	function licence_form_submit() {
		global $slt_form_submit_messages;
		
		//check for de-activation
		if ( isset( $_POST['slt_licence_form_submit'] ) && isset( $_POST['slt_licence_deactivate'] ) && wp_verify_nonce( $_POST['slt_license_nonce'], 'slt_license' ) ) {
			global $slt_form_submit_messages;
			
			$license_data = get_site_option( 'slt_license' );
			$license_key  = $license_data['key'];
			
			//build the request query
			$args        = array(
				'woo_sl_action'     => 'deactivate',
				'licence_key'       => $license_key,
				'product_unique_id' => WOO_SLT_PRODUCT_ID,
				'domain'            => WOO_SLT_INSTANCE
			);
			$request_uri = WOO_SLT_APP_API_URL . '?' . http_build_query( $args, '', '&' );
			$data        = wp_remote_get( $request_uri );
			
			if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
				$slt_form_submit_messages[] .= __( 'There was a problem connecting to ', 'formidable2rdb' ) . WOO_SLT_APP_API_URL;
				
				return;
			}
			
			$response_block = json_decode( $data['body'] );
			//retrieve the last message within the $response_block
			$response_block = $response_block[ count( $response_block ) - 1 ];
			$response       = $response_block->message;
			
			if ( isset( $response_block->status ) ) {
				if ( $response_block->status == 'success' && $response_block->status_code == 's201' ) {
					//the license is active and the software is active
					$slt_form_submit_messages[] = $response_block->message;
					
					$license_data = get_site_option( 'slt_license' );
					
					//save the license
					$license_data['key']        = '';
					$license_data['last_check'] = time();
					
					update_site_option( 'slt_license', $license_data );
				} else //if message code is e104  force de-activation
				{
					$license_data = get_site_option( 'slt_license' );
				}
				if ( $response_block->status_code == 'e002' || $response_block->status_code == 'e104' ) {
					
					
					//save the license
					$license_data['key']        = '';
					$license_data['last_check'] = time();
					
					update_site_option( 'slt_license', $license_data );
				} else {
					$slt_form_submit_messages[] = __( 'There was a problem deactivating the licence: ', 'formidable2rdb' ) . $response_block->message;
					//save the license
					$license_data['key']        = '';
					$license_data['last_check'] = '';
					update_site_option( 'slt_license', $license_data );
					
					return;
				}
			} else {
				$slt_form_submit_messages[] = __( 'There was a problem with the data block received from ' . WOO_SLT_APP_API_URL, 'formidable2rdb' );
				
				return;
			}
			
			//redirect
			$current_url = 'http' . ( isset( $_SERVER['HTTPS'] ) ? 's' : '' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			
			wp_redirect( $current_url );
			die();
			
		}
		
		
		if ( isset( $_POST['slt_licence_form_submit'] ) && wp_verify_nonce( $_POST['slt_license_nonce'], 'slt_license' ) ) {
			
			$license_key = isset( $_POST['license_key'] ) ? sanitize_key( trim( $_POST['license_key'] ) ) : '';
			
			if ( $license_key == '' ) {
				$slt_form_submit_messages[] = __( "Licence Key can't be empty", 'formidable2rdb' );
				
				return;
			}
			
			//build the request query
			$args        = array(
				'woo_sl_action'     => 'activate',
				'licence_key'       => $license_key,
				'product_unique_id' => WOO_SLT_PRODUCT_ID,
				'domain'            => WOO_SLT_INSTANCE
			);
			$request_uri = WOO_SLT_APP_API_URL . '?' . http_build_query( $args, '', '&' );
			$data        = wp_remote_get( $request_uri );
			
			if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
				$slt_form_submit_messages[] .= __( 'There was a problem connecting to ', 'formidable2rdb' ) . WOO_SLT_APP_API_URL;
				
				return;
			}
			
			$response_block = json_decode( $data['body'] );
			//retrieve the last message within the $response_block
			$response_block = $response_block[ count( $response_block ) - 1 ];
			$response       = $response_block->message;
			
			if ( isset( $response_block->status ) ) {
				if ( $response_block->status == 'success' && $response_block->status_code == 's100' ) {
					//the license is active and the software is active
					$slt_form_submit_messages[] = $response_block->message;
					
					$license_data = get_site_option( 'slt_license' );
					
					//save the license
					$license_data['key']        = $license_key;
					$license_data['last_check'] = time();
					
					update_site_option( 'slt_license', $license_data );
					
				} else {
					$slt_form_submit_messages[] = __( 'There was a problem activating the licence: ', 'formidable2rdb' ) . $response_block->message;
					
					return;
				}
			} else {
				$slt_form_submit_messages[] = __( 'There was a problem with the data block received from ' . WOO_SLT_APP_API_URL, 'formidable2rdb' );
				
				return;
			}
			
			//redirect
			$current_url = 'http' . ( isset( $_SERVER['HTTPS'] ) ? 's' : '' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			
			wp_redirect( $current_url );
			die();
		}
		
	}
	
	function licence_form() {
		?>
        <div class="wrap">
            <div id="icon-settings" class="icon32"></div>
            <h2><?php _e( "Software License", 'formidable2rdb' ) ?><br/>&nbsp;</h2>
            <form id="form_data" name="form" method="post">
                <div class="postbox">
					<?php wp_nonce_field( 'slt_license', 'slt_license_nonce' ); ?>
                    <input type="hidden" name="slt_licence_form_submit" value="true"/>
                    <div class="section section-text ">
                        <h4 class="heading"><?php _e( "License Key", 'formidable2rdb' ) ?></h4>
                        <div class="option">
                            <div class="controls">
                                <input autocomplete="off" type="text" value="" name="license_key" class="text-input">
                            </div>
                            <div class="explain"><?php _e( "Enter the License Key you got when bought this product. If you lost the key, you can always retrieve it from", 'formidable2rdb' ) ?> <a href="<?php echo $this->my_account; ?>" target="_blank"><?php _e( "My Account", 'formidable2rdb' ) ?></a><br/>
								<?php _e( "More keys can be generate from", 'formidable2rdb' ) ?> <a href="<?php echo $this->my_account; ?>" target="_blank"><?php _e( "My Account", 'formidable2rdb' ) ?></a>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="submit">
                    <input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Save', 'formidable2rdb' ) ?>">
                </p>
            </form>
        </div>
		<?php
	}
	
	function licence_deactivate_form() {
		$license_data = get_site_option( 'slt_license' );
		
		if ( is_multisite() ) {
			?>
            <div class="wrap">
            <div id="icon-settings" class="icon32"></div>
            <h2><?php _e( "Software License", 'formidable2rdb' ) ?></h2>
			<?php
		}
		
		?>
        <div id="form_data">
            <div class="postbox">
                <form id="form_data" name="form" method="post">
					<?php wp_nonce_field( 'slt_license', 'slt_license_nonce' ); ?>
                    <input type="hidden" name="slt_licence_form_submit" value="true"/>
                    <input type="hidden" name="slt_licence_deactivate" value="true"/>

                    <div class="section section-text ">
                        <h4 class="heading"><?php _e( "License Key", 'formidable2rdb' ) ?></h4>
                        <div class="option">
                            <div class="controls">
								<?php if ( $this->licence->is_local_instance() ) { ?>
                                    <p>Local instance, no key applied.</p>
								<?php } else { ?>
                                    <p><b><?php echo substr( $license_data['key'], 0, 20 ) ?>-xxxxx-xxxxx</b>&nbsp;&nbsp;&nbsp;<a class="button-secondary" title="Deactivate" href="javascript: void(0)" onclick="jQuery(this).closest('form').submit();"><?php _e( "Deactivate", 'formidable2rdb' ) ?></a></p>
								<?php } ?>
                            </div>
                            <div class="explain"><?php _e( "You can generate more keys from", 'formidable2rdb' ) ?> <a href="<?php echo $this->my_account; ?>" target="_blank">My Account</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
		<?php
		if ( is_multisite() ) {
			?>
            </div>
			<?php
		}
	}
	
	function licence_multisite_require_nottice() {
		?>
        <div class="wrap">
            <div id="icon-settings" class="icon32"></div>
            <h2><?php _e( "Software License", 'formidable2rdb' ) ?></h2>
            <div id="form_data">
                <div class="postbox">
                    <div class="section section-text ">
                        <h4 class="heading"><?php _e( "License Key Required", 'formidable2rdb' ) ?>!</h4>
                        <div class="option">
                            <div class="explain"><?php _e( "Enter the License Key you got when bought this product. If you lost the key, you can always retrieve it from", 'formidable2rdb' ) ?> <a href="<?php echo $this->my_account; ?>" target="_blank"><?php _e( "My Account", 'formidable2rdb' ) ?></a><br/>
								<?php _e( "More keys can be generate from", 'formidable2rdb' ) ?> <a href="<?php echo $this->my_account; ?>" target="_blank"><?php _e( "My Account", 'formidable2rdb' ) ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
		
	}
	
	
}