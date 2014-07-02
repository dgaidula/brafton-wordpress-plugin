<?php 

/**

 * @author Ali <techsupport@brafton.com>

 * Helper class for brafton_video_importer.

 */

include_once ( dirname( plugin_dir_path( __FILE__ ) ) . '/vendors/RCClientLibrary/AdferoArticlesVideoExtensions/AdferoVideoClient.php');

include_once ( dirname( plugin_dir_path( __FILE__ ) ) . '/vendors/RCClientLibrary/AdferoArticles/AdferoClient.php');

include_once ( dirname( plugin_dir_path( __FILE__ ) ) . '/vendors/RCClientLibrary/AdferoPhotos/AdferoPhotoClient.php');



class Brafton_Video_Helper

{

	private $video_out_client; 

	public $adfero_video_client; 

	public $adfero_client; 

	private $base_url = 'http://api.video.brafton.com/v2/';



	private $mp4;

	private $ogg;

	private $flv;

	private $HDmp4; 

	private $HDogg;

	private $HDflv;

	private $height;

	private $width;

	private $embed_code;

	public $brafton_options;

	private $presplash;

	public $post_type;

	function __construct( $brafton_options )

	{

		$this->brafton_options = $brafton_options;

		$post_type = $this->brafton_options->options['brafton_video_post_type']; 
		if( $this->brafton_options->options['brafton_video_post_type'] != "" )
			$this->post_type = $post_type; 
		else
			$this->post_type = 'post';



		$video_settings = $this->get_video_settings();



		$adfero_video_client = new AdferoVideoClient( $this->base_url, $video_settings['video_public'], $video_settings['video_secret'] );

		$this->adfero_client = new AdferoClient( $this->base_url, $video_settings['video_public'], $video_settings['video_secret'] );



		$this->video_out_client = $adfero_video_client->videoOutputs();

	}

	





	/**

	 * Retrieves video settings from options table. 

	 * 

	 * @return Array $video_settings

	 */

	private function get_video_settings()

	{

		$video_settings = array(

				'video_secret' => $this->brafton_options->options['brafton_video_secret'],

				'video_public' => $this->brafton_options->options['brafton_video_public'], 

				'feed_num' => $this->brafton_options->options['brafton_video_feed_num']

			);



		return $video_settings; 

	}

	/**

	 * Grabs all video articles from client's feed. 

	 * @return AdferoArticleList $video_article_list. 

	 */

	public function get_video_articles()

	{

		$feeds = $this->adfero_client->Feeds();

		$feedList = $feeds->ListFeeds(0, 10);



		$video_articles = $this->adfero_client->Articles();

		$feed_num = $this->brafton_options->options['brafton_video_feed_num']; 

		$video_article_list = $video_articles->ListForFeed( $feedList->items[ $feed_num ]->id, 'live', 0, 100 );		

		return $video_article_list;

	}



	/**

	 * Generates video output as html

	 */

	public function get_video_output( $brafton_id, $presplash )

	{



		$video_list = $this->video_out_client->ListForArticle( $brafton_id,0,10 );

		$list = $video_list->items;

		$this->presplash = $presplash;

		foreach( $list as $list_item ){

			$output = $this->video_out_client->Get( $list_item->id );

			$type = $output->type; 

			$this->format_video_output( $output, $type );

		}

		//Generate embed code string.

		$this->create_embed_code( $brafton_id, $presplash );

	}	


	/**

	 * Helper function for get_video_output(). Returns an associative 

	 * array with video and format as key and value pairs respectively.

	 * 

	 * Typically each video has 6 supported video formats. Clients rarely 

	 * use all of them. Unfortunately, cross browser support for all 

	 * video formats is slacking. 

	 * 

	 * @param AdferoVideoOutput $output

	 * @param String $type

	 */

	private function format_video_output( $output, $type )

	{

		$path = $output->path; 





		//Standard suported outputs

		if( $type == 'htmlmp4' ){ 

			$this->mp4 = $path;

			$this->width = $output->width; 

			$this->height = $output->height;

		}

		if( $type == 'htmlogg' ){

			$this->ogg = $path;

			$this->width = $output->width; 

			$this->height = $output->height; 

		}

		if( $type == 'flash' ) {

			$this->flv = $path; 

			$this->width = $output->width; 

			$this->height = $output->height;

		}



		//Grab HD outputs

		if ( $type == 'custom' )

		{

			//Retrieve video file extension.

			$ext = pathinfo( $path, PATHINFO_EXTENSION );



			switch($ext){

				case "mp4": $this->HDmp4 = $path; break;

				case "ogg": $this->HDogg = $path; break;

				case "flv": $this->HDflv = $path; break;

			}

		}

	}

	/**

	 * Helper function for get_video_output(). Generates video embed code html output. 

	 * 

	 * Must use echo htmlspecialchars( $embed_code ) to print output 

	 * when debugging. 

	 * 

	 * @param $brafton_id

	 * @return void

	 * 

	 */

	public function create_embed_code( $brafton_id, $presplash ){

		$player = $this->brafton_options->options['brafton_video_player'];

		$width = $this->width; 

		$height = $this->height; 



		if ( $player == 'atlantis' )

		{

			$this->embed_code = <<<EOT

            <video id='video-$brafton_id' class="ajs-default-skin atlantis-js" controls preload="auto" width="$width" height='$height'

                    poster='$presplash'>

                    <source src="$this->mp4" type='video/mp4' data-resolution="288" />

                    <source src="$this->ogg" type='video/ogg' data-resolution="288" />

                    <source src="$this->flv" type='video/flash' data-resolution="288" />

                    <source src="$this->HDmp4" type='video/mp4' data-resolution="720p" />

                    <source src="$this->HDogg" type='video/ogg' data-resolution="720p" />

                    <source src="$this->HDflv" type='video/flash' data-resolution="720p" />

            </video>

            <script type="text/javascript">

                    var atlantisVideo = AtlantisJS.Init({

                            videos: [{

                                    id: "video-$brafton_id"

                            }]

                    });

            </script>

EOT;



		}

		//default to videojs, even if none is selected for scripts.

		else{

			$this->embed_code=<<<EOT

			<video id='video-$brafton_id' class='video-js vjs-default-skin'

				controls preload='auto' width="$this->width" height='$this->height'

				poster='$presplash'

				data-setup='{"example_option":true}'>

				<source src="$this->mp4" type='video/mp4' />

				<source src="$this->ogg" type='video/ogg' />

				<source src="$this->flv" type='video/flash' />

			</video>

EOT;

		}	



	}



	/**

	 * Checks if video article already exists in WordPress database. 

	 * Returns post_id or false if no posts are found.

	 * 

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

	 * Updates existing  video article to reflect changes made to client's feed

	 * 

	 * WordPress Reference: http://codex.wordpress.org/Function_Reference/wp_update_post

	 * @param Array $post_exists['post_id', 'post_status']

	 * @param Array $video_article_array 

	 * @return int $post_id 

	 */

	private function update_post( $video_article_array,  $post_exists )

	{

		$video_article_array['ID'] = $post_exists;

		//Makes sure to update articles still in drafts

		if ( $video_article_array['post_status']  == 'draft' ) //make sure publish status is a string

			$video_article_array['edit_date']  = true; 



		//Update the article

		$post_id = wp_update_post( $video_article_array ); 

		return $post_id;

	}

	/**

	 * //Article publish date

	 * @return String $post_date

	 */

	public function format_post_date( $date ){

		//format post date

			$post_date_gmt = strtotime( $date );

			$post_date_gmt = gmdate( 'Y-m-d H:i:s', $post_date_gmt );

			$post_date = get_date_from_gmt( $post_date_gmt );

			

			return $post_date;

	}



	public function insert_video_article( $video_article_array, $brafton_id ) {

		//generate video embed code containing supported video output formats.

		$this->get_video_output( $brafton_id, $this->presplash );



		$video_article_array['post_type'] = $this->post_type; 



		$post_exists = $this->exists( $brafton_id );



		//if article does not exist

		if ( $post_exists  == false )

		{	//add the article to WordPress

			$post_id = wp_insert_post( $video_article_array ); 



			//add custom meta field so we can find the video article again later.

			update_post_meta($post_id, 'brafton_id', $brafton_id );



			//attach the video embed code to the article

			update_post_meta($post_id, 'video_embed_code', $this->embed_code );



		}

		else

		{

			//check if overwrite is set to on

			if ( $this->brafton_options->options['brafton_overwrite'] == 'on' ){

				$post_id = $this->update_post( $video_article_array, $post_exists ); 



				update_post_meta($post_id, 'video_embed_code', $this->embed_code );

			}



		}



		if( is_wp_error( $post_id) )

			brafton_log( array( 'message' => 'Failed to import video with brafton_id: ' . $brafton_id . ' titled: ' . $video_article_array['post_title'] . ". WP could't resolve this error: " . $post_id->get_error_message() ) );

		else

			brafton_log( array( 'message' => 'Successfully imported video with brafton_id: ' . $brafton_id . ' titled: ' . $video_article_array['post_title'] ) );



		return $post_id;

					

	}

}



?>