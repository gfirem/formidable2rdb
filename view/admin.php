<div class="wrap">
    <h2><?php echo Formidable2RdbManager::t( "Formidable2Rdb Settings" ); ?></h2>

    <form method="post" action="options.php">
		<?php settings_fields( 'formidable2rdb' ); ?>
		<?php do_settings_sections( 'formidable2rdb' ); ?>
    </form>
    
</div>
