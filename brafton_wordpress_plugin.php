<?php
/*
Plugin Name: Brafton WordPress Plugin
Plugin URI: http://www.brafton.com/support/wordpress
version: 1.4.0
Description: Automates Brafton Inc,  ContentLEAD, and Castleford Media content publishing.  
Author: Brafton Inc
Author URI: http://www.brafton.com/support/wordpress
GitHub Plugin URI: ContentLEAD/brafton-wordpress-plugin
GitHub Plugin URI: https://github.com/ContentLEAD/brafton-wordpress-plugin
    */
if( !class_exists( 'WP_Brafton_Article_Importer' ) )
{
    if ( !defined( 'BRAFTON_PLUGIN_VERSION_KEY' ) )
                define( 'BRAFTON_PLUGIN_VERSION_KEY', 'brafton_importer_version' );
    if ( !defined( 'MYPLUGIN_VERSION_NUM' ) )
                define( 'BRAFTON_PLUGIN_VERSION_NUM', '1.0.0' );
    include_once( plugin_dir_path( __FILE__ ) .'/src/brafton_article_helper.php' );
    include_once( plugin_dir_path( __FILE__ ) . '/src/brafton_taxonomy.php' );
    include_once( plugin_dir_path( __FILE__ ) . '/src/brafton_image_handler.php' );
    include_once( plugin_dir_path( __FILE__ ) . '/src/brafton_article_importer.php' );
    include_once( plugin_dir_path( __FILE__ ) . '/src/brafton_errors.php' );
    include_once( plugin_dir_path( __FILE__ ) . '/src/brafton_video_helper.php' );
    include_once( plugin_dir_path( __FILE__ ) . '/src/brafton_video_importer.php' );
    class WP_Brafton_Article_Importer
    {   
        public $brafton_options; 
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // Initialize Settings
            require_once( sprintf( "%s/src/brafton_errors.php", dirname( __FILE__ ) ) );
            require_once( sprintf( "%s/src/brafton_options.php", dirname( __FILE__ ) ) );
            $brafton_options = Brafton_options::get_instance();
            require_once( sprintf( "%s/wp_brafton_article_importer_settings.php", dirname( __FILE__ ) ) );
            $brafton_importer_settings = new WP_Brafton_Article_Importer_Settings( $brafton_options );
            
            // Register custom post types
            require_once( sprintf( "%s/src/brafton_article_template.php", dirname( __FILE__ ) ) );
            $article_post_type = $brafton_options->options['brafton_article_post_type'];
            $article_post_type_name = $brafton_options->brafton_get_post_type( $article_post_type );
            if( $article_post_type ){ 
                $Brafton_Article_Template = new Brafton_Article_Template( $brafton_options, $article_post_type_name, array( 'singular' => 'Article', 'plural' => 'Articles' ), array( 'brafton_id', 'photo_id',) );
                //only log when importer is executed.
                if( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true )
                    brafton_log( array( 'message' => "Article post type is ready for more content. Article custom post type id: " . $article_post_type) );
            }
            $video_post_type = $brafton_options->options['brafton_video_post_type'];
            $video_post_type_name = $brafton_options->brafton_get_post_type( $video_post_type );
            if( $video_post_type ){ 
                $brafton_Video_Template = new Brafton_Article_Template( $brafton_options, $video_post_type_name, array( 'singular' => 'Video', 'plural' => 'Videos' ), array( 'brafton_id', 'photo_id' ) );
                if( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true )
                    brafton_log( array( 'message' => "Video post type is ready for more content. Video custom post type id: " . $video_post_type) );
            }
        } // END public function __construct
        /**
         * Activate the plugin
         */
        public static function activate()
        {
            //add actions and filters here: 
            // Do nothing
        } // END public static function activate
        /**
         * Deactivate the plugin
         */     
        public static function deactivate()
        {
            $brafton_options = Brafton_options::get_instance();
            //remove scheduled hook.
            brafton_import_clear_crons( 'brafton_import_trigger_hook' );
            if( $brafton_options->options['brafton_purge'] == 'posts' )
            {
                brafton_log( array( 'message' => "attempting to delete articles" ) );
                $brafton_options->purge_articles(); 
            }
            
            if( $brafton_options->options['brafton_purge'] == 'all' )
            {
                $brafton_options->purge_articles(); 
                //$brafton_optinos->purge_options();
            }
            //Flush rewrite rules if custom post types are enabled.
            if( $brafton_options->options['brafton_article_post_type'] != "" || $brafton_options->options['brafton_video_post_type'] != "" ) 
                flush_rewrite_rules();
            // Do nothing
        } // END public static function deactivate
    } // END class WP_Brafton_Article_Importer
} // END if(!class_exists('WP_Brafton_Article_Importer'))
if( class_exists( 'WP_Brafton_Article_Importer' ) )
{
    // Installation and uninstallation hooks
    register_activation_hook( __FILE__, array( 'WP_Brafton_Article_Importer', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'WP_Brafton_Article_Importer', 'deactivate' ) );
    // instantiate the plugin class
    $WP_Brafton_Article_Importer = new WP_Brafton_Article_Importer();
    /* This is the scheduling hook for our plugin that is triggered by cron */
    #add_action('brafton_import_trigger_hook', 'run_import', 10, 2);
    
    // Add a link to the settings page onto the plugin page
    if( isset( $WP_Brafton_Article_Importer ) )
    {
        // Add the settings link to the plugins page
        function plugin_settings_link( $links )
        { 
            $settings_link = '<a href="options-general.php?page=brafton_importer_options">Settings</a>'; 
            array_unshift( $links, $settings_link ); 
            return $links; 
        }
        $plugin = plugin_basename( __FILE__ ); 
        add_filter( "plugin_action_links_$plugin", 'plugin_settings_link' );
        
        //Manually run importer when settings are saved.
        add_action( 'load-toplevel_page_WP_Brafton_Article_Importer', 'run_article_import' );
        add_action( 'load-toplevel_page_WP_Brafton_Article_Importer', 'run_video_import' );
        //Run video and article importers when archives form is saved
        add_action( 'load-brafton_page_brafton_archives', 'brafton_run_hourly_import' );
        require_once plugin_dir_path( __FILE__ ) . '/vendors/tgm-activation.php';
        add_action( 'load-brafton_page_WP_Brafton_Article_Importer', 'brafton_admin_notice' );
        add_action( 'tgmpa_register', 'brafton_setup_recommended_plugins' );
        function brafton_setup_recommended_plugins(){
            $plugins = array(
                array(
                    'name'      => 'Brafton Analytics Dashboard', // The plugin name
                    'slug'      => 'brafton-analytics-dashboard', // The plugin slug (typically the folder name)
                    'source'    => plugin_dir_path( __FILE__ ) . 'vendors/recommended-plugins/brafton-analytics-dashboard.zip', // The plugin source
                    'required'  => false, // If false, the plugin is only 'recommended' instead of required
                    'force_activation' => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
                ),
                
                array(
                    'name' => 'WordPress SEO',
                    'slug' => 'wordpress-seo',
                    'required' => false,            
                ),
                array( 
                    'name' => 'Github Updater',
                    'slug' => 'github-updater-master', 
                    'source' => 'https://github.com/afragen/github-updater/archive/master.zip',
                    'required' => true,
                    'force_activation' => true, 
                ),
                array(
                    'name' => 'Google Analytics for WordPress',
                    'slug' => 'google-analytics-for-wordpress',
                    'required' => false,    
                ),
                array( 
                    'name' => 'Google Sitemap Generator',
                    'slug' => 'google-sitemap-generator', 
                    'required' => false,
                ),
                
                array(
                    'name'   => 'Contact Form 7',
                    'slug'   => 'contact-form-7',
                    'required' => false,
                )        
            );
            $config = array(
                'id'           => 'brafton_tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
                'default_path' => '',                      // Default absolute path to pre-packaged plugins.
                'menu'         => 'tgmpa-install-plugins', // Menu slug.
                'has_notices'  => true,                    // Show admin notices or not.
                'dismissable'  => false,                    // If false, a user cannot dismiss the nag message.
                'dismiss_msg'  => 'Brafton recommended plugins not installed.',                      // If 'dismissable' is false, this message will be output at top of nag.
                'is_automatic' => true,                   // Automatically activate plugins after installation or not.
                'message'      => 'Brafton Inc. recommends the following wp plugins.',                      // Message to output right before the plugins table.
                'strings'      => array(
                    'notice_can_install_required'    => _n_noop( 'Brafton plugin requires the following plugin: %1$s.', 'Brafton plugin requires the following plugins: %1$s.', 'tgmpa' ),
                    'notice_can_install_recommended' => _n_noop( 'Brafton Inc. recommends the following plugin: %1$s.', 'Brafton Inc. recommends the following plugins: %1$s.', 'tgmpa' ),
                    'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'tgmpa' ), // %1$s = plugin name(s).
                    'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
                    'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
                    'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'tgmpa' ), // %1$s = plugin name(s).
                    'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with the Brafton Plugin: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with the Brafton Plugin: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
                    'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'tgmpa' ), // %1$s = plugin name(s).
                    'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'tgmpa' ),
                    'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'tgmpa' ),
                    'return'                          => __( 'Return to Required Plugins Installer', 'tgmpa' ),
                    'plugin_activated'                => __( 'Plugin activated successfully.', 'tgmpa' ),
                    'complete'                        => __( 'All plugins installed and activated successfully. %s', 'tgmpa' ), // %s = dashboard link.
                    'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
                )
            );
            tgmpa( $plugins, $config );
            
        }
        /**
         * Run the article importer
         */
        function run_article_import( $cron = null ){
            //Wait until settings are saved or cron is triggered before attempting to import articles
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true || isset( $_POST['option_page'] ) && $_POST['option_page'] == 'brafton_archives' || $cron )
            {
                //Grab saved options.
                $brafton_options = Brafton_options::get_instance();
                //If article importing is disabled - do nothing
                if( $brafton_options->options['brafton_import_articles'] === 'off' ) {
                        brafton_log( array( 'message' => "Article importing is disabled." ) );
                    return;
                    } 
                //If api key isn't set - do nothing
                if( $brafton_options->options['brafton_api_key'] === ""  ) {
                    brafton_log( array( 'message' => " Brafton Api key is not set." ) );
                    return;
                }
                //if brafton error reporting is enabled - log importing.
                brafton_log( array( 'message' => 'Starting to import articles.' ) );
                
                
                                
                $brafton_cats = new Brafton_Taxonomy( $brafton_options );
                $brafton_tags = new Brafton_Taxonomy( $brafton_options );
                $brafton_image = new Brafton_Image_Handler( $brafton_options );
                $brafton_article = new Brafton_Article_Helper( $brafton_options );
                $brafton_article_importer = new Brafton_Article_Importer(
                    $brafton_image, 
                    $brafton_cats, 
                    $brafton_tags, 
                    $brafton_article, 
                    $brafton_options
                    );
                $brafton_article_importer->import_articles();
                //Schedule importer to run the next hour.
                brafton_schedule_import();
                $brafton_options->update_option( "brafton_options", "brafton_import_trigger_count", $brafton_options->get_option( "brafton_options", "brafton_import_trigger_count") + 1, 0);
            }
        }
        
         /**
         * Run importer for video articles
         * 
         * 
         * @param bool $cron 
         */
        function run_video_import( $cron = null )
        {
            //Wait until settings are saved or cron is triggered before attempting to import articles
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true  || $cron ) 
            {
                $brafton_options = Brafton_options::get_instance();
                if( $brafton_options->options['brafton_enable_video'] === 'off' ) {
                    brafton_log( array( 'message' => 'Video importing is disabled') );
                    return;
                }
                    brafton_log( array( 'message' => 'Starting to import videos.' ) );
                    $brafton_cats = new Brafton_Taxonomy( $brafton_options );
                    $brafton_tags = new Brafton_Taxonomy( $brafton_options );
                    $brafton_image = new Brafton_Image_Handler( $brafton_options );
                    $brafton_video = new Brafton_Video_Helper( $brafton_options );
                    $brafton_video_importer = new Brafton_Video_Importer(
                        $brafton_image, 
                        $brafton_cats, 
                        $brafton_video, 
                        $brafton_options 
                        );
                    $brafton_video_importer->import_videos();
                    $brafton_options->update_option( "brafton_options", "brafton_import_trigger_count", $brafton_options->get_option( "brafton_options", "brafton_import_trigger_count") + 1, 0);
                    
                    //Schedule importer.
                    brafton_schedule_import();
            }
        }
        
        #run duplicate killer if version is not appropriate
    }
    //Add video player scripts and css to site <head>. Executive decision - only support Atlantis.js 
    //Include Video Js for legacy clients with videojs embed codes.
    function brafton_enqueue_video_scripts() {
        $brafton_options = Brafton_options::get_instance(); 
        //support atlantisjs embed codes
        $player = $brafton_options->options['brafton_video_player'];
        switch( $player ) {
            case $player = "atlantis":
                wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 'atlantisjs', 'http://p.ninjacdn.co.uk/atlantisjs/v0.11.7/atlantis.js', array( 'jquery' ) );
                wp_enqueue_script( 'videojs', '//vjs.zencdn.net/4.3/video.js', array( 'jquery' ) );
                if( $brafton_options->options['brafton_player_css'] == 'on' )
                    wp_enqueue_style( 'atlantis', 'http://p.ninjacdn.co.uk/atlantisjs/v0.11.7/atlantisjs.css' );
                    wp_enqueue_style( 'videocss', '//vjs.zencdn.net/4.3/video-js.css' );
                break;
        }
    }
    add_action( 'wp_enqueue_scripts', 'brafton_enqueue_video_scripts' );
    //Used to preview video in admin page.
    add_action( 'admin_init', 'brafton_enqueue_video_scripts' );
    function brafton_import_clear_crons($hook)
    {
        $crons = _get_cron_array();
        if ( empty( $crons ) )
            return;
        foreach ( $crons as $timestamp => $cron )
            if ( !empty( $cron[$hook] ) )
                unset($crons[$timestamp][$hook]);
        _set_cron_array( $crons );
    }
    /**  
     * This is the scheduling hook for our plugin that is triggered by cron
     */
    add_action('brafton_import_trigger_hook', 'brafton_run_hourly_import', 10, 2);
    function brafton_run_hourly_import()
    {
        $cron = true;
        run_article_import( $cron );
        run_video_import( $cron );
        brafton_log( array( 'message' => "Import successfully triggered by wp cron." ) );
        brafton_schedule_import();
        //update_option("brafton_import_trigger_count", get_option("brafton_import_trigger_count") + 1);
        // HACK: posts are duplicated due to a lack of cron lock resolution (see http://core.trac.wordpress.org/ticket/19700)
        // this is fixed in wp versions >= 3.4.
        // if ( version_compare( $wpVersion, '3.4', '<') )
        //     duplicateKiller();
    }
    function brafton_schedule_import(){
        //Use wp_next_scheduled to check if import is already scheduled
        $timestamp = wp_next_scheduled( "brafton_import_trigger_hook" );
        //If $timestamp == false schedule hourly imports since it hasn't been done previously
        if( $timestamp == false  ){
           //Schedule the event for right now, then hourly until importing is disabled.
           wp_schedule_event( time() + 3600, "hourly", "brafton_import_trigger_hook" );
        }
       
    }    
  //Load the admin page Stylesheet. 
    function wp_brafton_article_importer_settings_style() {
        $siteurl = get_option( 'siteurl' );
        $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/css/settings.css';
        echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
    }
    add_action( 'admin_head', 'wp_brafton_article_importer_settings_style' );
}
