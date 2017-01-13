<?php

interface Formidable2RdbInterface {

	public function create_table( $table_name, $columns );

	public function drop_table( $table_name );

	public function rename_table( $table_name, $new_table_name );

	public function add_column( $table_name, $columns );

	public function change_column( $table_name, $columns );

	public function get_columns( $table_name );

	public function drop_columns( $table_name, $columns );

	public function exist_table( $table_name );
	
	public function exist_column( $table_name, $column_name );
}