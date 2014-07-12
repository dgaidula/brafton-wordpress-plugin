<?php 
    include_once ( plugin_dir_path( __FILE__ ) . '../brafton_errors.php');

    define( "BRAFTON_OPTIONS", "brafton_options" );
    define( "BRAFTON_ERROR_LOG", "brafton_error_log" );
    /**
     * Singleton Class for retrieving options from the wordpress database.
     */
    class Brafton_Options
    {   
        //Default brafton options
        public $options; 
        //Array of plugin errors log
        public $errors; 
        //Brafton_Options Object 
        private static $instance = null;
        //Let's hinder direct instantiation by cloning.  
        private final function __construct( ){
            $default_options  =  array( "brafton_import_articles" =>"on", 
                                        "brafton_domain" => "api.brafton.com/", 
                                        "brafton_api_key" => "",
                                        "brafton_post_status" => "publish", 
                                        "brafton_enable_video" => "off", 
                                        "brafton_enable_script" => "off", 
                                        "brafton_player_css" => "off", 
                                        "brafton_enable_images" => "on", 
                                        "brafton_video_post_type" => "",
                                        "brafton_article_post_type" => "", 
                                        "brafton_post_publish_date" => "published", 
                                        "brafton_parent_categories" => "off", 
                                        "brafton_overwrite" =>"off", 
                                        "brafton_purge" => "none", 
                                        "brafton_enable_errors" => 'Off',
                                        "brafton_import_trigger_count" => 0,
                                        "brafton_player_css" => "",
                                        "brafton_video_player" => "",
                                        "brafton_video_public" => "",
                                        "brafton_video_secret" => "",
                                        "brafton_video_feed_num" => "",
                                        "brafton_post_author" => "",
                                        "enable_dynamic_authorship" => "off",
                                        "brafton_enable_tags" => "",
                                        "brafton_enable_categories" => "", 
                                        "brafton_custom_category" => "",
                                        "brafton_error_log" => ""
                                    );
            $brafton_options =  get_option( 'brafton_options' );
            $options = wp_parse_args( $brafton_options, $default_options );
            foreach( $options as $key => $value )
            {
                if( !$key ) continue;
                //Initialize brafton_error_log if one doesn't exist
                if( $key == 'brafton_error_log' && 
                    !isset( $brafton_options['brafton_error_log'] ) 
                    )
                {
                    brafton_initialize_log( 'brafton_error_log' );
                    continue;
                }    
                $brafton_options[$key] = $value;
            }
            $this->options = $brafton_options;  
        }
        private final function __clone() { }
        public final function __sleep() {
            throw new Exception('Serializing of Singletons is not allowed');
        }
        /**
         * Save  option in single option's table field
         * 
         * @param String $option_name 
         * @param String $key 
         * @param String $value
         *          
         */ 
        function update_option( $option_name, $key, $value ) {
            //first get the option as an array
            $options = get_option( $option_name );
            if ( !$options ) {
                //no options have been saved yet, so add it
                add_option( $option_name, array($key => $value) );
            } else {
                //update the existing option
                $options[$key] = $value;
                update_option( $option_name , $options );
                //echo "updated options are <pre>" . var_dump( $options ) . "</pre><br />";
            }
        }
        /**
         * Retreive option value from single field in WP options table.
         * @param String $option_name 
         * @param String $key          
         * @param String $default 
         * 
         * @return $option
         */
        function get_option($option_name, $key, $default = false) {
            $options = get_option( $option_name );
            if ( $options ) {
                return (array_key_exists( $key, $options )) ? $options[$key] : $default;
            }
            return $default;
        }
        /**
         * Removes single option from options brafton_options field in wp options table.
         * @param String $option_name 
         * @param String $key 
         */
        function delete_option($option_name, $key) {
            $options = get_option( $option_name );
            if ( $options ) {
                unset($options[$key]);
                update_option( $option_name , $options );
            }
        }
        /**
         * Access this object with this method.
         */
        public static function get_instance() {
            if (self::$instance === null) 
                self::$instance = new self();
            return self::$instance;
        }
        // /**
        //  * Registers settings for plugin options page.
        //  */
        // public function register_options()
        // {
        //  $options = $this->brafton_options;
        //  foreach( $options as $key => $value )
        //  {
        //      register_setting('WP_Brafton_Article_Importer_group', $key );
        //  }
        // }
        /**
         * Checks which company client is partnered with. 
         * Castleford, ContentLEAD, or Brafton
         * @return string $product
         */     
        public function brafton_get_product()
        {
            $product = $this->options['brafton_domain'];
            switch( $product ){
                case 'api.brafton.com/':
                    return 'Brafton';
                    break;  
                case 'api.contentlead.com/':
                    return 'ContentLEAD';
                    break; 
                case 'api.castleford.com.au/':
                    return 'Castleford'; 
                    break; 
            }
        }
        /**
         * Retrieves api feed url.
         */
        function get_feed_url(){
            $product = $this->options['brafton_domain'];
            $key = $this->options['brafton_api_key'];
            $feed_url = "http://" . $product . $key . '/news';
            return $feed_url;
        }
        /**
         *
         */
        /**
         *  
         *  Retrieves an array of author ids with user level greater than 0 from WordPress Database. 
         *  @uses http://codex.wordpress.org/Function_Reference/get_users
         *  @return array [int]
         */
        public function brafton_get_blog_authors()
        {
            $users = array(); 
            $args = array(  'blog_id' => $GLOBALS['blog_id'], 
                            'orderby' => 'display_name',
                            'who' => 'authors',
                );
            $blogusers = get_users( $args );
            $user_attributes = array();
            foreach ( $blogusers as $user ) {
                $user_attributes['id'] = $user->ID;
                $user_attributes['name'] = $user->display_name;
                $users[] = $user_attributes; 
            }
            return $users; 
        }
        
          /**
         * Helper method for default post status.
         * Retrieves brafton article post_type name from brafton options
         * used by both video and article importer classes to check post type.
         * 
         * @return $article_post_type
         */
        public function brafton_get_post_type( $option_value ){
            if( $option_value != "")
                return $option_value; 
            else
               return 'post';
        }
        public function brafton_has_api_key(){
            $option = $this->options['brafton_api_key'];
            if( $option == '' ) //better to check if api key is valid
                return false; 
            return true; 
        }
        public function validate_api_key( $key )
        {
            //todo:
            //what kind of hashing algorithm do we use for our API keys
        }
        public function validate_options( $input ){
            $output = get_option( 'brafton_options' );
           // todo:
           // validate feed key
           // validate custom taxonomies
        }
        public function next_import_time($scheduled_time, $time_now)
        {
            return round( abs( strtotime( $time_now ) -  strtotime( $scheduled_time ) )/ 60,0 );
        }
        /**
         * @usedby WP_Brafton_Article_Importer
         * Completely removes all instances of Brafton Articles from WP. 
         */
        public function purge_articles()
        {
            $video_post_type = $this->brafton_get_post_type( $this->options['brafton_video_post_type'] );
            $article_post_type = $this->brafton_get_post_type( $this->options['brafton_article_post_type'] );
            $post_type = array();
            if( isset( $video_post_type ) && $video_post_type )
                $post_type[] = $video_post_type;
            if( isset( $article_post_type )  && $article_post_type )
                $post_type[] = $article_post_type;
           
            $post_type[] = 'post';
            $args = array( 'post_type' => $post_type , 'meta_key' => 'brafton_id' , 'posts_per_page' => -1 );
            $purge = new WP_Query( $args );
            if( $purge->have_posts() ) : while( $purge->have_posts() ) : $purge->the_post();
                $post_id = get_the_ID();
                brafton_log( array( 'message' => 'Purge imported content on deactivation option seleted. Deleting post titled ' . get_the_title( $post_id ) ) );
                wp_delete_post( $post_id, true );
            endwhile;
            endif;
            wp_reset_postdata();
        }
        /**
         * Purges Options
         */
        public function purge_options()
        {
            delete_option( BRAFTON_ERROR_LOG );
            delete_option( BRAFTON_OPTIONS );
        }
        /**
         * Displays next scheduled import time.
         */
        public function next_scheduled_import(){
            $crons = _get_cron_array();

            $output = array();
            foreach ($crons as $timestamp => $cron)
            {
                if( wp_get_schedule( 'brafton_import_trigger_hook' ) ) {
                    $timestamp += 60;
                    if( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ){
                        $output['message'] = 'Brafton plugin requires <strong>WP Cron</strong> to automate content publishing. Please enable either WP Cron or <a href="http://codex.wordpress.org/Editing_wp-config.php#Alternative_Cron">Alternate Cron</a> in your wp-config.php file.';
                        $output['class'] = "error";                       
                    }
                    elseif( isset($cron['brafton_import_trigger_hook']) )
                    {
                        // $date = new DateTime();
                        // $date->setTimezone( new DateTimeZone( 'New York' ) );
                        // $date->setTimestamp( $timestamp );
                        $next = $this->next_import_time( date( "Y-m-d H:i:s", $timestamp ), date( "Y-m-d H:i:s" ) );
                        $output['message'] = "Hourly content importing enabled. " . $next ." minutes until next scan for new content.";
                        $output['class'] = "updated";
                    }                    
                }
                else
                {
                    $output['message'] = 'First time here? Save settings before hourly automatic content publishing can begin.';
                    $output['class'] = "updated";
                }
               
            }

            return $output;
        }
        public function link_to_product()
        {
            $product = $this->brafton_get_product(); 
            switch( $product )
            {
                case 'Brafton' : 
                    $url = 'http://brafton.com'; 
                    break; 
                case 'ContentLEAD': 
                    $url = 'http://contentlead.com';
                    break; 
                case 'Castleford': 
                    $url = 'http://castleford.com.au';
                    break; 
            }
            $output = sprintf('<a href="%s">%s</a>', $url, $product ); 
            return $output;     
        }
        public function article_list(){
            $product = $this->brafton_get_product(); 
            $api_key = $this->options['brafton_api_key'];
            switch( $product )
            {
                case 'Brafton' : 
                    $url = 'http://brafton.com'; 
                    break; 
                case 'ContentLEAD': 
                    $url = 'http://contentlead.com';
                    break; 
                case 'Castleford': 
                    $url = 'http://castleford.com.au';
                    break; 
            }
            $output = sprintf('<a href="%s/%snews">%s %s</a>', $url, $api_key, $product, $api_key  ); 
            return $output;   
        }
        public function get_article_link()
        {
            $feed = $this->options['brafton_api_key'];
            $product = $this->options['brafton_domain'];
            $post_id = get_the_ID();
            $brafton_id = get_post_meta($post_id, 'brafton_id', true);
            $feed_url = sprintf('http://%s%s/news/%s', $product, $feed, $brafton_id);
            return $feed_url; 
        }
        public function get_sections()
        {
            $sections = array(
                'brafton-article-section' => 'Article Settings', 
                'brafton-video-section' => 'Video Settings', 
                'brafton-advanced-section' => 'Advanced Settings', 
                'brafton-developer-section' => 'Developer Settings',
                ); 
            return $sections;
        }
    }
?>