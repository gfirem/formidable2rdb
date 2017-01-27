<?php
if ( ! class_exists( 'WooSltLicence_1_0' ) ) {
	class WooSltLicence_1_0 extends WooSltBase {
		
		function __construct( $controller ) {
			parent::__construct( $controller );
			$this->licence_deactivation_check();
		}
		
		public function licence_key_verify() {
			
			$license_data = $this->get_license_option();
			
			if ( $this->is_local_instance() ) {
				return true;
			}
			
			if ( ! isset( $license_data['key'] ) || $license_data['key'] == '' ) {
				return false;
			}
			
			return true;
		}
		
		function is_local_instance() {
			return false;
			$instance = trailingslashit( $this->Instance );
			if (
				strpos( $instance, base64_decode( 'bG9jYWxob3N0Lw==' ) ) !== false
				|| strpos( $instance, base64_decode( 'MTI3LjAuMC4xLw==' ) ) !== false
				|| strpos( $instance, base64_decode( 'c3RhZ2luZy53cGVuZ2luZS5jb20=' ) ) !== false
			) {
				return true;
			}
			
			return false;
		}
		
		function licence_deactivation_check() {
			if ( ! $this->licence_key_verify() || $this->is_local_instance() === true ) {
				return;
			}
			
			$license_data = $this->get_license_option();
			
			if ( isset( $license_data['last_check'] ) ) {
				if ( time() < ( $license_data['last_check'] + 86400 ) ) {
					return;
				}
			}
			
			$args        = $this->prepare_request( 'status-check' );
			$request_uri = $this->Api. '?' . http_build_query( $args, '', '&' );
			$data        = wp_remote_get( $request_uri );
			
			if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
				return;
			}
			
			$response_block = json_decode( $data['body'] );
			//retrieve the last message within the $response_block
			$response_block = $response_block[ count( $response_block ) - 1 ];
			$response       = $response_block->message;
			
			if ( isset( $response_block->status ) ) {
				if ( $response_block->status == 'success' ) {
					if ( $response_block->status_code == 's203' || $response_block->status_code == 's204' ) {
						$license_data['key'] = '';
					}
				}
				
				if ( $response_block->status == 'error' ) {
					$license_data['key'] = '';
				}
			}
			
			$license_data['last_check'] = time();
			$this->update_license_option( $license_data );
		}
	}
}