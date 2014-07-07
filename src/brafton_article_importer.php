<?php 
if ( !class_exists( 'Article_Importer' ) )
{	
	include_once ( plugin_dir_path( __FILE__ ) . '../vendors/SampleAPIClientLibrary/ApiHandler.php');
	include_once 'brafton_article_helper.php';
	include_once 'brafton_taxonomy.php';
	include_once 'brafton_image_handler.php';
	include_once 'brafton_errors.php';
	/**
	 * @package WP Brafton Article Importer 
	 *
	 */
	class Brafton_Article_Importer {
		 public $brafton_article_log;
		 public $brafton_article;
		 public $brafton_images;
		 public $brafton_options;
		//Initialize 
		function __construct ( 
						Brafton_Image_Handler $brafton_image = Null, 
						Brafton_Taxonomy $brafton_cats, 
						Brafton_Taxonomy $brafton_tags, 
						Brafton_Article_Helper $brafton_article, 
						Brafton_Options $brafton_options )
		{
			$this->brafton_options = $brafton_options;
			//and load the image class.
			$this->brafton_image_handler = $brafton_image;
			$this->brafton_cats = $brafton_cats;
			$this->brafton_tags = $brafton_tags; 
			$this->brafton_article = $brafton_article; 
			$this->brafton_image = $brafton_image;
		}
		/**
		 * @uses Brafton_Article_Helper to retrieve an articles array containing NewsItem objects.
		 * @uses ApiHandler indirectly through Brafton_Article_Helper to grab article specific metadata from client's xml uri.
		 * @uses NewsItem indirectly through ApiHandler to grab article specific metadata from client's xml uri.
		 * @uses NewsCategory indirectly through NewsItem to grab category id's from client's xml uri.
		 * @uses XMLHandler indirectly through NewsItem to make http requests to client's xml url. 
		 * @uses Brafton_Taxonomy to assign category and tags to Articles
		 * @uses Brafton_Image_handler to attach post thumbnails to Articles
		 * 
		 * 
		 * Imports content from client's xml feed's uri into WordPress. 
		 */
		public function import_articles(){
			//Retrieve articles from feed
			$article_array = $this->brafton_article->get_articles();
			if ( empty( $article_array) )
				brafton_log( array( 'message'=>  "No articles found on the feed. Check to see if any exist: " . $this->brafton_options->article_list() ) );
			//Retrieve article import log
			foreach( $article_array as $a ){
				//Get article meta data from feed
				$brafton_id = $a->getID(); 
				$post_exists = $this->brafton_article->exists( $brafton_id );
				if( $post_exists == false || $this->brafton_options->options['brafton_overwrite'] == 'on' )
				{
					brafton_log( array( 'message' => 'Attempting to import article with brafton_id: ' . $brafton_id ) );
					$post_date = $this->brafton_article->get_publish_date( $a ); 
					$post_title = $a->getHeadline();
					$post_content = $a->getText(); 
					if( $this->brafton_options->options['enable_dynamic_authorship']  === 'on');
					$by_line = $a->getByLine();
					
	
					$post_excerpt = $a->getExtract(); 
					$keywords = $a->getKeywords();
					
					//prepare video article category id array
					if( $this->brafton_options->options['brafton_enable_categories'] == "on" ){ 
						$cats = $a->getCategories(); 
						$post_category = $this->brafton_cats->get_terms( $cats, 'category', null, $brafton_id );  
					}
					//prepare article tag id array
					if( $this->brafton_options->options['brafton_enable_tags'] == "on" ){
						$tags = $a->getTags();
						$input_tags = $this->brafton_tags->get_terms( $tags, 'post_tag', null, $brafton_id );
					}
					//Get more video article meta data
					$post_author = $this->brafton_options->options['brafton_post_author']; 
					if( isset( $by_line ) ) {
						$post_author = $this->brafton_article->get_blog_user_id( $by_line ); 
					}
					$post_status = $this->brafton_options->options['brafton_post_status'];
					//prepare single article meta data array
					$article = compact(
								'brafton_id', 
								'post_author', 
								'post_date', 
								'post_content', 
								'post_title', 
								'post_status', 
								'post_excerpt', 
								'post_category'
								/* 'tags_input' */
							); 	
				//for articles imported as drafts. let publish date be determined by articles.
				if( ! $post_status == "publish" )
					$article['post_date'];
					if( isset( $input_tags) )
						$article['tags_input'] = $input_tags;
					//insert article to WordPress database
					$post_id = $this->brafton_article->insert_article( $article );
					
					//update post to include thumbnail image
					if ( $this->brafton_options->options['brafton_enable_images'] == "on" ){ 
						$photos = $a->getPhotos(); 
						$this->brafton_image->insert_image( $photos, $post_id );	
					}
				}
				else{
				 	brafton_log( array( 'message' => 'Article already exists and overwrite is disabled. Check the ' . get_post_type( $post_exists ) . " post type in your wp_posts table. Article Title: " . get_the_title( $post_exists ) . " Post ID: " . $post_exists ) );
				 } 
			}
		}
	}
}
?>