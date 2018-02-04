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

	private static function get_license_option() {
		return maybe_unserialize( get_site_option( self::$plugins_slug . '_license' ) );
	}

	private static function update_license_option( $license_data ) {
		return update_site_option( self::$plugins_slug . '_license', maybe_serialize( $license_data ) );
	}

	private static function delete_license_option() {
		return delete_site_option( self::$plugins_slug . '_license' );
	}

	public static function isUnlimited() {
		$license = self::getLicense();
		if ( ! empty( $license ) ) {
			$result = $license->is_unlimited();
		} else {
			$result = $license;
		}

		return $result;
	}

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

	private static function process_multi_site() {
		add_action( 'fs_before_admin_menu_init_' . self::$plugins_slug, function () {
			if ( defined( 'BLOG_ID_CURRENT_SITE' ) && get_current_blog_id() != BLOG_ID_CURRENT_SITE ) {
				$free         = freemius( self::$plugins_slug );
				$license_data = self::get_license_option();
				if ( ! empty( $license_data['key'] ) && ! empty( $license_data['user'] ) && ! empty( $license_data['site'] ) ) {
					if ( ! $free->is_paying() && ! $free->has_active_valid_license() && is_multisite() && empty( $license_data['key']->quota ) ) {
						$free->opt_in_from_code( $license_data['user'], $license_data['key']->secret_key );
					}
				}
			}
		} );

		add_action( 'fs_after_account_connection_' . self::$plugins_slug, function ( $user, $site ) {
			if ( defined( 'BLOG_ID_CURRENT_SITE' ) && get_current_blog_id() == BLOG_ID_CURRENT_SITE ) {
				$free = freemius( self::$plugins_slug );
				if ( is_numeric( $site->license_id ) ) {
					$license = $free->_get_license_by_id( $site->license_id );
					if ( $license !== false && empty( $license->quota ) ) {//Only execute in the main site
						self::update_license_option( array( 'key' => $license, 'user' => $user, 'site' => $site ) );
					}
				}
			}
		}, 10, 2 );

		add_action( 'fs_after_account_delete_' . self::$plugins_slug, function () {
			self::delete_license_option();
		} );

		add_action( 'fs_after_uninstall_' . self::$plugins_slug, function () {
			self::delete_license_option();
		} );

		add_action( 'fs_is_submenu_visible_' . self::$plugins_slug, function ( $is_visible, $menu_id ) {
			if ( defined( 'BLOG_ID_CURRENT_SITE' ) && get_current_blog_id() != BLOG_ID_CURRENT_SITE ) {
				$is_visible = false;
			}

			return $is_visible;
		}, 10, 2 );
	}

	public static function start_freemius() {
		global $formidable2rdb_fs;

		if ( ! isset( $formidable2rdb_fs ) ) {
			require_once dirname( __FILE__ ) . '/freemius/start.php';

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
				'menu'             => array(
					'slug'       => 'formidable2rdb',
					'first-path' => 'admin.php?page=formidable2rdb',
					'support'    => false,
					'pricing'    => true
				),
				'trial'            => array(
					'days'               => 14,
					'is_require_payment' => true,
				),
			) );
		}

//		self::process_multi_site();

		return $formidable2rdb_fs;
	}
}