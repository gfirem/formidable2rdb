<tr class="frm_options_heading">
	<td colspan="2">
		<div class="menu-settings">
			<h3 class="frm_no_bg"><?php echo Formidable2RdbManager::t( "Field capabilities"); ?></h3>
		</div>
	</td>
</tr>
<tr>
	<td>
		<label for="field_options[field_capabilities_enabled_<?php echo $field['id'] ?>]"><?php echo Formidable2RdbManager::t("Lock field options"); ?></label>
	</td>
	<td>
		<input type="checkbox" <?= $is_capability_enabled ?> name="field_options[field_capabilities_enabled_<?php echo $field['id'] ?>]" id="field_options[field_capabilities_enabled_<?php echo $field['id'] ?>]" value="1"/>
	</td>
</tr>
<tr>
	<td>
		<label for="field_options[field_capabilities_message_<?php echo $field['id'] ?>]"><?php echo Formidable2RdbManager::t( "Lock Message" ); ?></label>
	</td>
	<td>
		<input class="dyn_default_value frm_long_input" type="text" name="field_options[field_capabilities_message_<?php echo $field['id'] ?>]" id="field_options[field_capabilities_message_<?php echo $field['id'] ?>]" value="<?php echo $field['field_capabilities_message'] ?>"/>
	</td>
</tr>