<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbTrackTables {
	public function __construct( $hooks = true ) {
		if ( $hooks == true ) {
			if ( is_network_admin() ) {
				add_action( 'network_admin_menu', array( $this, 'network_menu' ) );
			}
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			
			//Track table options
			add_action( "formidable2rdb_after_add_table", array( $this, "add_track_table" ), 10, 4 );
			add_action( "formidable2rdb_after_drop_table", array( $this, "delete_track_table" ), 10, 4 );
			
			add_action( "wp_insert_post", array( $this, "on_post_inserted" ), 10, 3 );
		}
	}
	
	/**
	 * Update the action id into the track table. That is because the first action created pass 1 as action id
	 *
	 * @param $post_ID
	 * @param $post
	 * @param $update
	 */
	public function on_post_inserted( $post_ID, $post, $update ) {
		$t = $post;
		
		if ( $post->post_excerpt == Formidable2RdbManager::getSlug() && $post->post_type == "frm_form_actions" ) {
			$post_content = FrmAppHelper::maybe_json_decode( $post->post_content );
			if ( ! empty( $post_content["f2r_table_name"] ) ) {
				$data = self::get_track_table_by_name( $post_content["f2r_table_name"] );
				if ( ! empty( $data ) ) {
					$data["action_id"] = $post_ID;
					$r                 = self::update_track_table( $post_content["f2r_table_name"], $data );
				}
			}
		}
	}
	
	/**
	 * Network menu
	 */
	public function network_menu() {
		add_submenu_page( "settings.php", Formidable2RdbManager::t( "Formidable2Rdb" ), Formidable2RdbManager::t( "Formidable2Rdb" ), 'manage_network', Formidable2RdbManager::getSlug(), array( $this, 'menu_manage' ) );
	}
	
	/**
	 * Site menu
	 */
	public function admin_menu() {
		add_submenu_page( 'formidable', Formidable2RdbManager::t( "Formidable2Rdb" ), Formidable2RdbManager::t( "Formidable2Rdb" ), 'manage_options', Formidable2RdbManager::getSlug(), array( $this, 'menu_manage' ) );
	}
	
	/**
	 * Menu view implementation
	 */
	public function menu_manage() {
		$site_id = ( is_network_admin() ) ? false : get_current_blog_id();
		$tables  = self::get_tables( $site_id );
		$aa      = ( is_network_admin() ) ? "SI " : "NO ";
		echo "Es network " . $aa;
		echo "<pre>" . json_encode( $tables ) . "</pre>";
		include( F2M_VIEW_PATH . '/admin.php' );
	}
	
	/**
	 * Get table list for site or all sites
	 *
	 * @param bool $site_id
	 *
	 * @return array
	 */
	public static function get_tables( $site_id = false ) {
		$result = array();
		
		if ( is_multisite() ) {
			if ( ! $site_id ) {
				$sites = get_sites();
				foreach ( $sites as $site ) {
					$current_tables = self::get_table_from_option( $site->blog_id );
					$result         = array_merge( $result, $current_tables );
				}
			} else {
				$result = self::get_table_from_option( $site_id );
			}
		} else {
			$result = self::get_table_from_option();
		}
		
		return $result;
	}
	
	/**
	 * Get table from option
	 *
	 * @param bool $blog_id The blog id or empty to get for the current
	 *
	 * @return array
	 */
	private static function get_table_from_option( $blog_id = false ) {
		$result = array();
		if ( $blog_id != false ) {
			$current_tables = get_blog_option( $blog_id, Formidable2RdbManager::getSlug() . '_tables' );
			if ( ! empty( $current_tables ) ) {
				$result = maybe_unserialize( $current_tables );
			}
		} else {
			$current_tables = get_option( Formidable2RdbManager::getSlug() . '_tables' );
			if ( ! empty( $current_tables ) ) {
				$result = maybe_unserialize( $current_tables );
			}
		}
		
		return $result;
	}
	
	/**
	 * Add a table to tracked options
	 *
	 * @param $full_table_name
	 * @param $table_name
	 * @param $site_id
	 * @param $action_id
	 */
	public function add_track_table( $full_table_name, $table_name, $site_id, $action_id ) {
		
		Formidable2RdbLog::log( array(
			'action'         => "F2R_Management",
			'object_type'    => Formidable2RdbManager::getShort(),
			'object_subtype' => "added_table",
			'object_name'    => "Added the table " . $full_table_name,
		) );
		
		$current_table = array(
			"table"      => $table_name,
			"full_table" => $full_table_name,
			"site_id"    => $site_id,
			"action_id"  => $action_id
		);
		
		$tables = get_option( Formidable2RdbManager::getSlug() . '_tables' );
		
		if ( ! empty( $tables ) ) {
			$tables = maybe_unserialize( $tables );
		} else {
			$tables = array();
		}
		
		if ( is_multisite() && $site_id != false ) {
			$tables = array_merge( $tables, array( $full_table_name => $current_table ) );
		} else {
			$tables = array_merge( $tables, array( $full_table_name => $current_table ) );
		}
		if ( ! empty( $tables ) ) {
			$r = update_option( Formidable2RdbManager::getSlug() . '_tables', maybe_serialize( $tables ) );
		}
	}
	
	/**
	 * Get tracked table from options. If not exist return false
	 *
	 * @param $table_name
	 *
	 * @return bool|array
	 */
	public static function get_track_table_by_name( $table_name ) {
		$tables = get_option( Formidable2RdbManager::getSlug() . '_tables' );
		
		if ( ! empty( $tables ) ) {
			$tables = maybe_unserialize( $tables );
			
			foreach ( $tables as $table_key => $table_val ) {
				if ( $table_name == $table_val["table"] ) {
					return $table_val;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Update tracked table with the provided data
	 *
	 * @param $table_name
	 * @param $data
	 *
	 * @return bool
	 */
	public static function update_track_table( $table_name, $data ) {
		$tables = get_option( Formidable2RdbManager::getSlug() . '_tables' );
		
		if ( ! empty( $tables ) ) {
			$tables = maybe_unserialize( $tables );
			
			foreach ( $tables as $table_key => $table_val ) {
				if ( $table_name == $table_val["table"] ) {
					$tables[ $table_key ] = $data;
					$r                    = update_option( Formidable2RdbManager::getSlug() . '_tables', maybe_serialize( $tables ) );
					
					return $r;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Delete track table when is delete a table from data base
	 *
	 * @param $full_table_name
	 * @param $table_name
	 * @param $site_id
	 * @param $action_id
	 */
	public function delete_track_table( $full_table_name, $table_name, $site_id, $action_id ) {
		Formidable2RdbLog::log( array(
			'action'         => "F2R_Management",
			'object_type'    => Formidable2RdbManager::getShort(),
			'object_subtype' => "drop_table",
			'object_name'    => "Dropped the table " . $full_table_name,
		) );
		
		$tables = get_option( Formidable2RdbManager::getSlug() . '_tables' );
		
		if ( ! empty( $tables ) ) {
			$tables = maybe_unserialize( $tables );
			foreach ( $tables as $table_key => $table_val ) {
				if ( $full_table_name == $table_val["full_table"] ) {
					unset( $tables[ $table_key ] );
				}
			}
			
			$r = update_option( Formidable2RdbManager::getSlug() . '_tables', maybe_serialize( $tables ) );
		}
	}
}