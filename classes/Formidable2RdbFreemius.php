<?php
/**
 * @package WordPress
 * @subpackage Formidable, formidable_copy_action
 * @author GFireM
 * @copyright 2017
 * @link http://www.gfirem.com
 * @license http://www.apache.org/licenses/
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Formidable2RdbFreemius {

	private static $plugins_slug = 'formidable2rdb';

	public static function getLicense() {
		$fs = self::getFreemius();

		return $fs->_get_license();
	}

	/**
	 * @return Freemius
	 */
	public static function getFreemius() {
		global $formidable2rdb_fs;

		return $formidable2rdb_fs;
	}

	public static function start_freemius() {
		global $formidable2rdb_fs;

		if ( ! isset( $formidable2rdb_fs ) ) {
			require_once dirname( __FILE__ ) . '/freemius/start.php';

			try {
				$formidable2rdb_fs = fs_dynamic_init( array(
					'id'               => '723',
					'slug'             => 'formidable2rdb',
					'type'             => 'plugin',
					'public_key'       => 'pk_dc6ce49acae620ba0bc501baaebe6',
					'is_premium'       => true,
					'is_premium_only'  => true,
					'has_addons'       => false,
					'has_paid_plans'   => true,
					'is_org_compliant' => false,
					'trial'            => array(
						'days'               => 14,
						'is_require_payment' => true,
					),
					'menu'             => array(
						'slug'       => 'formidable2rdb',
						'first-path' => 'admin.php?page=formidable2rdb',
						'support'    => false,
					),
					'secret_key'       => 'sk_{w=^Dogkm9ou=Derl#t]$luqo6Y2o',
				) );
			} catch ( Freemius_Exception $e ) {
				return false;
			}
		}

		return $formidable2rdb_fs;
	}
}