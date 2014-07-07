<?php
/**
 * Video Import loop
 */

include_once ( plugin_dir_path( __FILE__ ) . '../vendors/RCClientLibrary/AdferoArticlesVideoExtensions/AdferoVideoClient.php');
include_once ( plugin_dir_path( __FILE__ ) . '../vendors/RCClientLibrary/AdferoArticles/AdferoClient.php');
include_once ( plugin_dir_path( __FILE__ ) . '../vendors/RCClientLibrary/AdferoPhotos/AdferoPhotoClient.php');

include_once 'brafton_video_helper.php';
include_once 'brafton_taxonomy.php';
include_once 'brafton_image_handler.php';
include_once 'brafton_errors.php';


class Brafton_Video_Importer 
{
	public $brafton_video;
	public $brafton_image;
	public $brafton_options;

	//Initialize 
	function __construct ( 
					Brafton_Image_Handler $brafton_image = Null, 
					Brafton_Taxonomy $brafton_cats, 
					Brafton_Video_Helper $brafton_video, 
					Brafton_Options $brafton_options )
	{

		$this->brafton_options = $brafton_options;

	
		//grab image data for previously imported images
		//and load the image class.
		$this->brafton_image = $brafton_image;
		
		$this->brafton_cats = $brafton_cats;
		$this->brafton_video = $brafton_video; 
		$this->brafton_image = $brafton_image;
	}

	public function import_videos()
	{
		$video_articles = $this->brafton_video->get_video_articles();
		$categories = $this->brafton_video->adfero_client->Categories();

		foreach( $video_articles->items as $video )
		{

			$brafton_id = $video->id;
			$post_exists = $this->brafton_video->exists( $brafton_id );
			if( $post_exists == false || $this->brafton_options->options['brafton_overwrite'] == 'on' )
			{
				$attribute = $this->brafton_video->adfero_client->Articles()->Get( $brafton_id );
				
				$presplash = $attribute->fields['preSplash'];

				$postsplash = $attribute->fields['postSplash'];
				$post_title = $attribute->fields['title'];
				$post_excerpt = $attribute->fields['extract'];
				$post_date = $this->brafton_video->format_post_date( $attribute->fields['date'] );
				$post_content = $attribute->fields['content'];

				$post_status = $this->brafton_options->options['brafton_post_status'];

				$this->brafton_video->get_video_output( $brafton_id, $presplash );
				
				$post_category = $this->brafton_cats->get_terms( false, 'category', true, $brafton_id, $categories );
				
				$video_article = compact( 
					'post_author', 
					'post_date', 
					'post_content', 
					'post_title', 
					'post_status', 
					'post_excerpt', 
					'post_category'
					);

				//for articles imported as drafts. let publish date be determined by articles.
				if( ! $post_status == "publish" )
					$video_article['post_date'];

				$post_id = $this->brafton_video->insert_video_article( $video_article, $brafton_id );
				
		
				$scale_axis = 500;
				$scale = 500;
				//update post to include thumbnail image
				if ( $this->brafton_options->options['brafton_enable_images'] == "on" ) {
					$photos = $this->brafton_video->adfero_client->ArticlePhotos();
					$this->brafton_image->insert_image( $photos, $post_id, $video = true, $scale_axis, $scale, $brafton_id );	
				}
			}
			else{
				 	brafton_log( array( 'message' => 'Video already exists and overwrite is disabled. Video Title: ' . get_the_title( $post_exists ) . " Post ID: " . $post_exists ) );

			}
		}
	}
}

?>