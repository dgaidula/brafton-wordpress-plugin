<?php 
    require_once( sprintf( "%s/brafton_errors.php", dirname( __FILE__ ) ) );

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
        public $archives; 
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

        public function last_import_run()
        {

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
        
        /**
         * Renders an upload field
         */
        public function settings_xml_upload($args)
        {
            $name = $args['name'];
            $label = $args['label'];
            echo sprintf('<div class="archive-upload"><p>%s</p><input type="file" name="%s" /></div>', $label, $name);
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

        /**
         * There are no hooks to modify the output of do_settings_sections()
         * 
         * Writing custom versions of do_settings_sections to avoid overwriting wp_core. 
         * Couldn't figure out how to have tabbed nav through settings api while using
         * single field to store database options. 
         * 
         * Need to Revise later. 
         * @source http://www.smashingmagazine.com/2011/10/20/create-tabs-wordpress-settings-pages/
         * @source http://wordpress.stackexchange.com/questions/33629/change-the-display-of-settings-api-do-settings-sections
         */ 
        function brafton_do_settings_sections($page) {
            global $wp_settings_sections, $wp_settings_fields;
            if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
                return;
            $count = 0;
            foreach( (array) $wp_settings_sections[$page] as $section ) {
                $count++;
                echo sprintf('<div class="%s">', 'tab-pane');
                echo "<h3>{$section['title']}</h3>\n";
                call_user_func($section['callback'], $section);
                if ( !isset($wp_settings_fields) ||
                     !isset($wp_settings_fields[$page]) ||
                     !isset($wp_settings_fields[$page][$section['id']]) )
                        continue;
                echo '<div class="settings-form-wrapper">';
                $this->brafton_do_settings_fields($page, $section['id']);
                echo '</div>';
                echo '</div>';
            }
        }

        /**
         * See brafton_do_settings_sections
         */
        function brafton_do_settings_fields($page, $section) {
            global $wp_settings_fields;

            if ( !isset($wp_settings_fields) ||
                 !isset($wp_settings_fields[$page]) ||
                 !isset($wp_settings_fields[$page][$section]) )
                return;

            foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
                echo '<div class="settings-form-row">';
                if ( !empty($field['args']['label_for']) )
                    echo '<p><label for="' . $field['args']['label_for'] . '">' .
                        $field['title'] . '</label><br />';
                else
                    echo '<p>' . $field['title'] . '<br />';
                call_user_func($field['callback'], $field['args']);
                echo '</p></div>';
            }
        }

        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_input_text($args)
        {
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = $this->get_option( 'brafton_options', $field);
            // echo a proper input type="text"
            echo sprintf('<div class="%s"><input type="text" name="%s[%s]" id="%s" value="%s" /></div>', $args['name'], BRAFTON_OPTIONS, $field, $field, $value);
        } // END public function settings_field_input_text($args)

        public function settings_author_dropdown( $element )
        {
            $field = $element['name'];
            $value = $this->get_option( 'brafton_options', $element['name'] ); 
            
            $output = '<select name= "' . BRAFTON_OPTIONS . '[' . esc_attr( $field ) . ']" >'; 
  
                $options = $this->author_options(); 
            
               
                foreach ( $options as $o )
                {
                   
                    $output .= '<option value="' .  esc_attr( $o['id'] ) . '"'; 
                    if( $value == $o['id'] )
                        $output .=  ' selected >'; 
                    else
                        $output .= '>';

                    $output .=  esc_attr( $o['name'] ) . '</option>';
                    
                }
                $output .=  '</select>';

            echo sprintf( $output );
        }

        /**
         * @uses Brafton_Options to retrieve users with authorship privileges 
         */
        private function author_options(){


               $blog_authors = $this->brafton_get_blog_authors(); 

               return $blog_authors; 
        }

        public function render_radio($element)
        {
            $output = '';
            $value = $this->get_option( 'brafton_options', $element['name'] ); 

            //echo $value;

            if ( $value == '' && isset( $element['default'] ) ){
                $value = $element['default'];
                $this->update_option( 'brafton_options', $element['name'], $element['default'] );
            }
            
                foreach ($element['options'] as $key => $option)
                {
                    $output .= '<div class="radio-option ' . str_replace( '_', '-', $element['name'] ) . '"><label><input type="radio" name="' . BRAFTON_OPTIONS . '['. esc_attr($element['name']) .']" value="'. esc_attr($key) . '"';

                    if ( $value == $option ){
                      $output .=   checked($key, $value, true) . ' checked' . ' /><span>' . esc_html($option) . '</span></label></div>';
                    }
                    $output .=   checked($key, $value, false) . ' /><span>' . esc_html($option) . '</span></label></div>';
                }                                   
            echo sprintf( $output );
        }

        public function render_select($element)
        {
            $element = array_merge(array('value' => null), $element);
            
            $output = '<select name="' . BRAFTON_OPTIONS . '['. esc_attr($element['name']) .']"' . (isset($element['class']) ? ' class="'. esc_attr($element['class']) .'"' : '') . '>';
            
            foreach ( (array) $element['options'] as $key => $option) 
            {
                if (is_array($option)) {
                    $output .= '<optgroup label="' . esc_attr($key) . '">' . $this->_render_options($option) . '</optgroup>';
                }
                else {
                    $output .= $this->_render_options(array($key => $option), $element['value']);
                }
                
            }
            
            return $output . '</select>';
        }

        // helper for: render_select()
        private function _render_options($options, $selected = '') 
        {   
            $output = '';
            
            foreach ($options as $key => $option) {
                $output .= '<option value="'. esc_attr($key) .'"'. selected((string) $selected, $key, false) .'>' . esc_html($option) . '</option>';
            }
            
            return $output;
        }
    }
?>