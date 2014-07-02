<?php
if( !class_exists('WP_Brafton_Article_Importer_Settings' ) )
{
    /*
     *Requires Wordpress Version 2.7
     *Handling HTTP Requests*
     */
    if( !class_exists( 'WP_Http' ) )
        include_once( ABSPATH . WPINC. '/class-http.php' );
    /**
     * Contains logic used to render Brafton Options Menu
     * @uses Brafton_Options to dynamically display options stored in database
     */
    class WP_Brafton_Article_Importer_Settings
    {
        /**
         * Construct the Plugin object
         * @param $brafton_options 
         */
        public function __construct( Brafton_Options $brafton_options )
        {
            if( isset( $brafton_options ) )
                $this->brafton_options = $brafton_options; 
            // register actions
            add_action( 'admin_init', array( &$this, 'admin_init' ));
            add_action( 'admin_menu', array( &$this, 'add_menu' ));
        } // END public function __construct
        
        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
            // register brafton plugin's settings
            register_setting( 'WP_Brafton_Article_Importer_group', 'brafton_options' ); 
            // add brafton settings sections
            add_settings_section(
                'brafton_article_section', 
                'Article Settings', 
                array( &$this, 'settings_section_brafton_article' ), 
                'WP_Brafton_Article_Importer'
            );
            add_settings_section(
                'brafton_video_section', 
                'Video Settings', 
                array( &$this, 'settings_section_brafton_video' ), 
                'WP_Brafton_Article_Importer'
            );
            add_settings_section(
                'brafton_advanced_section', 
                'Advanced Settings', 
                array( &$this, 'settings_section_brafton_advanced' ), 
                'WP_Brafton_Article_Importer'
            );
            add_settings_section(
                'brafton_developer_section', 
                'Developer Settings', 
                array( &$this, 'settings_section_brafton_developer' ), 
                'WP_Brafton_Article_Importer'
            );
            add_settings_section(
                'brafton_archives_section',
                'Archives', 
                array( &$this, 'settings_section_brafton_archives' ),
                'Brafton_Archives'
            );
           
            // Possibly do additional admin_init tasks
        } // END public static function activate
        /**
         * Register article section fields 
         */
        public function settings_section_brafton_article()
        {
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_import_article', 
                'Articles', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_article_section',
                array(
                    'name' => 'brafton_import_articles',
                    'options' => array( 'off' => ' Off',
                                        'on' => ' On' ), 
                    'default' => 'off'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_api_key', 
                'API Key', 
                array( &$this->brafton_options, 'settings_field_input_text' ),  
                'WP_Brafton_Article_Importer', 
                'brafton_article_section',
                array(
                    'name' => 'api-key',
                    'field' => 'brafton_api_key'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_domain', 
                'Product', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_article_section',
                array(
                    'name' => 'brafton_domain', 
                    'options' => array( 'api.brafton.com/' => ' Brafton', 
                                        'api.contentlead.com/'=> ' ContentLEAD', 
                                        'api.castleford.com.au/' => ' Castleford'
                        ),
                    'default' => 'api.brafton.com/'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_post_author', 
                'Post Author', 
                array( &$this->brafton_options, 'settings_author_dropdown' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_article_section',
                array(
                    'name' => 'brafton_post_author'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_post_status', 
                'Default Post Status', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_article_section',
                 array(
                    'name' => 'brafton_post_status', 
                    'options' => array('publish' => ' Publish',
                                       'draft' => ' Draft' ),
                    'default' => 'publish'
                )
            );
        }
        /**
         * Register video section fields 
         */
        public function settings_section_brafton_video()
        {
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_enable_video', 
                'Videos', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_video_section',
                array(
                    'name' => 'brafton_enable_video',
                    'options' => array( 'off' => ' Off',
                                        'on' => ' On' ), 
                    'default' => 'off'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_video_secret', 
                'Private Key', 
                array( &$this->brafton_options, 'settings_field_input_text' ),  
                'WP_Brafton_Article_Importer', 
                'brafton_video_section',
                array(
                    'name' => 'video-private-key',
                    'field' => 'brafton_video_secret'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_video_public', 
                'Public Key', 
                array( &$this->brafton_options, 'settings_field_input_text' ),  
                'WP_Brafton_Article_Importer', 
                'brafton_video_section',
                array(
                    'name' => 'video-public-key',
                    'field' => 'brafton_video_public'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_video_feed_num', 
                'Feed Number', 
                array( &$this->brafton_options, 'settings_field_input_text' ),  
                'WP_Brafton_Article_Importer', 
                'brafton_video_section',
                array(
                    'name' => 'feed-number',
                    'field' => 'brafton_video_feed_num', 
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_video_player', 
                'Video Player', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_video_section',
                array(
                    'name' => 'brafton_video_player',
                    'options' => array( 'atlantis' => ' AtlantisJS',
                                        'none' => ' None'
                                        ), 
                    'default' =>    'none'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_enable_script', 
                'Import Jquery', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_video_section',
                array(
                    'name' => 'brafton_enable_script',
                    'options' => array( 'off' => ' Off',
                                        'on' => ' On' ), 
                    'default' => 'off'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_player_css', 
                'Player CSS', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_video_section',
                array(
                    'name' => 'brafton_player_css',
                    'options' => array( 'off' => ' Off',
                                        'on' => ' On' ), 
                    'default' => 'off'
                )
            );
        }
        /**
         * Register advanced section fields 
         */
        public function settings_section_brafton_advanced()
        {
            add_settings_field(
                'WP_Brafton_Article_Importer_enable_dynamic_authorship', 
                'Dynamic Authorship', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_advanced_section',
                array(
                    'name' => 'enable_dynamic_authorship', 
                    'options' => array( 'off' => ' Off',
                                        'on' => ' On' ), 
                    'default' => 'off'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_enable_images', 
                'Images', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_advanced_section', 
                array(
                    'name' => 'brafton_enable_images', 
                    'options' => array( 'off' => ' Off',
                                        'on' => ' On' ), 
                    'default' => 'on'
                    )
                );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_enable_categories', 
                'Categories', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_advanced_section',
                array(
                    'name' => 'brafton_enable_categories', 
                    'options' => array('on' => ' Brafton Categories',
                                       'off' => ' None' ), 
                    'default' => 'on'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_enable_tags', 
                'Tags', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_advanced_section',
                array(
                    'name' => 'brafton_enable_tags', 
                    'options' => array('tags' => ' Brafton Tags as Tags',
                                       'keywords' => ' Brafton Keywords as Tags',
                                       'categories' => ' Brafton Categories as Tags', 
                                       'none' => ' None' ), 
                    'default' => 'none'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_custom_category', 
                'Custom Categories', 
                array( &$this->brafton_options, 'settings_field_input_text' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_advanced_section',
                array(
                    'name' => 'custom_categories',
                    'field' => 'brafton_custom_category'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_custom_post_tag', 
                'Custom Tags', 
                array( &$this->brafton_options, 'settings_field_input_text' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_advanced_section',
                array(
                    'name' => 'custom_tags',
                    'field' => 'brafton_custom_post_tag'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_post_publish_date', 
                'Post Date: ', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_advanced_section',
                 array(
                    'name' => 'brafton_post_publish_date', 
                    'options' => array('published' => ' Published Date',
                                       'modified' => ' Last Modified Date',
                                       'created' => ' Created Date'
                                       ),
                    'default' => 'published'
                )
            );
        }
        /**
         * Register developer section fields 
         */
        public function settings_section_brafton_developer()
        {
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_article_post_type', 
                'Custom Article Post Type', 
                array( &$this->brafton_options, 'settings_field_input_text' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_developer_section', 
                array(
                    'name' => 'brafton-post-slug', 
                    'field' => 'brafton_article_post_type',
                    )
                );
               add_settings_field(
                'WP_Brafton_Article_Importer_brafton_video_post_type', 
                'Custom Video Post Type', 
                array( &$this->brafton_options, 'settings_field_input_text' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_developer_section', 
                array(
                    'name' => 'brafton-post-slug', 
                    'field' => 'brafton_video_post_type',
                    )
                );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_overwrite', 
                'Overwrite', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_developer_section',
                array(
                    'name' => 'brafton_overwrite', 
                    'options' => array('off' => ' Off',
                                       'on' => ' On' ), 
                    'default' => 'off'
                )
            ); 
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_purge', 
                'Deactivation', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_developer_section',
                array(
                    'name' => 'brafton_purge',
                    'options' => array( 'none' => ' Stop Importing Content', 
                                        'posts' => ' Delete All ' . $this->brafton_options->brafton_get_product() . ' Content', 
                                        'all' => ' Purge this plugin entirely!'
                                        ), 
                    'default' => 'none'
                )
            );
            add_settings_field(
                'WP_Brafton_Article_Importer_brafton_enable_errors', 
                'Errors', 
                array( &$this->brafton_options, 'render_radio' ), 
                'WP_Brafton_Article_Importer', 
                'brafton_developer_section',
                array(
                    'name' => 'brafton_enable_errors', 
                    'options' => array( 'off' => ' Off',
                                        'on' => ' On'
                                        ), 
                    'default' => 'off'
                )
            );
        }

        /**
         * Register archive page fields 
         */
        public function settings_section_brafton_archives(){
            
             add_settings_field(
                'WP_Brafton_Article_Importer_brafton_archives',
                'Archives',
                array( &$this->brafton_options, 'settings_xml_upload' ),
                'Brafton_Archives',
                'brafton_archives_section',
                array('label' => 'Upload a specific xml Archive file', 
                    'name' => 'brafton-archive' 
                    )
            ); 
        }
        /**
         * add js to admin head for jQuery Tabs 
         */
        public function scripts() {
            //Ul quick pagination http://www.sitepoint.com/jquery-quick-pagination-list-items/
            wp_enqueue_script( 'jquery-pagination', plugin_dir_url( __FILE__ ) . 'js/jquery.quick.pagination.min.js', array( 'jquery' ) );
            wp_enqueue_script( 'brafton-admin-js', plugin_dir_url( __FILE__ ) . 'js/brafton-admin.js', array( 'jquery', 'jquery-pagination' ) );
            
            //wp_print_scripts( 'jquery-ui-tabs' );
        }
       
        /**
         * add brafton plugin menu pages
         */     
        public function add_menu()
        {
            // Add a page to manage this plugin's settings
            $admin_page = add_menu_page(
                'WP Brafton Article Importer Settings', 
                 $this->brafton_options->brafton_get_product(), 
                'manage_options', 
                'WP_Brafton_Article_Importer', 
                array( &$this, 'plugin_settings_page' )
            );
            add_submenu_page(
                'WP_Brafton_Article_Importer', 
                'Brafton Settings', 
                'Settings', 
                'manage_options', 
                'WP_Brafton_Article_Importer', 
                array( &$this, 'plugin_settings_page' )
                );
             // add_submenu_page(
             //    'WP_Brafton_Article_Importer', 
             //    'Brafton Dashboard', 
             //    'Reports', 
             //    'manage_options', 
             //    'brafton_dashboard', 
             //    array( &$this, 'plugin_dashboard_page' )
             //    );
            add_submenu_page( 
                'WP_Brafton_Article_Importer', 
                'Archival Upload', 
                'Archival Import', 
                'edit_files', 
                'brafton_archives', 
                array( &$this, 'brafton_archives_page' ) 
            );
            add_action( 'admin_print_scripts-' . $admin_page, array( &$this, 'scripts' ) );

           if( $this->brafton_options->options['brafton_enable_errors'] == "on" ) { 
                $error_page = add_submenu_page( 
                    'WP_Brafton_Article_Importer', 
                    'Brafton Errors', 
                    'Error Log', 
                    'edit_files', 
                    'brafton_errors', 
                    array( &$this, 'brafton_errors_page' ) 
                );
            add_action( 'admin_print_scripts-' . $error_page, array( &$this, 'scripts' ) );
            }

        } // END public function add_menu()
       
        /**
         * Archive Menu page callback 
         */
        public function brafton_archives_page()
        {
            if( !current_user_can( 'manage_options' ) )
                {
                    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
                }
        
            // Render the settings template
            include( sprintf( "%s/src/templates/archives.php", dirname( __FILE__ ) ) );
        }
       
        public function brafton_errors_page(){
            if( !current_user_can( 'manage_options' ) )
            {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

            // Render the settings template
            include( sprintf( "%s/src/templates/error_log.php", dirname( __FILE__ ) ) );
        }
        /**
         * Brafton Menu Callback
         */     
        public function plugin_settings_page()
        {
            if( !current_user_can( 'manage_options' ) )
            {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }
    
            // Render the settings template
            include_once( plugin_dir_path( __FILE__ )  . '/src/templates/settings.php'  );
        } // END public function plugin_settings_page()
    } // END class WP_Brafton_Article_Importer_Settings
} // END if(!class_exists('WP_Brafton_Article_Importer_Settings' ))