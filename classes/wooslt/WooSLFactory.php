<?php

if ( ! class_exists( 'WooSLFactory', false ) ) {
	
	class WooSLFactory {
		protected static $classVersions = array();
		protected static $sorted = false;
		
		public static function forms_autoloader( $class_name ) {
			try {
				if ( ! preg_match( '/^WooSlt.+$/', $class_name ) ) {
					return;
				}
				$file = F2M_WOOSL_PATH . $class_name . '.php';
				if ( file_exists( $file ) ) {
					include( $file );
				}
			} catch ( Exception $exc ) {
				echo $exc->getMessage();
			}
		}
		
		/**
		 * Create a new instance
		 *
		 * @param $controller
		 * @param $class
		 *
		 * @return mixed
		 *
		 */
		public static function buildManager( $controller, $class, $args = null ) {
			spl_autoload_register( array( 'WooSLFactory', 'forms_autoloader' ) );
			$class = self::getLatestClassVersion( $class );
			
			if ( ! empty( $args ) ) {
				return new $class( $controller, $args );
			} else {
				return new $class( $controller );
			}
		}
		
		/**
		 * Get the specific class name for the latest available version of a class.
		 *
		 * @param string $class
		 *
		 * @return string|null
		 */
		public static function getLatestClassVersion( $class ) {
			if ( ! self::$sorted ) {
				self::sortVersions();
			}
			
			if ( isset( self::$classVersions[ $class ] ) ) {
				return reset( self::$classVersions[ $class ] );
			} else {
				return null;
			}
		}
		
		/**
		 * Sort available class versions in descending order (i.e. newest first).
		 */
		protected static function sortVersions() {
			foreach ( self::$classVersions as $class => $versions ) {
				uksort( $versions, array( __CLASS__, 'compareVersions' ) );
				self::$classVersions[ $class ] = $versions;
			}
			self::$sorted = true;
		}
		
		protected static function compareVersions( $a, $b ) {
			return - version_compare( $a, $b );
		}
		
		/**
		 * Register a version of a class.
		 *
		 * @access private This method is only for internal use by the library.
		 *
		 * @param string $generalClass Class name without version numbers, e.g. 'PluginUpdateChecker'.
		 * @param string $versionedClass Actual class name, e.g. 'PluginUpdateChecker_1_2'.
		 * @param string $version Version number, e.g. '1.2'.
		 */
		public static function addVersion( $generalClass, $versionedClass, $version ) {
			if ( ! isset( self::$classVersions[ $generalClass ] ) ) {
				self::$classVersions[ $generalClass ] = array();
			}
			self::$classVersions[ $generalClass ][ $version ] = $versionedClass;
			self::$sorted                                     = false;
		}
	}
}

WooSLFactory::addVersion( 'WooSltCodeAutoUpdate', 'WooSltCodeAutoUpdate_1_0', '1.0' );
WooSLFactory::addVersion( 'WooSltLicence', 'WooSltLicence_1_0', '1.0' );
WooSLFactory::addVersion( 'WooSltOptionsInterface', 'WooSltOptionsInterface_1_0', '1.0' );