<?php 
/**
 * Brafton XMLHandler Validator 
 * 
 * Validator class used to provide better log information when field attributes 
 * necessary for specific settings are not found in xml feed.
 */
include_once( plugin_dir_path( __FILE__ ) . '/brafton_errors.php' );
include_once( plugin_dir_path( __FILE__ ) . '/admin/brafton_options.php' );
include_once( plugin_dir_path( __FILE__ ) . '/brafton_validator.php' );
include_once( plugin_dir_path( __FILE__ ) . '../vendors/SampleAPICLientLibrary/NewsItem.php' );
class Brafton_XMLHandler_Validator extends Brafton_Validator {
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

	public function is_attribute( $value, $type, $log = null ){
		$this->value = $value;
		$this->type = $type;
		$this->valid = false; 
		$this->trace = debug_backtrace();
		$this->log = ( isset( $log ) ) ? $log : true; 

		switch ( $type ){
			case 'byline' :
				$user_id = $this->validate_byline();
				return $user_id; 
				break;
			case 'photos' :
				$this->validate_photos();
			case 'categories' :
				$this->validate_html();
				break;
			case 'tags' :
				$this->validate_tags();
				break; 
			case 'keywords' :
				$this->validate_keywords();
				break; 
			case 'htmlMetaKeywords' :
				$this->validate_htmlMetaKeywords();
				break; 
			case 'html' :
				$this->validate_html(); 
				break; 
			case 'text' :
				$this->validate_text();
				break;
		}
		return $this->valid;
	}
	/**
	 * Ensures byline field is enabled when dynamic
	 * authorship is setting is enabled.
	 */ 
	protected function validate_byline(){
		brafton_log( array( 'message' => 'Validating byline ' . $this->value ) );
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
	 * Makes sure images exist on the client's feed 
	 * when images are enabled in Brafton Settings.
	 * @todo
	 */
	protected function validate_photos(){
	}
	/**
	 * Makes sure tags field exists on the client's feed 
	 * when tags as tags are enabled in Brafton Settings.
	 * @todo
	 */
	protected function validate_tags(){

	}

	/**
	 * Makes sure keywords field exists on the client's feed
	 * when keywords as tags are enabled in Brafton Settings.
	 * @todo
	 */
	protected function validate_keywords(){

	}
	/**
	 * Makes sure htmlMetaKeywords field exists on the client's
	 * feed when dynamic cta's are enabled.
	 * @todo
	 */
	protected function validate_htmlMetaKeywords(){

	}
	/**
	 * Makes sure html
	 */ 
	protected function validate_html(){

	}
	/**
	 * Validates text field on the client's feed. 
	 */
	protected function validate_text(){

	}
}
?>