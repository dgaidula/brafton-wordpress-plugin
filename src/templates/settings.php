<?php
    // Initialize Settings
    require_once( sprintf(realpath(dirname(__FILE__) . '/..') .'/brafton_options.php'));
    $brafton_options = Brafton_options::get_instance(); 
 ?>
<div class="wrap">
    <div class="brafton-options">
    <h2> <?php echo $brafton_options->brafton_get_product(); ?>  Importer</h2>
    <?php 
    if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true )
            echo '<div class="updated fade"><p>' . __( sprintf('%s options updated.', $brafton_options->brafton_get_product() ) ) . '</p></div>'; ?>
    <form method="post" action="options.php" enctype="multipart/form-data"> 
        <?php @settings_fields('WP_Brafton_Article_Importer_group'); ?>
       <!--  <div class="ui-tabs"> --> 
            <h2 class="nav-tab-wrapper">
                <?php 
                
               $sections = $brafton_options->get_sections(); 
                foreach ( $sections as $section_slug => $section ) 
                        echo '<a class="nav-tab" href="#' . $section_slug . '">' . $section . '</a>'; 
                
            ?>
            </h2><!-- end .ul-tabs-nav -->
                <?php $brafton_options->brafton_do_settings_sections( $_GET['page'] ); ?>
        </div><!-- end .ul-tabs-->
        <?php   @submit_button(); ?>
        <?php
        if( $brafton_options->brafton_has_api_key() )
            echo '<div class="footer">Thank you for Partnering with ' . $brafton_options->link_to_product() .' </div>';
        ?>
    </form>
    </div><!--- .brafton-options -->
</div><!-- .wrap -->