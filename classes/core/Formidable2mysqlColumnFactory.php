<?php

class Formidable2mysqlColumnFactory {
	
	/**
	 * Get the MySql Column from JSON
	 *
	 * @param bool $json
	 * @param bool $force_all
	 *
	 * @return Formidable2mysqlColumn[]|bool|Formidable2mysqlColumn
	 */
	public static function import_json( $json = false, $force_all = false ) {
		if ( $json ) {
			$data = json_decode( stripslashes( $json ), true );
			if ( is_array( $data ) ) {
				$result = array();
				foreach ( $data as $key => $value ) {
					if ( ( ! empty( $value["Enabled"] ) && $value["Enabled"] == "1" ) || $force_all ) {
						$result[ $value["Id"] ] = new Formidable2mysqlColumn( $value["Field"], $value["Type"], $value["Length"], $value["Precision"], $value["Null"], "", $value["Default"], "", $value["Enabled"], $value["Id"], $force_all );
					}
				}
				
				return $result;
			} else {
				$r = null;
				if ( ( ! empty( $value["Enabled"] ) && $value["Enabled"] == "1" ) || $force_all ) {
					return new Formidable2mysqlColumn( $data["Field"], $data["Type"], $data["Length"], $data["Precision"], $data["Null"], "", $data["Default"], "", $data["Enabled"], $value["Id"], $force_all );
				}
			}
		}
		
		return false;
	}
}