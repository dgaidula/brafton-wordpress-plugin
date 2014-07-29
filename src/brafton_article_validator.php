<?php 
/**
 * Brafton XMLHandler Validator 
 * 
 * Validator class used to provide better log information when field attributes 
 * necessary for specific settings are not found in xml feed.
 * 
 * Also helps reduce article settings complexity by dynamically enabling 
 * supported features if feed values are set properly.
 */
include_once( plugin_dir_path( __FILE__ ) . '/brafton_errors.php' );
include_once( plugin_dir_path( __FILE__ ) . '/admin/brafton_options.php' );
include_once( plugin_dir_path( __FILE__ ) . '/brafton_validator.php' );
include_once( plugin_dir_path( __FILE__ ) . '../vendors/SampleAPICLientLibrary/NewsItem.php' );
include_once( plugin_dir_path( __FILE__ ) . '../vendors/SampleAPICLientLibrary/NewsCategory.php' );
include_once( plugin_dir_path( __FILE__ ) . '../vendors/SampleAPICLientLibrary/PhotoInstance.php' );
class Brafton_Article_Validator extends Brafton_Validator {
	/**
	 * Brafton settings.
	 */
	public $brafton_options;
	/**
	 * Brafton feed node.
	 */

	function __construct(){
        $this->brafton_options = Brafton_options::get_instance();
	}
	/**
	 * This is the only cla
	 */ 
	public function is_found( $value, $type, $log = null, $brafton_id = null ){
		$this->value = $value;
		$this->type = $type;
		$this->valid = false; 
		$this->trace = debug_backtrace();
		$this->log = ( isset( $log ) ) ? $log : true; 
		$this->brafton_id = ( isset( $brafton_id ) ) ? $brafton_id : null;
		switch ( $type ){
			case 'byline' :
				$user_id = $this->validate_byline();
				return $user_id; 
				break;
			case 'photo' :
				$this->validate_photos();
				break;
			case 'categories' :
				$this->validate_tags();
				break;
			case 'tags' :
				$this->validate_tags();
				break; 
			case 'keywords' :
				$this->validate_tags();
				break; 
			case 'htmlMetaKeywords' :
				$this->validate_string_text();
				break; 
			case 'html' :
				$this->validate_html(); 
				break; 
			case 'text' :
				$this->validate_text();
				break;
			case 'headline' : 
				$this->validate_string_text();
				break;
		}		
		return $this->valid;
	}
	/**
	 * Validates byline field when dynamic
	 * authorship setting is enabled.
	 */ 
	protected function validate_byline(){
		if( $this->brafton_options->options['enable_dynamic_authorship'] === 'on' && $this->value === '' ) {
			if( $this->log )
				brafton_log( array( 'message' => 'Dynamic authorship is enabled however, byline field is not active.' ) );
		} elseif(  $this->value !== "" ){ 
			$user_id = $this->validate_user( $this->value );
			return $user_id;  
		} else {
			if( $this->log )
				brafton_log( array( 'message' => 'Dynamic authorship is off and byline field is disabled.' ) );
		} 
	}
	/**
	 * Validates photos field when images 
	 * setting is enabled.
	 * @todo
	 */
	protected function validate_photos(){
		$link = $this->brafton_options->get_article_link( $this->brafton_id ) . '/photos';
		if( $this->validate_string( $this->value ) ){
			$this->valid = true;
			if( $this->log )
				brafton_log( array( 'message' => sprintf( '%s Photo <a href="%s" target="_blank">Instance</a> found on the <a href="%s" target="_blank">feed</a>', $this->type, $this->value, $link ) ) );
		} else {
			if( $this->log ) 
				brafton_log( array( 'message' =>  sprintf( 'Photo not found on the <a href="%s" target="_blank">feed</a> %s', $link, $this->value ) ) );
		}
	}

	/**
	 * Determins whether to use catetgories, tags, or keywords as tags
	 * @return String $this->type
	 */
	protected function validate_tags(){
		$link = $this->brafton_options->get_article_link( $this->brafton_id );

		$option = $this->brafton_options->options['brafton_enable_tags'];
		if( $option === $this->type ) {
				$tag_option_exists_on_feed = $this->validate_string( $this->value );
			if( $tag_option_exists_on_feed ) { 
				brafton_log( array( 'message' => sprintf( 'Using %s as tags for this <a href="%s" target="_blank">article.</a>', $this->type, $link ) ) );
				return $this->type;
			} elseif( !$tag_option_exists_on_feed ) { 
				brafton_log( array( 'message' => sprintf( 'Brafton %s as tags is enabled but there are no %s for this <a href="%s" target="_blank">article - %s </a>.', $option, $this->type, $link, $this->value ) ) );
				return false;
			}else { 
				brafton_log( array('message' => 'Tags setting disabled.') );
				return false;
			}
		}
		
	}
}
?>