<h3 class="frm_first_h3"><?php echo Formidable2RdbManager::t( "Formidable2Rdb" ) ?></h3>
<table class="form-table">
	<tr>
		<td width="150px"><?php echo Formidable2RdbManager::t( "Version: " ) ?></td>
		<td>
			<span><?php echo Formidable2RdbManager::getVersion() ?></span>
		</td>
	</tr>
	<tr class="form-field" valign="top">
		<td width="150px">
			<label for="<?php echo Formidable2RdbManager::getShort() ?>_key"><?php echo Formidable2RdbManager::t( "Order key: " ) ?></label>
			<span class="frm_help frm_icon_font frm_tooltip_icon" title="" data-original-title="<?php echo Formidable2RdbManager::t( "Order key send to you with order confirmation, to get updates." ) ?>"></span>
		</td>
		<td><input type="text" name="<?php echo Formidable2RdbManager::getShort() ?>_key" id="<?php echo Formidable2RdbManager::getShort() ?>_key" value="<?php echo $key ?>"/></td>
	</tr>
	<tr class="form-field" valign="top">
		<td width="150px"><?php echo Formidable2RdbManager::t( "Key status: " ) ?></label></td>
		<td><?php echo $gManager->getStatus() ?></td>
	</tr>
</table>

<h3><?php echo Formidable2RdbManager::t( "Role " ) ?></h3>
<table class="form-table">
	<tr>
		<td width="150px"><label for="<?php echo Formidable2RdbManager::getShort() ?>_enabled"><?php echo Formidable2RdbManager::t( "Enabled: " ) ?></label></td>
		<td><input <?php echo "$enabled_string"; ?> type="checkbox" name="<?php echo Formidable2RdbManager::getShort() ?>_enabled" id="<?php echo Formidable2RdbManager::getShort() ?>_enabled" value="1"/></td>
	</tr>
	<tr>
		<td width="150px"><label for="<?php echo Formidable2RdbManager::getShort() ?>_role"><?php echo Formidable2RdbManager::t( "Approve roles: " ) ?></label></td>
		<td>
			<select multiple name="<?php echo Formidable2RdbManager::getShort() ?>_role[]" id="<?php echo Formidable2RdbManager::getShort() ?>_role">
				<?php
				$editable_roles = array_reverse( get_editable_roles() );
				foreach ( $editable_roles as $role_id => $details ) {
					$name = translate_user_role( $details['name'] );
					if ( in_array( $role_id, $role ) ) {
						echo "\n\t<option selected='selected' value='" . esc_attr( $role_id ) . "'>$name</option>";
					} else {
						echo "\n\t<option value='" . esc_attr( $role_id ) . "'>$name</option>";
					}
				}
				?>
			</select>
		</td>
	</tr>
</table>