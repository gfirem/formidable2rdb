<?php
/** @var Formidable2mysqlColumn $map */
/** @var Formidable2RdbColumnType $map_ui */
?>
<tr class="frm_options_heading">
    <td colspan="2">
        <div class="menu-settings">
            <h3 class="frm_no_bg"><?php echo Formidable2RdbManager::t( "Formidable2Rdb Map Data" ) ?></h3>
        </div>
    </td>
</tr>
<tr>
    <td colspan="2">
        <p>
            <b><?php echo Formidable2RdbManager::t( "Name:" ) . " " ?></b><?php echo ( empty( $map->Field ) ) ? '-' : $map->Field; ?>
			<?php if ( $map_ui->isNeedDefault() ): ?><b><?php echo Formidable2RdbManager::t( "Default:" ) . " " ?></b><?php echo ( empty( $map->Default ) ) ? '-' : $map->Default; endif; ?>
            <b><?php echo Formidable2RdbManager::t( "Type:" ) . " " ?></b><?php echo ( empty( $map->Type ) ) ? '-' : $map->Type; ?>
	        <?php if ( $map_ui->isNeedLength() ): ?><b><?php echo Formidable2RdbManager::t( "Length:" ) . " " ?></b><?php echo ( empty( $map->Length ) ) ? '-' : $map->Length; endif;?>
	        <?php if ( $map_ui->isNeedPrecision() ): ?><b><?php echo Formidable2RdbManager::t( "Precision:" ) . " " ?></b><?php echo ( empty( $map->Precision ) ) ? '-' : $map->Precision; endif;?>
            <b><?php echo Formidable2RdbManager::t( "Is Null:" ) . " " ?></b><?php echo ( empty( $map->Null ) ) ? '-' : $map->Null; ?>
        </p>
    </td>
</tr>