<?php
/**
 * @author Brafton Inc.
 * Validator class for brafton wordpress plugin
 */

require_once( plugin_dir_path() . "brafton_errors.php" );

class Brafton_Validator {
	/**
	 * Type of thing to be validated
	 * $string 
	 */ 
	public $type; 
	/**
	 * Value being validated.
	 */
	public $value;
	/**
	 * Bool
	 */
	public $valid; 
	/**
	 * Array
	 */ 
	public $trace; 
	/**
	 * Determins whether given value is valid.
	 * 
	 * @param $value
	 * @param $type  
	 * @return Bool $valid
	 */
	public function is_valid( $value, $type ){
		$this->value = $value;
		$this->type = $type;
		$this->valid = false; 
		$this->trace = debug_backtrace();

		switch ( $type ){
			case 'url' :
				$this->validate_url; 
				break;
			case 'string' :
				$this->validate_string; 
				break; 
			case 'html' :
				$this->validate_html;
				break;
			case 'xml' :
				$this->validate_xml;
				break; 
			case 'api' :
				$this->validate_api;
				break; 
			case 'user' :
				$this->validate_user;
				break; 
		}
		return $this->valid;
	}
	/**
	 * Validates a url
	 */
	private function validate_url(){
		if( strpos( $this->value, 'http://' ) !== false || strpos( $this->value, 'https://' ) !== false ){ 
			$this->valid = true; 
		} else {
			brafton_log( array( 'message' => "Invalid url: " . $this->value . " . Validate url called by {$this->trace[1]['class']} :: {$this->trace[1]['function']} " ) );
		}
	} 
	/**
	 * Validates a given string.
	 */
	private function validate_string(){
		if( gettype( $this->value ) === "string" ) { 
			$this->valid = true;
		} else{
			brafton_log( array( 'message' => 'Invalid string: ' . $this->value . ". Validate string called by {$this->trace[1]['class']} :: {$this->trace[1]['function']}" ) );
		}	 
	}
	/**
	 * Checks if given xml string is valid. 
	 */
	private function validate_xml(){
		libxml_use_internal_errors(true);

		$doc = simplexml_load_string( $this->value );
		$xml = explode( "\n", $this->value );

		if( $doc !== false ) {
			$this->valid = true;
		} else{
		    $errors = libxml_get_errors();

		    foreach ( $errors as $error ) {
		        brafton_log(  array( 'message' => "Invalid xml string. PHP resolved this error: " . display_xml_error( $error, $xml ) . " Validate xml called by {$this->trace[1]['class']} :: {$this->trace[1]['function']}" ) );
		    }

		    libxml_clear_errors();
		}
	}
	/**
	 * Checks if a given string is valid html.
	 */
	private function validate_html(){
		libxml_use_internal_errors(true);

		$doc = new DOMDocument();
		$doc::loadHTML( $this->value );

		if( $doc === true ) {
			$this->valid = true;
		} else{
		    $errors = libxml_get_errors();

		    foreach ( $errors as $error ) {
		        brafton_log(  array( 'message' => "Invalid html string. PHP resolved this error: " . display_xml_error( $error, $xml ) . " Validate html called by {$this->trace[1]['class']} :: {$this->trace[1]['function']}" ) );
		    }

		    libxml_clear_errors();
		}
	}
	/**
	 * Checks if a given api key is valid.
	 */
	private function validate_api(){
		if(preg_match("/^[a-zA-Z0-9]+$/", $this->value ) == 1) {
			$this->valid = true; 
		} else {
			brafton_log( array( 'message' => 'Provided api key is invalid: ' . $this->value . " Api tokens only include: numbers 0-9, lower and uppercase letters, and hyphens" ) );
		}
	}
	/**
	 * Checks if a given display name matches any registered wordpress users.
	 */
	private function validate_user(){
		$blogusers = get_users( array( 'fields' => array( 'display_name' ) ) );
		foreach ( $blogusers as $user ) {
			if( $user->display_name = $this->value ) { 
				$this->valid = true; 
			} else {
				brafton_log( array( 'message' => $this->value . ' display name does not match any registered wordpress users for this blog: ' . get_bloginfo() . " Validate user called by {$this->trace[1]['class']} :: {$this->trace[1]['function']}" ) );
			}
		}
	}
}
?>