<input type="hidden" value="<?php echo esc_attr( $form_action->post_content['f2r_mapped_field'] ); ?>" name="<?php echo $action_control->get_field_name( 'f2r_mapped_field' ) ?>">
<input type="hidden" value="<?php echo esc_attr( $form_action->post_content['f2r_old_table_name'] ); ?>" name="<?php echo $action_control->get_field_name( 'f2r_old_table_name' ) ?>">
<input type="hidden" value="<?php echo esc_attr( $form_action->post_content['f2r_old_mapped_field'] ); ?>" name="<?php echo $action_control->get_field_name( 'f2r_old_mapped_field' ) ?>">
<input type="hidden" value="<?php echo esc_attr( $form_action->post_content['f2r_old_field'] ); ?>" name="<?php echo $action_control->get_field_name( 'f2r_old_field' ) ?>">
<h3 id="f2r_section"><?php echo Formidable2RdbManager::t( ' Table configuration ' ) ?></h3>
<hr/>
<table class="form-table frm-no-margin" _>
    <tbody id="f2r-table-body">
    <tr>
        <th>
            <label for="<?php echo $action_control->get_field_name( 'f2r_table_name' ) ?>"><b><?php echo Formidable2RdbManager::t( ' Table Name: ' ); ?></b></label>
        </th>
        <td>
            <input class="f2r_table_name" type="text" action_id="<?php echo $this->number ?>" value="<?php echo esc_attr( $form_action->post_content['f2r_table_name'] ); ?>" name="<?php echo $action_control->get_field_name( 'f2r_table_name' ) ?>" id="<?php echo $action_control->get_field_name( 'f2r_table_name' ) ?>">
            --> <?php echo Formidable2RdbManager::t( ' Result table name: ' ) . "<strong>" . $wpdb->prefix; ?><span id="table_name_result_<?php echo $this->number ?>"><?php echo esc_attr( $form_action->post_content['f2r_table_name'] ); ?></strong></span>
			<?php if ( ! empty( $table_structure_container_css ) ): ?>
                <button class="check_table" action_id="<?php echo $this->number ?>" type="button"><?php echo Formidable2RdbManager::t( ' Check if exist ' ) ?></button>
                <img class="f2r_loading" id="f2r_loading_<?php echo $this->number ?>" src="/wp-content/plugins/formidable/images/ajax_loader.gif"/>
			<?php endif; ?>
        </td>
    </tr>
    </tbody>
</table>
<div id="table_structure_<?php echo $this->number ?>" class="<?php echo "$table_structure_container_css"; ?>">
    <h3 id="f2r_section"><?php echo Formidable2RdbManager::t( ' Map field to Database column ' ) ?></h3>
    <hr/>
    <table class="form-table f2r_map_table frm-no-margin">
        <thead>
        <tr>
            <th><b><?php echo Formidable2RdbManager::t( ' Field Name: ' ); ?></th>
            <th><b><?php echo Formidable2RdbManager::t( ' Enabled: ' ); ?></th>
            <th><b><?php echo Formidable2RdbManager::t( ' Column Name: ' ); ?></th>
            <th><b><?php echo Formidable2RdbManager::t( ' Column Default: ' ); ?></th>
            <th><b><?php echo Formidable2RdbManager::t( ' Column Type: ' ); ?></th>
            <th><b><?php echo Formidable2RdbManager::t( ' Length/Precision: ' ); ?></th>
            <th><b><?php echo Formidable2RdbManager::t( ' Null: ' ); ?></th>
        </tr>
        </thead>
        <tbody id="f2r-table-body">
		<?php
		$source_values = $form_action->post_content['f2r_mapped_field'];
		if ( ! empty( $source_values ) ) {
			$source_values = Formidable2mysqlColumnFactory::import_json( $source_values, true );
		}
		foreach ( $fields as $id => $f ):
			
			if ( in_array( $f["type"], Formidable2RdbAdmin::exclude_fields() ) ) {
				continue;
			}
			
			$column_field_id   = $f["id"];
			$column_name       = $f["field_key"];
			$column_default    = "";
			$column_type       = "none";
			$column_enabled    = "";
			$column_length     = 100;
			$column_precision  = 0;
			$is_null           = "selected='selected'";
			$is_not_null_value = "";
			if ( ! empty( $source_values ) && array_key_exists( $f["id"], $source_values ) ) {
				if ( $source_values[ $f["id"] ]->Enabled ) {
					$column_enabled = "checked='checked'";
				}
				$column_name      = $source_values[ $f["id"] ]->Field;
				$column_default   = $source_values[ $f["id"] ]->Default;
				$column_type      = $source_values[ $f["id"] ]->Type;
				$column_length    = $source_values[ $f["id"] ]->Length;
				$column_precision = $source_values[ $f["id"] ]->Precision;
				
				if ( $source_values[ $f["id"] ]->Null == "NOT NULL" ) {
					$is_null           = "";
					$is_not_null_value = "selected='selected'";
				}
			}
			?>
            <tr class="f2r_row">
                <td class="f2r_table_field">
                    <b><?php echo $f["name"] . " (" . $f["id"] . ")(" . $f["type"] . ")" ?></b>
                    <input type="hidden" action_id="<?php echo $this->number ?>" class="f2r f2r_map_id f2r_map_option_<?php echo $this->number ?>" name="f2r_column_field_id_<?php echo $f["field_key"] ?>" id="f2r_column_field_id_<?php echo $f["field_key"] ?>" value="<?php echo "$column_field_id"; ?>">
                </td>
                <td class="f2r_table_enabled">
                    <input <?php echo "$column_enabled"; ?> type="checkbox" action_id="<?php echo $this->number ?>" class="f2r f2r_map_enabled f2r_map_option_<?php echo $this->number ?>" name="f2r_map_enabled_<?php echo $f["field_key"] ?>" id="f2r_map_enabled_<?php echo $f["field_key"] ?>" value="1"/>
                </td>
                <td class="f2r_table_standard">
                    <input type="text" action_id="<?php echo $this->number ?>" class="f2r f2r_map_name f2r_map_option_<?php echo $this->number ?>" name="f2r_column_name_<?php echo $f["field_key"] ?>" id="f2r_column_name_<?php echo $f["field_key"] ?>" value="<?php echo "$column_name"; ?>">
                </td>
                <td>
                    <input type="text" action_id="<?php echo $this->number ?>" class="f2r f2r_map_default f2r_map_option_<?php echo $this->number ?>" name="f2r_column_default_<?php echo $f["field_key"] ?>" id="f2r_column_default_<?php echo $f["field_key"] ?>" value="<?php echo "$column_default"; ?>">
                </td>
                <td>
                    <select field_type="<?php echo $f["type"]; ?>" action_id="<?php echo $this->number ?>" class="f2r f2r_map_type f2r_map_option_<?php echo $this->number ?>" name="f2r_column_type_<?php echo $f["field_key"] ?>" id="f2r_column_type_<?php echo $f["field_key"] ?>">
						<?php
						foreach ( Formidable2RdbAdmin::get_granted_column_type_for_field( $f["type"] ) as $k => $i ) {
							$selected = ( $k == $column_type ) ? "selected='selected'" : '';
							echo '<option ' . $selected . ' value="' . $k . '">' . $i . '</option>';
						}
						?>
                    </select>
                </td>
                <td class="f2r_length_container">
                    <div class="length_precision_container">
                        <input type="text" action_id="<?php echo $this->number ?>" class="f2r f2r_map_length f2r_map_option_<?php echo $this->number ?>" name="f2r_column_length_<?php echo $f["field_key"] ?>" id="f2r_column_length_<?php echo $f["field_key"] ?>" value="<?php echo "$column_length"; ?>">
                        <input type="text" action_id="<?php echo $this->number ?>" class="f2r f2r_map_precision f2r_map_option_<?php echo $this->number ?>" name="f2r_column_precision_<?php echo $f["field_key"] ?>" id="f2r_column_precision_<?php echo $f["field_key"] ?>" value="<?php echo "$column_precision"; ?>">
                    </div>
                </td>
                <td>
                    <select action_id="<?php echo $this->number ?>" class="f2r f2r_map_not_null f2r_map_option_<?php echo $this->number ?>" name="f2r_column_not_null_<?php echo $f["field_key"] ?>" id="f2r_column_not_null_<?php echo $f["field_key"] ?>">
                        <option <?php echo "$is_null"; ?> value="YES"><?php echo Formidable2RdbManager::t( ' YES ' ); ?></option>
                        <option <?php echo "$is_not_null_value"; ?> value="NO"><?php echo Formidable2RdbManager::t( ' NO ' ); ?></option>
                    </select>
                </td>
            </tr>
		<?php endforeach; ?>
        <tr>
            <td colspan="6">
                <hr/>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<script>
    jQuery(document).ready(function ($) {
        var action_id = <?php echo $this->number; ?>;
        $(".frm_form_settings").on("submit", function (e) {
            var $form = $(this);
            var mapped_field = $("[name='frm_formidable2rdb_action[" + action_id + "][post_content][f2r_mapped_field]']");
            var mapped_old_field = $("[name='frm_formidable2rdb_action[" + action_id + "][post_content][f2r_old_mapped_field]']");
            var mapped_table_name = $("[name='frm_formidable2rdb_action[" + action_id + "][post_content][f2r_table_name]']");
            var mapped_old_table_name = $("[name='frm_formidable2rdb_action[" + action_id + "][post_content][f2r_old_table_name]']");

            var action_fields = $('table.f2r_map_table tr.f2r_row').map(function (i, v) {
                var $id = $('.f2r_map_id', this);
                var $enabled = $('.f2r_map_enabled', this);
                var $name = $('.f2r_map_name', this);
                var $default = $('.f2r_map_default', this);
                var $type = $('.f2r_map_type', this);
                var $length = $('.f2r_map_length ', this);
                var $precision = $('.f2r_map_precision', this);
                var $null = $('.f2r_map_not_null', this);
                return {
                    'Id': $id.val(),
                    'Enabled': $enabled.is(":checked"),
                    'Field': $name.val(),
                    'Default': $default.val(),
                    'Type': $type.val(),
                    'Length': $length.val(),
                    'Precision': $precision.val(),
                    'Null': $null.val()
                }
            }).get();

            var json = JSON.stringify(action_fields);
            mapped_field.val(json);
            if (!mapped_old_field.val()) {
                mapped_old_field.val(json);
            }
            if (!mapped_old_table_name.val()) {
                mapped_old_table_name.val(mapped_table_name.val());
            }
        });
    });
</script>