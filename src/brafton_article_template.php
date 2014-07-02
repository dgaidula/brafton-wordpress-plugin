<?php
if(!class_exists('Brafton_Article_Template'))
    include_once 'brafton_errors.php';
{
	/**
	 * A PostTypeTemplate class that provides 3 additional meta fields
	 */
	class Brafton_Article_Template
	{
        /**
         * Custom Post Type id. Set by 
         */ 
		public $post_type_id;

        /**
         * Singular and Plural versions of CPT Name. 
         * 
         * Array
         */
        public $product_names;

        /**
         * Custom Meta Box values.
         * 
         * Array 
         */ 
		private $_meta;

        public $brafton_options;
		
    	/**
    	 * The Constructor
    	 */
    	public function __construct( Brafton_Options $brafton_options, $post_type_option, $product_names, $_meta )
    	{
                $this->brafton_options = $brafton_options;
                $this->post_type_id = $post_type_option;
                $this->product_names = $product_names;

                $this->_meta = $_meta;
    		// register actions
    		add_action('init', array(&$this, 'init'));
    		add_action('admin_init', array(&$this, 'admin_init'));  
            //brafton_log( array( 'message' => "Successfully created " . $product_names['singular'] .  " custom post type with id: " . $this->post_type_id ) );
           
    	} // END public function __construct()

    	/**
    	 * hook into WP's init action hook
    	 */
    	public function init()
    	{
    		// Initialize Post Type
    		$this->create_brafton_post_type();
    		add_action('save_post', array(&$this, 'save_post'));
    	} // END public function init()

    	/**
    	 * Create the post type
    	 */
    	public function create_brafton_post_type()
    	{         
            // $post_slug = $this->brafton_options->options['brafton_custom_post_slug'];
            // if( !$post_slug )
            //     $post_slug = 'blog'; 

     		register_post_type($this->post_type_id,
    			array(
    				'labels' => array(
    					'name' => $this->brafton_options->brafton_get_product() . " " . $this->product_names['plural'],
    					'singular_name' => __(ucwords(str_replace("_", " ", $this->post_type_id)))
    				),
    				'public' => true,
    				'has_archive' => true,
                    'taxonomies' => array('category'),
                    'rewrite'            => array( 'slug' => $this->post_type_id ),
    				'description' => __("This is a sample post type meant only to illustrate a preferred structure of plugin development"),
    				'supports' => array(
    					   'title', 'author' , 'editor', 'excerpt', 'thumbnail', 'revisions', 'post_formats', 'custom-fields'
    				),
    			)
    		);
            
            //todo modify this to only flush rewrite rules when settings are updated manually. 
            flush_rewrite_rules();
    	}
	
    	/**
    	 * Save the metaboxes for this custom post type
    	 */
    	public function save_post($post_id)
    	{
            // verify if this is an auto save routine. 
            // If it is our form has not been submitted, so we dont want to do anything
            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            {
                return;
            }
            
    		if( isset($_POST['post_type']) == $this->post_type_id && current_user_can('edit_post', $post_id))
    		{
    			foreach($this->_meta as $field_name)
    			{
    				// Update the post's meta field
    				update_post_meta($post_id, $field_name, $_POST[$field_name]);
    			}
    		}
    		else
    		{
    			return;
    		} // if($_POST['post_type'] == $this->post_type_id && current_user_can('edit_post', $post_id))
    	} // END public function save_post($post_id)

    	/**
    	 * hook into WP's admin_init action hook
    	 */
    	public function admin_init()
    	{			
    		// Add metaboxes
            $product = $this->product_names['plural'];
            switch( $product ){
                case 'Videos' :
                    add_action('add_meta_boxes', array(&$this, 'add_video_meta_boxes'));
                    break;
                case 'Articles' : 
                    add_action('add_meta_boxes', array(&$this, 'add_article_meta_boxes'));

                    break; 
            }
                
    	} // END public function admin_init()
			
        //hook into WP's add_meta_boxes action hook to print video meta boxes in admin menu.    
        public function add_video_meta_boxes()
        {
            // Add this metabox to every selected post
            add_meta_box( 
                sprintf('WP_Brafton_Article_Importer_%s_section', $this->post_type_id),
                sprintf('%s %s Information', ucwords( str_replace( "_", " ", $this->brafton_options->brafton_get_product() ) ), $this->product_names['singular'] ),
                array(&$this, 'add_video_inner_meta_boxes'),
                $this->post_type_id, 
                'normal',
                'high'
            );
        }    
    	/**
    	 * hook into WP's add_meta_boxes action hook to display article meta boxes in video menu.
    	 */
    	public function add_article_meta_boxes()
    	{
    		// Add this metabox to every selected post
    		add_meta_box( 
    			sprintf('WP_Brafton_Article_Importer_%s_section', $this->post_type_id),
    			sprintf('%s %s Information', ucwords( str_replace( "_", " ", $this->brafton_options->brafton_get_product() ) ), $this->product_names['singular'] ),
    			array(&$this, 'add_article_inner_meta_boxes'),
    			$this->post_type_id, 
                'side'
    	    );					
    	} // END public function add_meta_boxes()

        /**
         * called off of the add video meta box
         */     
        public function add_video_inner_meta_boxes( $post )
        {
            // Render the job order metabox
            include( sprintf( "%s/templates/brafton_video_template_metabox.php", dirname( __FILE__ ), $this->post_type_id ) );    
        }
		/**
		 * called off of the add article meta box
		 */		
		public function add_article_inner_meta_boxes( $post )
		{		
			// Render the job order metabox
			include( sprintf( "%s/templates/brafton_article_template_metabox.php", dirname( __FILE__ ), $this->post_type_id ) );			
		} // END public function add_inner_meta_boxes($post)

        /**
         * Flush rewrite rules. 
         * Method runs after post type is activated and anytime settings are updated as long as custom post type option exists.
         */
        public function brafton_flush_rewrite(){
            //Only run when we are on brafton options page and settings are updated.
            if( isset( $_GET['page'] ) && $_GET['page'] =='WP_Brafton_Article_Importer' && isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] )
            {
                echo 'in brafton flush';

                $brafton_options = Brafton_options::get_instance();

                if( $brafton_options->options['brafton_article_post_type'] != "" || $brafton_options->options['brafton_video_post_type'] !=" " ){
                  flush_rewrite_rules();
                  echo 'flushed rewrite rules';                  
                }
             
            }

        }

	} // END class Brafton_Article
} // END if(!class_exists('Brafton_Article'))