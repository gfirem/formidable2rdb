<?php
if ( ! class_exists( 'WooSltCodeAutoUpdate_1_0' ) ) {
	class WooSltCodeAutoUpdate_1_0 extends WooSltBase {
		# URL to check for updates, this is where the index.php script goes
		public $api_url;
		private $slug;
		public $plugin;
		
		public function __construct( $controller, $args ) {
			parent::__construct( $controller );
			$this->api_url = $args['api_url'];
			$this->slug    = $args['slug'];
			$this->plugin  = $args['plugin'];
		}
		
		public function check_for_plugin_update( $checked_data ) {
			if ( empty( $checked_data->checked ) || ! isset( $checked_data->checked[ $this->plugin ] ) ) {
				return $checked_data;
			}
			
			$request_string = $this->prepare_request( 'plugin_update' );
			if ( $request_string === false ) {
				return $checked_data;
			}
			
			// Start checking for an update
			$request_uri = $this->api_url . '?' . http_build_query( $request_string, '', '&' );
			$data        = wp_remote_get( $request_uri );
			
			if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
				return $checked_data;
			}
			
			$response_block = json_decode( $data['body'] );
			
			if ( ! is_array( $response_block ) || count( $response_block ) < 1 ) {
				return $checked_data;
			}
			
			//retrieve the last message within the $response_block
			$response_block = $response_block[ count( $response_block ) - 1 ];
			$response       = isset( $response_block->message ) ? $response_block->message : '';
			
			if ( is_object( $response ) && ! empty( $response ) ) // Feed the update data into WP updater
			{
				//include slug and plugin data
				$response->slug   = $this->slug;
				$response->plugin = $this->plugin;
				
				$checked_data->response[ $this->plugin ] = $response;
			}
			
			return $checked_data;
		}
		
		
		public function plugins_api_call( $def, $action, $args ) {
			if ( ! is_object( $args ) || ! isset( $args->slug ) || $args->slug != $this->slug ) {
				return false;
			}
			
			//$args->package_type = $this->package_type;
			
			$request_string = $this->prepare_request( $action );
			if ( $request_string === false ) {
				return new WP_Error( 'plugins_api_failed', __( 'An error occur when try to identify the pluguin.', 'wooslt' ) . '&lt;/p> &lt;p>&lt;a href=&quot;?&quot; onclick=&quot;document.location.reload(); return false;&quot;>' . __( 'Try again', 'wooslt' ) . '&lt;/a>' );
			};
			
			$request_uri = $this->api_url . '?' . http_build_query( $request_string, '', '&' );
			$data        = wp_remote_get( $request_uri );
			
			if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
				return new WP_Error( 'plugins_api_failed', __( 'An Unexpected HTTP Error occurred during the API request.', 'wooslt' ) . '&lt;/p> &lt;p>&lt;a href=&quot;?&quot; onclick=&quot;document.location.reload(); return false;&quot;>' . __( 'Try again', 'wooslt' ) . '&lt;/a>', $data->get_error_message() );
			}
			
			$response_block = json_decode( $data['body'] );
			//retrieve the last message within the $response_block
			$response_block = $response_block[ count( $response_block ) - 1 ];
			$response       = $response_block->message;
			
			if ( is_object( $response ) && ! empty( $response ) ) // Feed the update data into WP updater
			{
				//include slug and plugin data
				$response->slug   = $this->slug;
				$response->plugin = $this->plugin;
				
				$response->sections = (array) $response->sections;
				$response->banners  = (array) $response->banners;
				
				return $response;
			}
		}
	}
}

