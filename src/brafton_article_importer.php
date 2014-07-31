<?php 
if ( !class_exists( 'Article_Importer' ) )
{	
	include_once ( plugin_dir_path( __FILE__ ) . '../vendors/SampleAPIClientLibrary/ApiHandler.php');
	include_once 'brafton_article_helper.php';
	include_once 'brafton_taxonomy.php';
	include_once 'brafton_image_handler.php';
	include_once 'brafton_errors.php';
	include_once ( plugin_dir_path( __FILE__ ) . '/brafton_article_validator.php' );
	include_once 'brafton_hooks.php';
	
	/**
	 * @package WP Brafton Article Importer 
	 *
	 */
	class Brafton_Article_Importer {
		 public $brafton_article_log;
		 public $brafton_article;
		 public $brafton_images;
		 public $brafton_options;
		 public $validator; 
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
			$this->validator = new Brafton_Article_Validator();
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
			if ( empty( $article_array) ) { 
				brafton_log( array( 'message'=>  "No articles found on the feed. Check to see if any exist: " . $this->brafton_options->article_list() ) );
				return;
			}
			//Retrieve article import log
			foreach( $article_array as $a ){
				//Get article meta data from feed
				$brafton_id = $a->getID(); 
				$post_exists = $this->brafton_article->exists( $brafton_id );
				if( $post_exists == false || $this->brafton_options->options['brafton_overwrite'] == 'on' )
				{
					$post_date = $this->brafton_article->get_publish_date( $a ); 
					$post_title = $a->getHeadline();
					$post_content = $a->getText(); 

					//Pull author from byline field if set.
					//if( $this->brafton_options->options['enable_dynamic_authorship']  === 'on');
					$by_line = $a->getByLine();
	
					$post_excerpt = $a->getExtract(); 
					//Only make request category page if necessary. 
					if( $this->brafton_options->options['brafton_enable_categories'] == "on" || $this->brafton_options->options['brafton_enable_tags'] == "categories" )
						$cats = $a->getCategories(); 
					//prepare video article category id array
					if( $this->brafton_options->options['brafton_enable_categories'] == "on"  ){ 
						$post_category = $this->brafton_cats->get_terms( $cats, 'category', null, $brafton_id );  
					}
					//prepare article tag id array
					//prepare article tag id array
					if( $this->brafton_options->options['brafton_enable_tags'] != "none" ){
						switch ( $this->brafton_options->options['brafton_enable_tags'] ){
							case 'tags' :
								$tags = explode( ', ', $a->getTags() );
								$tags_input = $this->brafton_tags->insert_terms( $tags, 'post_tag' );
								break;
							case 'categories' : 
								$tags = $cats;
								$tags_input = $this->brafton_tags->get_terms( $tags, 'post_tag', null, $brafton_id );
								break;
							case 'keywords' :
								$tags = explode( ', ', $a->getKeywords() );
								$tags_input = $this->brafton_tags->insert_terms( $tags, 'post_tag' );
								break;
						}
					}

					//Set Post Author
					$post_author = $this->brafton_options->options['brafton_post_author']; 
					if( isset( $by_line ) ) {
						$user_id = $this->validator->is_found( $by_line, 'byline' );

						if( gettype( $user_id ) === 'integer' )
							$post_author = $user_id; 
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
					//for articles imported as drafts, let publish date be determined by wp.
					if( $post_status === "draft" )
						unset( $article['post_date'] );


					if( $tags_input !== false ){
						$article['tags_input'] = $tags_input;

					}
					//insert article to WordPress database
					$post_id = $this->brafton_article->insert_article( $article );
					
					//update post to include thumbnail image
					if ( $this->brafton_options->options['brafton_enable_images'] == "on" ){ 
						$photos = $a->getPhotos(); 
						$this->brafton_image->insert_image( $photos, $post_id );	
					}

					// Run any custom functions provided in theme functions.php
					$brafton_helper_classes = array( 
					'options' => $this->brafton_options, 
					'article' => $this->brafton_article, 
					'images' => $this->brafton_image, 
					'taxonomy' => $this->brafton_cats 
					);
	        		brafton_article_save_hook( $post_id, $article, $brafton_helper_classes );
				}
				else{
				 	brafton_log( array( 'message' => sprintf('Article "<a href="%s" target="_blank">%s</a>" already exists and overwrite is disabled.', get_edit_post_link( $post_exists ), get_the_title( $post_exists ) )  ) );
				} 
			}
		}
	}
}
?>