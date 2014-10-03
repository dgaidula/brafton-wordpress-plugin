<?php
/**
 * @author Ali <techsupport@brafton.com>
 * Handles post tag and category assignment. Maintains parent child relationships
 * if they exist. Brafton Tech or will need to set these relationships manually. 
 * 
 * If necessary we can add a get_parent_term to dynamically insert taxonomy hierarchy 
 * found in the feed.
 */
	
	class Brafton_Taxonomy {
		
		public $brafton_options;
		function __construct( $brafton_options){
			$this->braftion_options = $brafton_options;
		}
		/**
		 * Adds given tags, categories, or custom taxonomies to wordpress database.   
		 * @param String $taxonomy
		 * @param Array $terms 
		 * Retrieves array of either category tag, or brafton_term id's to be included in article array
		 * @return $term_id[int] 
		 * 
		 */
		public function get_terms( $terms = null, $taxonomy, $video = null, $brafton_id = null, $client = null )
		{
			$term_array = array(); 
			//When terms is a string convert it to an array.

			if( isset( $video ) ){
				$term_array = $this->insert_video_terms( $client, $brafton_id );
			}
			if( $terms ){ 
				#brafton_log( array( 'message' => "Preparing to insert items in the following taxonomy " . $taxonomy . " items: " . var_export( $terms, true ) ) );
				foreach( $terms as $t )
				{	
					$term_name = $t->getName(); 
					if( $term_name ){
						$term_id = $this->insert_term( $term_name, $taxonomy );
						$term_array[] = $term_id;
					}

				}
			}
			else
				brafton_log( array( 'message' => 'No ' . $taxonomy .' found for this article on the feed. Brafton ID: ' . $brafton_id ) );
			 
			$custom_terms = $this->get_custom_terms( $taxonomy );
			if( $custom_terms == false ) {
				brafton_log( array( 'message' => ' Custom ' . $taxonomy . ' is not set. ') );
				return $term_array;
			}
			$include_custom = array_merge( $term_array, $custom_terms );
			return $include_custom;	
		}
		public function insert_term( $term_name, $taxonomy )
		{
			$term = get_term_by( 'name', sanitize_text_field( $term_name ), $taxonomy );
				//If term already exists	
				if( ! $term == false )
					$term_id = $term->term_id;
				//Insert new term
				else{
					// todo: check if term has a parent taxonomy.
					$term_id = wp_insert_term( sanitize_text_field( $term_name ), $taxonomy);
				}
				return $term_id;
		}
		/**
		 * Inserts terms when given array of strings.
		 * 
		 *  @param $terms[String]
		 */
		public function insert_terms( $terms, $taxonomy )
		{
			if( count( $terms ) > 0 ) { 
				$terms_array = array();
				foreach( $terms as $term ){ 
					$terms_array[] = $this->insert_term( $term, $taxonomy ); 	
				}
				return $terms_array;
			}
			return false; 
		}
		/**
		 * Retrieves custom terms from options table. Returns Array of custom terms. 
		 * If no terms are defined, returns false.
		 * 
		 * @param Array $term_array
		 * @param String $taxonomy
		 * @return Mixed $custom_terms 
		 */
		public function get_custom_terms(  $taxonomy )
		{
			$options = get_option( 'brafton_options' );
			$option = 'brafton_custom_' . $taxonomy;
			if ( $option == 'brafton_custom_category' && isset( $options['brafton_custom_category'] ) ) 
				$custom_terms = $options[$option];
			if ( $option == 'brafton_custom_post_tag' && isset( $options['brafton_custom_post_tag'] ) )
				$custom_terms = $this->brafton_options->options['brafton_custom_post_tag']; 
			if( !isset( $custom_terms ) )
				return false;	

			//do a pattern match later.			
			$terms = explode( ', ', $custom_terms );
			brafton_log( array( 'message' => "Preparing to insert items in the following taxonomy " . $taxonomy . " items: " . var_export( $terms, true ) ) );
			foreach( $terms as $t )
			{
				$term_id = $this->insert_term( $t, $taxonomy );
				$term_array[] = $term_id;
			} 
			return $term_array;
		}
		/**
		 * Insert Video Terms
		 * Video feed only has categories. since 07-09-2014
		 */ 
		private function insert_video_terms( $categories, $brafton_id ){
			
			if ( isset($categories->ListForArticle($brafton_id, 0, 100)->items[0]->id ) )
			{
				$cat_id = $categories->ListForArticle( $brafton_id, 0, 100 )->items[0]->id;
				$category = $categories->Get( $cat_id );
				$post_category = array(
					wp_create_category( $category->name )
				);
				return $post_category;
			}
		}
	}	
?>