<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbDataTable extends WP_List_Table_G {

	private $table_name;
	private $columns;

	public function __construct( $table_name, $column ) {
		if ( empty( $table_name ) || empty( $column ) ) {
			throw new Exception( "Formidable2RdbDataTable need a valid parameters into the constructor." );
		}

		$this->table_name = $table_name;
		$this->columns    = $column;

		parent::__construct( array(
			'singular' => Formidable2RdbManager::t( "Table" ),
			'plural'   => Formidable2RdbManager::t( "Tables" ),
		) );
	}

	/**
	 * this is a default column renderer
	 *
	 * @param $item - row (key, value array)
	 * @param $column_name - string (key)
	 *
	 * @return HTML
	 */
	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * This method return columns to display in table
	 * you can skip columns that you do not want to show
	 * like content, or description
	 *
	 * @return array
	 */
	function get_columns() {
		return $this->columns;
	}

	/**
	 * This method return columns that may be used to sort table
	 * all strings in array - is column names
	 * notice that true on name column means that its default sort
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'created_at' => array( 'created_at', true )
		);

		return $sortable_columns;
	}

	/**
	 * This is the most important method
	 *
	 * It will get rows from database and prepare them to be showed in table
	 */
	function prepare_items() {
		try {
			global $wpdb;
			$table_name = $this->table_name;

			$per_page = 5; // constant, how much records will be shown per page

			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();

			// here we configure table headers, defined in our methods
			$this->_column_headers = array( $columns, $hidden, $sortable );

			// will be used in pagination settings
			$total_items = $wpdb->get_var( "SELECT COUNT(rdb_id) FROM $table_name" );

			// prepare query params, as usual current page, order by and order direction
			$paged   = isset( $_REQUEST['paged'] ) ? max( 0, intval( $_REQUEST['paged'] ) - 1 ) : 0;
			$orderby = ( isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array_keys( $this->get_sortable_columns() ) ) ) ? $_REQUEST['orderby'] : 'created_at';
			$order   = ( isset( $_REQUEST['order'] ) && in_array( $_REQUEST['order'], array( 'asc', 'desc' ) ) ) ? $_REQUEST['order'] : 'asc';

			// [REQUIRED] define $items array
			// notice that last argument is ARRAY_A, so we will retrieve array
			$this->items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", array( $per_page, $paged ) ), ARRAY_A );

			// [REQUIRED] configure pagination
			$this->set_pagination_args( array(
				'total_items' => $total_items, // total items defined above
				'per_page'    => $per_page, // per page constant defined at top of method
				'total_pages' => ceil( $total_items / $per_page ) // calculate pages count
			) );
		} catch ( Exception $ex ) {
			Formidable2RdbManager::handle_exception( $ex->getMessage() );
		}
	}

}