<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbSettings {

	public static function route() {

		$action = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';
		$action = FrmAppHelper::get_param( $action );
		if ( $action == 'process-form' ) {
			return self::process_form();
		} else {
			return self::display_form();
		}
	}

	/**
	 * @internal var gManager GManager_1_0
	 */
	public static function display_form() {
		$gManager       = GManagerFactory::buildManager( 'Formidable2RdbManager', 'formidable2rdb', Formidable2RdbManager::getShort() );
		$key            = get_option( Formidable2RdbManager::getShort() . 'licence_key' );
		$enabled        = get_option( Formidable2RdbManager::getShort() . 'enabled' );
		$enabled_string = "";
		if ( ! empty( $enabled ) && $enabled == "1" ) {
			$enabled_string = "checked='checked'";
		}
		$role          = array();
		$role_internal = maybe_unserialize( get_option( Formidable2RdbManager::getShort() . 'role' ) );
		if ( ! empty( $role_internal ) && is_array( $role_internal ) ) {
			$role = $role_internal;
		}
		include( F2M_VIEW_PATH . '/settings.php' );
	}

	public static function process_form() {
		if ( isset( $_POST[ Formidable2RdbManager::getShort() . '_key' ] ) && ! empty( $_POST[ Formidable2RdbManager::getShort() . '_key' ] ) ) {
			$gManager = GManagerFactory::buildManager( 'Formidable2RdbManager', 'formidable2rdb', Formidable2RdbManager::getShort() );
			$gManager->activate( $_POST[ Formidable2RdbManager::getShort() . '_key' ] );
			update_option( Formidable2RdbManager::getShort() . 'licence_key', $_POST[ Formidable2RdbManager::getShort() . '_key' ] );
		} else {
			delete_option( Formidable2RdbManager::getShort() . 'licence_key' );
		}

		if ( isset( $_POST[ Formidable2RdbManager::getShort() . '_role' ] ) && ! empty( $_POST[ Formidable2RdbManager::getShort() . '_role' ] ) ) {
			update_option( Formidable2RdbManager::getShort() . 'role', maybe_serialize( $_POST[ Formidable2RdbManager::getShort() . '_role' ] ) );
		} else {
			delete_option( Formidable2RdbManager::getShort() . 'role' );
		}

		if ( isset( $_POST[ Formidable2RdbManager::getShort() . '_enabled' ] ) && ! empty( $_POST[ Formidable2RdbManager::getShort() . '_enabled' ] ) ) {
			update_option( Formidable2RdbManager::getShort() . 'enabled', maybe_serialize( $_POST[ Formidable2RdbManager::getShort() . '_enabled' ] ) );
		} else {
			delete_option( Formidable2RdbManager::getShort() . 'enabled' );
		}

		self::display_form();
	}
}