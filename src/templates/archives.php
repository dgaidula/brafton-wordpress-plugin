<?php
	// Initialize Settings
    require_once( sprintf(realpath(dirname(__FILE__) . '/..') .'/brafton_options.php'));
    $brafton_options = Brafton_options::get_instance(); 
 ?>

<div class="wrap">
    <div class="brafton-options">
    <h2> <?php echo $brafton_options->brafton_get_product(); ?>  Manual Archival Upload</h2>
    <p>If you wish to update existing content, enable overwrite</p>
    <form method="post" action="" enctype="multipart/form-data">
        <?php settings_fields( 'brafton_archives' ); ?>
        <?php @do_settings_fields('Brafton_Archives_group'); ?>
  
        <?php do_settings_sections('Brafton_Archives'); ?>

        <?php do_settings_sections( $_GET['page'] ); ?>

         <!-- <p class="submit"><input name="Submit" type="submit" class="button-primary" value="<?php echo  __( "Save Changes" )?>" /></p>'
 -->
        <?php   @submit_button(); ?>

            <?php
        if( $brafton_options->brafton_has_api_key() )
            echo '<div class="footer">Thank you for Partnering with ' . $brafton_options->link_to_product() .' </div>';
        ?>    
    </form>

    </div><!--- .brafton-options -->
</div><!-- .wrap -->
 