<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbTrackTables {
	public function __construct() {
		//Track table options
		add_action( "formidable2rdb_after_add_table", array( $this, "add_track_table" ), 10, 4 );
		add_action( "formidable2rdb_after_drop_table", array( $this, "delete_track_table" ), 10, 4 );
		add_action( "formidable2rdb_after_rename_table", array( $this, "rename_track_table" ), 10, 2 );
		
		add_action( "wp_insert_post", array( $this, "on_post_inserted" ), 10, 3 );
	}
	
	/**
	 * Update the action id into the track table. That is because the first action created pass 1 as action id
	 *
	 * @param $post_ID
	 * @param $post
	 * @param $update
	 */
	public function on_post_inserted( $post_ID, $post, $update ) {
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
		return self::get_tracked_table_by( "table", $table_name );
	}
	
	/**
	 * Get table data by provided key into the setting
	 *
	 * @param $source
	 * @param $data
	 *
	 * @return bool|array
	 */
	public static function get_tracked_table_by( $source, $data ) {
		$tables = get_option( Formidable2RdbManager::getSlug() . '_tables' );
		
		if ( ! empty( $tables ) ) {
			$tables = maybe_unserialize( $tables );
			
			foreach ( $tables as $table_key => $table_val ) {
				if ( $data == $table_val[ $source ] ) {
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
		
		self::delete_table( $full_table_name, "full_table" );
	}
	
	/**
	 * Remove table from the options using the $key param to find into it
	 *
	 * @param $table_name
	 * @param $key
	 */
	public static function delete_table( $table_name, $key ) {
		$tables = get_option( Formidable2RdbManager::getSlug() . '_tables' );
		
		if ( ! empty( $tables ) ) {
			$tables = maybe_unserialize( $tables );
			foreach ( $tables as $table_key => $table_val ) {
				if ( $table_name == $table_val[ $key ] ) {
					unset( $tables[ $table_key ] );
				}
			}
			
			$r = update_option( Formidable2RdbManager::getSlug() . '_tables', maybe_serialize( $tables ) );
		}
	}
	
	/**
	 * Update options when table is renamed
	 *
	 * @param $old_name
	 * @param $new_name
	 */
	public function rename_track_table( $old_name, $new_name ) {
		$table               = self::get_track_table_by_name( $old_name );
		$table["table"]      = $new_name;
		$table["full_table"] = Formidable2RdbGeneric::get_table_name( $new_name );
		self::update_track_table( $old_name, $table );
	}
}