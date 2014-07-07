<?php
	include_once ( plugin_dir_path( __FILE__ ) . '../vendors/SampleAPIClientLibrary/ApiHandler.php');
	include_once ( plugin_dir_path( __FILE__ ) . '../vendors/SampleAPIClientLibrary/marpro-utility.php');
	class Brafton_Article_Helper {
		public $post_type;
		public $brafton_options;
		// Require Client Libraries 
		function __construct( Brafton_Options $brafton_options ){
			$this->brafton_options = $brafton_options;
			$post_type = $this->brafton_options->options['brafton_article_post_type']; 
			if( $this->brafton_options->options['brafton_article_post_type'] != "" )
				$this->post_type = $post_type; 
			else
				$this->post_type = 'post';
		}
		/**
		 * Checks if article already exists in WordPress database. Returns post_id or false if 
		 * no posts are found.
		 * @return Mixed $post_id
		 * @param int brafton_id       
		 */
		public function exists( $brafton_id ) //should be private
		{
			$args = array(
					'post_type' => $this->post_type, 
					'meta_query' => array( 
						array( 
							'key' => 'brafton_id', 
							'value' => $brafton_id 
						) 
					) 
			);
			$find = new WP_Query( $args );
			$post_id = false; 
			if( $find->have_posts() ) {
				while( $find->have_posts() ) {
				    $find->the_post();
				    $post_id = get_the_ID();
				} // end while
			} // end if
			wp_reset_postdata();
			return $post_id; 
		}
		/**
		 * Updates existing articles to reflect changes made to articles in client's feed 
		 * Reference: http://codex.wordpress.org/Function_Reference/wp_update_post
		 * @param Array $post_exists['post_id', 'post_status']
		 * @param Array $article_array 
		 * @return int $post_id 
		 */
		private function update_post( $article_array,  $post_exists )
		{
			$article_array['ID'] = $post_exists;
			//Makes sure to update articles still in drafts
			if ( $article_array['post_status']  == 'draft' ) //make sure publish status is a string
			{
				$article_array['edit_date']  = true; 
			}
			$post_id = marpro_wp_update_post( $article_array ); 
			return $post_id;
		}
		/**
		 * Grab Articles either from a specified Archive file or from Client Feed
		 * @return Array $article_array['post_author', 'post_date', 'post_content', 'post_title', 'post_status', 'post_excerpt', 'post_categories', 'tag_input']
		 */
		public function get_articles( )
		{
			$feed_settings = $this->get_feed_settings(); 
			if ( isset( $_FILES['brafton-archive']['tmp_name'] ) ) //todo add archive file upload settings
			{
				brafton_log( array( 'message' => "Archive option selected. Importing articles from xml archive file." ) );
				$articles = NewsItem::getNewsList( $_FILES['brafton-archive']['tmp_name'], "html" );
			} 
			else 
			{
				if ( preg_match( "/\.xml$/", $feed_settings['api_key'] ) ){
					$articles = NewsItem::getNewsList( $feed_settings['api_key'], 'news' );
				}
				else
				{
					$url = 'http://' . $feed_settings['api_url'];
					$ApiHandler = new ApiHandler( $feed_settings['api_key'], $url );
					$articles = $ApiHandler->getNewsHTML(); 	
				}
			}
			return $articles; 
		}
		/** Handles dynamic authorship based on byline field.
		 *  Carefull it uses the display name rather than username since the
		 *  former doesn't have to be unique. First, last, or full name must
		 *  match the user's display name in the databse.
		 *  need to refactor this contains a loop inside of the article import loop. Not good.
		 *  @author Ali 3-21-2014
		 */
		function get_blog_user_id( $byLine ) 
		{
			//find this blog's users who have authorship rights.
			$blog_id = get_current_blog_id();
		    $args = array(  'blog_id' => $blog_id, 
		                    'orderby' => 'display_name',
		                    'who' => 'authors',
		        );
		    $blogusers = get_users( $args );
		    $author_set = false; 
		    // compare each user with byLine field. 
		    foreach ($blogusers as $user) {
		    	//byline is either first or last name and display name is full name
		    	$first_or_last = stripos( $user->display_name, $byLine ); 
		    	if( $author_set == false) 
		    		//we have a direct match. 
		    		if( $byLine == $user->display_name ){
						$user_id = $user->ID;
						$author_set = true; 
			        }
			        //the byLine is just first or last name and this 
			        //substring is found in User display name
					elseif( gettype( $first_or_last ) == 'integer' )
					{
						$user_id = $user->ID; 
						$author_set = true; 
					}
		    }
		    return $user_id; 
		}
		/**
		 * //Article publish date
		 * @return String $post_date
		 */
		public function get_publish_date($article_array) {
			
			switch (  $this->brafton_options->options['brafton_post_publish_date']  )
			{
				case 'modified':
					$date = $article_array->getLastModifiedDate();
					break;
				case 'created':
					$date = $article_array->getCreatedDate();
					break;
				default:
					$date = $article_array->getPublishDate();
					break;
			}
			//format post date
			$post_date_gmt = strtotime( $date );
			$post_date_gmt = gmdate( 'Y-m-d H:i:s', $post_date_gmt );
			$post_date = get_date_from_gmt( $post_date_gmt );
			
			return $post_date;
		}
		/**
		 * Retrieves client feed uri and brafton API key from brafton settings
		 * @return Array $feed_settings['url', 'API_key']
		 */
		public function get_feed_settings( ){
				$feed_settings = array(
					"api_url" => $this->brafton_options->options['brafton_domain'],
					"api_key" => $this->brafton_options->options['brafton_api_key'],
				);	
			
			
			return $feed_settings; 
		}
		/**
		 * Insert article into database
		 * @return int post_id
		 * @param Array $article_array = array (
		 * 								'post_author', 
		 * 								'post_date', 
		 * 								'post_content', 
		 * 								'post_title', 
		 * 								'post_status', 
		 * 								'post_excerpt', 
		 * 								'post_categories', 
		 * 								'tag_input', 
		 * 								'brafton_id'
		 * 							);
		 */
		public function insert_article($article_array){
			
			$article_array['post_type'] = $this->post_type; 
			// //Checks if post exists
			$post_exists = $this->exists( $article_array['brafton_id'] );
			$brafton_id = $article_array['brafton_id']; 
			unset( $article_array['brafton_id'] );
			//if article does not exist
			if ( $post_exists  == false )
			{	//add the article to WordPress
				$post_id = marpro_wp_insert_post( $article_array ); 
				
				//add custom meta field so we can find the article again later.
				update_post_meta($post_id, 'brafton_id', $brafton_id );
			}
			else
			{
				//check if overwrite is set to on
				if ( $this->brafton_options->options['brafton_overwrite'] == 'on' )
					$post_id = $this->update_post( $article_array, $post_exists ); 
				
			}
			if( is_wp_error( $post_id) )
				brafton_log( array( 'message' => 'Failed to import article with brafton_id: ' . $brafton_id . ' titled: ' . $article_array['post_title'] . '. WP returned error: ' . $post_id->get_error_message() ) );
			else
				brafton_log( array( 'message' => 'Successfully imported article with brafton_id: ' . $brafton_id . ' titled: ' . $article_array['post_title'] ) );
			
			return $post_id;
			//not returning post_id here because if post already exists and overwrite 
			//isn't enabled, post_id will be undefined.
		}
		/**
		 * Generates an array of all sucessfully imported articles. Maintains order
		 * articles are found in the client's feed.
		 * 
		 * @return article_log
		 * 
		 */
		public function imported_articles(){
		}
		/**
		 * @usedby WP_Brafton_Article_Importer
		 * Completely removes all instances of Brafton Articles from WP. 
		 */
        public function purge_articles()
        {
        	
        }
	}
?>