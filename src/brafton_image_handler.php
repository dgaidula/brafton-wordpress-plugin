<?php 

	// Require Client Libraries 

	include_once( dirname( plugin_dir_path( __FILE__ ) ). '/vendors/SampleAPIClientLibrary/ApiHandler.php');

	include_once( dirname( plugin_dir_path( __FILE__ ) ) . '/vendors/SampleAPIClientLibrary/Photo.php');

	include_once( dirname( plugin_dir_path( __FILE__ ) ) . '/vendors/SampleAPIClientLibrary/PhotoInstance.php');

	include_once( dirname( plugin_dir_path( __FILE__ ) ) . '/vendors/RCClientLibrary/AdferoArticlesVideoExtensions/AdferoVideoClient.php');

	include_once( dirname( plugin_dir_path( __FILE__ ) ) . '/vendors/RCClientLibrary/AdferoArticles/AdferoClient.php');

	include_once( dirname( plugin_dir_path( __FILE__ ) ) . '/vendors/RCClientLibrary/AdferoPhotos/AdferoPhotoClient.php');

	include_once( plugin_dir_path( __FILE__ ) . '/brafton_errors.php' );

	class Brafton_Image_Handler {

		public $brafton_options;

		function __construct( $brafton_options){

			$this->brafton_options = $brafton_options;

		}

		/**

		 * Removes attached image and adds new post thumnail image to  an article.

		 * @param int $post_id

		 * @param Array $images_array['image_id', 'image_caption', 'image_url']

		 * @return int $updated_attachment_id

		 */

		private function update_image( $images_array,  $post_id )

		{

			//Grab pic_id of pre-existing post.

			$old_image_id = get_post_meta( $post_id, $images_array['image_id'], true ); 

			//Make sure the article to update doesn't already have an image	

			if( ! ( get_the_post_thumbnail( $post_id ) ) ){

				//if the image is the same as the image on client's feed. Do nothing.

				if( $old_image_id == $images_array['image_id'] )

					return; 



				//Detach old image.

				delete_post_thumbnail( $post_id ); 

			}



			$updated_attachment_id = $this->download_image( $images_array, $post_id );

			return $updated_attachment_id;



		}

		/**

		 * @usedby Brafton_Article_Importer to update posts with post thumbnail images after post creation. 

		 * @usedby Brafton_Video_Importer to update posts with post thumbnail images after post creation. 

		 * @param mixed $photos 

		 * @param int $post_id

		 * @param bool $video optional 

		 * @return $attachment_id

		 * $photos parameter must either be an AdferoArticlePhotosClient 

		 * object or a string for video articles and regular articles respectively. 

		 */

		public function insert_image( $photos, $post_id, $video = NULL, $scale_axis = NULL, $scale = NULL, $brafton_id = NULL )

		{

			if( $video )

				$images_array = $this->get_video_images( $photos, $scale_axis, $scale, $brafton_id ); 

			else

				$images_array = $this->get_article_images( $photos ); 



			if ( $images_array == false ) return;





			if( $this->brafton_options->options['brafton_overwrite'] == 'on' )

				$attachment_id = $this->update_image( $images_array, $post_id ); 

			

			else

				$attachment_id = $this->download_image( $images_array, $post_id ); 



			return $attachment_id;

		}	



		/**

	 	 * Retrieves image uri and excerpt details for articles.  

	 	 * @uses XMLHandler

	 	 * @uses Photo

	 	 * @uses PhotoInstance

	 	 *

		 * @param Array $photos of Photo objects 

		 * @return Array $images_array['image_id', 'image_caption', 'image_url']

		 */

		private function get_article_images( $photos )

		{

			$image_id = null; 

			$image_url = null;

			$image_caption = null;

			if ( !empty( $photos ) )

			{

				//Large photo

				$image = $photos[0]->getLarge();// uses XMLHandler and Photo returns PhotoInstance

				if ( !empty( $image ) )

				{

					$image_url = $image->getUrl(); //necessary web request returns string

					$image_caption = $photos[0]->getCaption();

					$image_id = $photos[0]->getId(); //necessary 

				}

			}



				$images_array = compact( 'image_id', 'image_caption', 'image_url' );

				return $images_array; 

		}



		/**

		 * Retrieves image uri and excerpt details for video articles. 

		 * @param AdferoArticlePhotosClient $photos 

		 * @return Array images_array['image_id', 'image_caption', 'image_url']

		 */

		private function get_video_images( $photos, $scale_axis, $scale, $brafton_id ){

			

			$photoURI = "http://pictures.video.brafton.com/v2/";

			$photoClient = new AdferoPhotoClient( $photoURI );



			$thisPhotos = $photos->ListForArticle($brafton_id, 0, 100);

			//If Video doesn't include an image.

			if ( ! isset( $thisPhotos->items[0] ) ) {

				brafton_log( array( 'message' => "This video article doesn't include an image. Brafton_id: " . $brafton_id ) );

				return false;

			}



			$image_id = $photos->Get( $thisPhotos->items[0]->id )->sourcePhotoId;

			$image_url = $photoClient->Photos()->GetScaleLocationUrl( $image_id, $scale_axis, $scale )->locationUri;



			$image_url = strtok( $image_url, '?' );

			$image_caption = $photos->Get($thisPhotos->items[0]->id)->fields['caption'];



			$image_id = $thisPhotos->items[0]->id;



			$images_array = compact( 'image_id', 'image_caption', 'image_url' );



			return $images_array; 



		}



		/**

		 * Downloads and stores image as post thumbnail 

		 * Reference: http://codex.wordpress.org/Function_Reference/media_handle_sideload

		 * Reference: http://codex.wordpress.org/Function_Reference/download_url

		 * @param Array $images_array['image_id', 'image_caption', 'image_url']

		 * @param int $post_id 

		 * @return int $attachment_id

		 */

		public function download_image( $images_array, $post_id )

		{	



			if( ! $images_array['image_url'] ) {

				brafton_log( 

						array( 

							'message' => 'This article is missing an image. Make sure your feed has an image for this article - ' . get_the_title( $post_id )

							 )

					);

			}



			$orig_filename = $this->get_image_file_name( $images_array['image_url'] ); 



			// If post already has a thumbnail or feed does not have an updated image - Move on to the next article in the loop.

		    if ( has_post_thumbnail( $post_id ) ){

		     return;

		    }



			// Download file to temp location and setup a fake $_FILE handler

		    // with a new name based on the slug



		    $tmp_name = download_url( $images_array['image_url'] );

		    $file_array['name'] = $orig_filename;  // new filename based on slug

		    $file_array['tmp_name'] = $tmp_name;

		     // If error storing temporarily, unlink

		    if ( is_wp_error( $orig_filename ) ) {

		        @unlink( $file_array['tmp_name'] );

		        $file_array['tmp_name'] = '';

		    }



		    $attachment = array(

									'title' => $images_array['image_caption'],

									'post_excerpt' => $images_array['image_caption'],

									'caption' => $images_array['image_caption'],

									'alt' => 'inherit', 

								);



		    // validate and store the image.  

		    $attachment_id = media_handle_sideload( $file_array, $post_id, $attachment );

		    add_post_meta( $post_id, '_thumbnail_id', $attachment_id );

		    add_post_meta( $post_id, 'image_id', $images_array['image_id'] );

			return $attachment_id; 

		} 



		/**

		 * Strips file name from image url 

		 * @return String $image_file_name

		 * 

   		 */

		public function get_image_file_name( $original_image_url )

		{

			$domain = $this->brafton_options->options['brafton_domain'];

			$domain = str_replace( 'api', 'http://pictures', $domain );

			$image_file_name = str_replace( $domain , "" , $original_image_url);



			return $image_file_name; 

		}



	}

?>