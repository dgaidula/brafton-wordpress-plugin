<?php
	// Initialize Settings
    require_once( sprintf(realpath(dirname(__FILE__) . '/..') .'/brafton_options.php'));
    $brafton_options = Brafton_options::get_instance(); 
 ?>
<div class="wrap">
    <div class="brafton-options">
    <h2> <?php echo $brafton_options->brafton_get_product(); ?>  Manual Archival Upload</h2>
    <form method="post" action="" enctype="multipart/form-data">

        <h2 class="nav-tab-wrapper">
            <a class="nav-tab" href="#archives">Import</a>
            <a class="nav-tab" href="#history">History</a>
        </h2>
            <p>If you wish to update existing content, enable overwrite</p>
             <?php  settings_fields( 'brafton_archives' ); ?>
            <?php @$brafton_options->brafton_do_settings_fields( $_GET['page'], 'Brafton_Archives_group', true); ?>
            <?php  do_settings_sections('Brafton_Archives'); ?>
            <?php   @submit_button(); ?>

        </div>
        <div class="tab-pane">
            <p>Here's list of article id's for all successfully imported content.</p>

            <form action="">
</form>
            <?php 


            $video_post_type = $brafton_options->options['brafton_video_post_type'];
            $article_post_type = $brafton_options->options['brafton_article_post_type'];
            $post_type = array();
            if( isset( $video_post_type ) && $video_post_type )
                $post_type[] = $video_post_type;
            if( isset( $article_post_type )  && $article_post_type )
                $post_type[] = $article_post_type;
           
            $post_type[] = 'post';
            $args = array( 
                        'post_type' => $post_type,
                        'meta_key' => 'brafton_id', 
                        'post_status' =>  array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit' ),
                        'posts_per_page' => -1 
                    ); 
            $query = new WP_Query( $args ); 
            $content = array( );
            if( $query->have_posts() ) : while( $query->have_posts() ) : $query->the_post(); 
            $brafton_id =  get_post_meta( get_the_ID(), 'brafton_id', true );
                if( $brafton_id != "" )
                    $content[] = $brafton_id;
            endwhile;
            endif;
            wp_reset_postdata();
            if ( ! empty( $content ) ) : 

                $history = '<input type="text" value="';
                foreach( $content as $c )
                    $history .= $c . ' ';
                

                $history .= '">';
                echo $history;
            endif;
            ?>
        </div>
            <?php
        if( $brafton_options->brafton_has_api_key() )
            echo '<div class="footer">Thank you for Partnering with ' . $brafton_options->link_to_product() .' </div>';
        ?>    
    </form>
    </div><!--- .brafton-options -->
</div><!-- .wrap -->
 