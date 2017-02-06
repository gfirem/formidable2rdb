<input type="hidden" value="<?php echo esc_attr( $form_action->post_content['f2r_mapped_field'] ); ?>" name="<?php echo $action_control->get_field_name( 'f2r_mapped_field' ) ?>">
<input type="hidden" value="<?php echo esc_attr( $form_action->post_content['f2r_old_table_name'] ); ?>" name="<?php echo $action_control->get_field_name( 'f2r_old_table_name' ) ?>">
<input type="hidden" value="<?php echo esc_attr( $form_action->post_content['f2r_old_mapped_field'] ); ?>" name="<?php echo $action_control->get_field_name( 'f2r_old_mapped_field' ) ?>">
<input type="hidden" value="<?php echo esc_attr( $form_action->post_content['f2r_old_field'] ); ?>" name="<?php echo $action_control->get_field_name( 'f2r_old_field' ) ?>">
<h3 id="f2r_section"><?php echo Formidable2RdbManager::t( ' Table configuration ' ) ?></h3>
<span><?php echo Formidable2RdbManager::t( "Changes are applied when the form is updated." ); ?></span>
<hr/>
<table class="form-table frm-no-margin" _>
    <tbody id="f2r-table-body">
    <tr>
        <th>
            <label for="<?php echo $action_control->get_field_name( 'f2r_table_name' ) ?>">
                <b><?php echo Formidable2RdbManager::t( ' Table Name: ' ); ?></b>
                <span class="frm_help frm_icon_font frm_tooltip_icon" title="" data-original-title="<?php echo Formidable2RdbManager::t( 'Create a name for your table here. When you update the form, it will be created in the DB for you and you can change the name later.' ); ?>"></span>
            </label>
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
            <th>
                <b><?php echo Formidable2RdbManager::t( ' Field Name: ' ); ?></b>
                <span class="frm_help frm_icon_font frm_tooltip_icon" title="" data-original-title="<?php echo Formidable2RdbManager::t( 'The field name used in the form. It includes the id and the type of the field for reference.' ); ?>"></span>
            </th>
            <th>
                <b><?php echo Formidable2RdbManager::t( ' Enabled: ' ); ?></b>
                <span class="frm_help frm_icon_font frm_tooltip_icon" title="" data-original-title="<?php echo Formidable2RdbManager::t( 'When checked, this field will be mapped to the column in the DB table. If unchecked in later edits, the column will be dropped and the data will be lost.' ); ?>"></span>
            </th>
            <th>
                <b><?php echo Formidable2RdbManager::t( ' Column Name: ' ); ?></b>
                <span class="frm_help frm_icon_font frm_tooltip_icon" title="" data-original-title="<?php echo Formidable2RdbManager::t( 'The name for the column in the DB. It accepts only letters, numbers and underscores.' ); ?>"></span>
            </th>
            <th>
                <b><?php echo Formidable2RdbManager::t( ' Column Default: ' ); ?></b>
                <span class="frm_help frm_icon_font frm_tooltip_icon" title="" data-original-title="<?php echo Formidable2RdbManager::t( 'The default value to be saved to the DB table when this form is submitted.' ); ?>"></span>
            </th>
            <th>
                <b><?php echo Formidable2RdbManager::t( ' Column Type: ' ); ?></b>
                <span class="frm_help frm_icon_font frm_tooltip_icon" title="" data-original-title="<?php echo Formidable2RdbManager::t( 'The format of the data stored in this column. Base this on what type of field is being used to create the data.' ); ?>"></span>
            </th>
            <th>
                <b><?php echo Formidable2RdbManager::t( ' Length/Precision: ' ); ?></b>
                <span class="frm_help frm_icon_font frm_tooltip_icon" title="" data-original-title="<?php echo Formidable2RdbManager::t( 'The length of the field in characters and when applicable, the precision of a numeric field in decimal places.' ); ?>"></span>
            </th>
            <th>
                <b><?php echo Formidable2RdbManager::t( ' Null: ' ); ?></b>
                <span class="frm_help frm_icon_font frm_tooltip_icon" title="" data-original-title="<?php echo Formidable2RdbManager::t( 'Whether the column will accept null values.' ); ?>"></span>
            </th>
        </tr>
        </thead>
        <tbody id="f2r-table-body">
		<?php
		$source_values = $form_action->post_content['f2r_mapped_field'];
		if ( ! empty( $source_values ) ) {
			$source_values = Formidable2mysqlColumnFactory::import_json( $source_values, true );
		}
		foreach ( $fields[ $main_form ] as $id => $f ):
			if ( in_array( $f["type"], Formidable2RdbGeneric::exclude_fields() ) ) {
				continue;
			}
			
			$repeatable_content = false;
			if ( $f["type"] == 'divider' ) {
				if ( array_key_exists( intval( $f['id'] ), $fields ) ) {
					$repeatable_content = true;
				} else {
					continue;
				}
			}
			
			$column_field_id   = $f["id"];
			$column_name       = $f["field_key"];
			$column_default    = "";
			$column_type       = "none";
			$column_enabled    = "";
			$column_length     = 5;
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
			$string_type = ( $f["type"] == 'divider' ) ? 'Repeatable' : $f["type"];
			?>
            <tr class="f2r_row">
                <td class="f2r_table_field">
                    <b><?php echo $f["name"] . "<br/> (" . $f["id"] . ")(" . $string_type . ")" ?></b>
                    <input type="hidden" action_id="<?php echo $this->number ?>" class="f2r f2r_map_id f2r_map_option_<?php echo $this->number ?>" name="f2r_column_field_id_<?php echo $f["field_key"] ?>" id="f2r_column_field_id_<?php echo $f["field_key"] ?>" value="<?php echo "$column_field_id"; ?>">
                </td>
                <td class="f2r_table_enabled">
                    <input <?php echo "$column_enabled"; ?> type="checkbox" action_id="<?php echo $this->number ?>" field_id="<?php echo $f["id"] ?>" class="f2r f2r_map_enabled f2r_map_option_<?php echo $this->number ?>" name="f2r_map_enabled_<?php echo $f["field_key"] ?>" id="f2r_map_enabled_<?php echo $f["field_key"] ?>" value="1"/>
					<?php echo ( $repeatable_content ) ? '<a field_id="' . $f["id"] . '" id="f2r_show_repeatable_fields" class="f2r_show_repeatable_fields">' . Formidable2RdbManager::t( 'Inside Fields' ) . '</a>' : ''; ?>
                    <span class="frm_help_column_enabled frm_help frm_icon_font frm_tooltip_icon" id="frm_help_column_enabled_<?php echo $f["id"] ?>" title="" data-original-title="<?php echo Formidable2RdbManager::t( 'When you save the form all the data in the column will be lost.' ); ?>"></span>
                </td>
                <td class="f2r_table_standard">
                    <input autocomplete="off" type="text" action_id="<?php echo $this->number ?>" class="f2r f2r_map_name f2r_map_option_<?php echo $this->number ?>" name="f2r_column_name_<?php echo $f["field_key"] ?>" id="f2r_column_name_<?php echo $f["field_key"] ?>" value="<?php echo "$column_name"; ?>">
                </td>
                <td>
                    <input autocomplete="off" type="text" action_id="<?php echo $this->number ?>" class="f2r f2r_map_default f2r_map_option_<?php echo $this->number ?>" name="f2r_column_default_<?php echo $f["field_key"] ?>" id="f2r_column_default_<?php echo $f["field_key"] ?>" value="<?php echo "$column_default"; ?>">
                </td>
                <td>
                    <select field_id="<?php echo $f["field_key"] ?>" field_type="<?php echo $f["type"]; ?>" action_id="<?php echo $this->number ?>" class="f2r f2r_map_type f2r_map_option_<?php echo $this->number ?>" name="f2r_column_type_<?php echo $f["field_key"] ?>" id="f2r_column_type_<?php echo $f["field_key"] ?>">
						<?php
						/**
						 * @var integer $k
						 * @var Formidable2RdbColumnType $i
						 */
						foreach ( Formidable2RdbGeneric::get_granted_column_type_for_field( $f["type"] ) as $k => $i ) {
							$selected = ( $i->getType() == $column_type ) ? "selected='selected'" : '';
							echo '<option ' . $selected . ' value="' . $i->getType() . '">' . $i->getName() . '</option>';
						}
						?>
                    </select>
                </td>
                <td class="f2r_length_container">
                    <div class="length_precision_container">
                        <input autocomplete="off" field_id="<?php echo $f["field_key"] ?>" type="text" action_id="<?php echo $this->number ?>" class="f2r f2r_map_length f2r_map_option_<?php echo $this->number ?>" name="f2r_column_length_<?php echo $f["field_key"] ?>" id="f2r_column_length_<?php echo $f["field_key"] ?>" value="<?php echo "$column_length"; ?>">
                        <input autocomplete="off" type="text" action_id="<?php echo $this->number ?>" class="f2r f2r_map_precision f2r_map_option_<?php echo $this->number ?>" name="f2r_column_precision_<?php echo $f["field_key"] ?>" id="f2r_column_precision_<?php echo $f["field_key"] ?>" value="<?php echo "$column_precision"; ?>">
                    </div>
                </td>
                <td>
                    <select action_id="<?php echo $this->number ?>" class="f2r f2r_map_not_null f2r_map_option_<?php echo $this->number ?>" name="f2r_column_not_null_<?php echo $f["field_key"] ?>" id="f2r_column_not_null_<?php echo $f["field_key"] ?>">
                        <option <?php echo "$is_null"; ?> value="YES"><?php echo Formidable2RdbManager::t( ' YES ' ); ?></option>
                        <option <?php echo "$is_not_null_value"; ?> value="NO"><?php echo Formidable2RdbManager::t( ' NO ' ); ?></option>
                    </select>
                </td>
            </tr>
			<?php if ( ! empty( $repeatable_content ) ) : ?>
            <tr id="f2r_hidden_repeatable_section_<?php echo $f['id'] ?>" class="f2r_hidden_repeatable_section">
                <td></td>
                <td colspan="6">
                    <p><?php echo Formidable2RdbManager::t( ' The value of the fields inside this repeatable section will store as json inside this column.' ); ?></p>
                    <b><?php echo Formidable2RdbManager::t( 'Fields:' ); ?></b><br/>
                    <ul>
						<?php
						foreach ( $fields[ $f['id'] ] as $rid => $rf ) {
							echo '<li>' . $rf["name"] . ' (' . $rf['id'] . ')(' . $rf['type'] . ')</li>';
						}
						?>
                    </ul>
                </td>
            </tr>
		<?php endif; ?>
		<?php endforeach; ?>
        <tr>
            <td colspan="7">
                <hr/>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<span><?php echo Formidable2RdbManager::t( "Select when to trigger the action  - when an entry is created, updated, deleted or imported." ); ?></span>
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

			if (!mapped_table_name.val()) {
				e.preventDefault();
				mapped_table_name.addClass("f2r_error");
				alert(formidable2rdb.table_name_required);
			} else {
				mapped_table_name.removeClass("f2r_error");
			}
		});
	});
</script>