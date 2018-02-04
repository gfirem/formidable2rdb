
<h3><?php echo Formidable2RdbManager::t( "Server Credential" ) ?></h3>
<table class="form-table">
    <tr>
        <td width="150px">
            <label for="<?php echo "$_credential_target_id" ?>"><?php echo Formidable2RdbManager::t( "Use installation credential: " ) ?></label>
            <p><?php echo Formidable2RdbManager::t( " If you check this option, the system not take in count the next credential and disable the credential in the action." ) ?></p>
        </td>
        <td><input type="text" size="40" name="<?php echo "$_credential_target_id" ?>" id="<?php echo "$_credential_target_id" ?>" value="<?php echo "$_credential_target" ?>"/></td>
    </tr>
	<tr>
		<td width="150px"><label for="<?php echo Formidable2RdbManager::getShort() ?>_public_key"><?php echo Formidable2RdbManager::t( "Api key: " ) ?></label></td>
		<td><input type="text" size="40" name="<?php echo Formidable2RdbManager::getShort() ?>_public_key" id="<?php echo Formidable2RdbManager::getShort() ?>_public_key" value="<?php echo $public_key ?>"/></td>
	</tr>
	<tr class="form-field" valign="top">
		<td width="150px">
			<label for="<?php echo Formidable2RdbManager::getShort() ?>_private_key"><?php echo Formidable2RdbManager::t( "Secret key: " ) ?></label>
		</td>
		<td><input type="password" size="40" name="<?php echo Formidable2RdbManager::getShort() ?>_private_key" id="<?php echo Formidable2RdbManager::getShort() ?>_private_key" value="<?php echo $private_key ?>"/></td>
	</tr>
	<tr class="form-field" valign="top">
		<td width="150px">
			<label for="<?php echo Formidable2RdbManager::getShort() ?>_refresh_factor"><?php echo Formidable2RdbManager::t( "Status refresh time: " ) ?></label>
		</td>
		<td>
			<input type="number" size="5" name="<?php echo Formidable2RdbManager::getShort() ?>_refresh_factor" id="<?php echo Formidable2RdbManager::getShort() ?>_refresh_factor" value="<?php echo $refresh_factor ?>"/>
			<p><?php echo Formidable2RdbManager::t( "Time in minutes. The system use to monitor the time between calls to MailJet server to maintain an internal cache system " ) ?></p>
		</td>
	</tr>
</table>