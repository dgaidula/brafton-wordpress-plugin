<?php
	include_once ( plugin_dir_path( __FILE__ ) . '../vendors/SampleAPIClientLibrary/ApiHandler.php');
	include_once ( plugin_dir_path( __FILE__ ) . '../vendors/SampleAPIClientLibrary/marpro-utility.php');
	include_once ( plugin_dir_path( __FILE__ ) . '/brafton_xmlhandler_validator.php' );
	class Brafton_Article_Helper {
		public $post_type;
		public $brafton_options;
		public $validator;
		// Require Client Libraries 
		function __construct( Brafton_Options $brafton_options ){
			$this->brafton_options = $brafton_options;
			$this->validator = new Brafton_XMLHandler_Validator();
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
					),
					'post_status' =>  array(
						'publish', 
						'pending', 
						'draft', 
						'auto-draft', 
						'future', 
						'private', 
						'inherit' 
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
				update_post_meta( $post_id, 'brafton_id', $brafton_id );
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