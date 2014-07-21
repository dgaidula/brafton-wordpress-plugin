<?php
/**
 * @author Brafton Inc.
 * Validator class for brafton wordpress plugin. 
 */

require_once( plugin_dir_path( __FILE__ ) . "/brafton_errors.php" );

class Brafton_Validator {
	/**
	 * validation type
	 * String 
	 */ 
	public $type; 
	/**
	 * Variable being validated.
	 */
	public $value;
	/**
	 * Bool
	 */
	public $valid;

	/**
	 * String
	 */ 
	public $brafton_id; 
	/**
	 * Stack Trace used to add helpful debug information to brafton log message
	 * Array
	 */ 
	public $trace; 

	/**
	 * Log validation results 
	 * Bool $log
	 */
	public $log;
	/**
	 * Determins whether given value is valid.
	 * 
	 * @param $value
	 * @param $type  
	 * @return Bool $valid
	 */
	public function is_found( $value, $type, $log = null, $brafton_id ){
		$this->value = ( gettype( $value ) === 'array' ) ? implode( " ", $value ) : $value;
		$this->type = $type;
		$this->valid = false; 
		$this->log = ( isset( $log ) ) ? $log : true; 
		$this->trace = debug_backtrace();
		switch ( $type ){
			case 'url' :
				$this->validate_url(); 
				break;
			case 'file' :
				$this->validate_file();
			case 'string' :
				$this->validate_string(); 
				break; 
			case 'html' :
				$this->validate_html();
				break;
			case 'xml' :
				$this->validate_xml();
				break; 
			case 'api' :
				$this->validate_api();
				break; 
			case 'user' :
				$this->validate_user();
				break; 
			case 'state' :
				$this->validate_state();
				break;
		}
		return $this->valid;
	}
	/**
	 * Validates a url
	 */
	protected function validate_url(){
		if(  filter_var( $this->value , FILTER_VALIDATE_URL ) ){ 
			if( strpos( $this->value, 'http://' ) !== false || strpos( $this->value, 'https://' ) !== false )
				$this->valid = true; 
		} else {
			if( $this->log )
				brafton_log( array( 'message' => "Invalid url: " . $this->value . " . Validate url called by {$this->trace[1]['class']} :: {$this->trace[1]['function']} " ) );
		}
	} 
	/**
	 * Validates a file location
	 */ 
	protected function validate_file(){
		if(  filter_var( $this->value , FILTER_VALIDATE_URL ) ){ 
			if( strpos( $this->value, 'file://' ) !== false )
				$this->valid = true; 
		} else {
			if( $this->log )
				brafton_log( array( 'message' => "Invalid file: " . $this->value . " . Validate file called by {$this->trace[1]['class']} :: {$this->trace[1]['function']} " ) );
		}
	}
	/**
	 * Validates a given string.
	 */
	protected function validate_string($value ){
		if( isset( $value ) &&  $value !== "" && gettype( $value ) === "string" ) { 
			return true;
		} 
		return false;
	}
	/**
	 * Checks if given xml string is valid. 
	 */
	protected function validate_xml(){
		libxml_use_internal_errors(true);

		$doc = simplexml_load_string( $this->value );
		$xml = explode( "\n", $this->value );

		if( $doc !== false ) {
			$this->valid = true;
		} else{
		    $errors = libxml_get_errors();

		    foreach ( $errors as $error ) {
				if( $this->log )
		        	brafton_log(  array( 'message' => "Invalid xml string. PHP resolved this error: " . display_xml_error( $error, $xml ) . " Validate xml called by {$this->trace[1]['class']} :: {$this->trace[1]['function']}" ) );
		    }

		    libxml_clear_errors();
		}
	}
	/**
	 * Checks if a given string is valid html.
	 */
	protected function validate_html(){
		libxml_use_internal_errors(true);

		$doc = new DOMDocument();
		@$doc::loadHTML( $this->value );

		if( $doc === true ) {
			$this->valid = true;
		} else{
		    $errors = libxml_get_errors();

		    foreach ( $errors as $error ) {
				if( $this->log )
		        	brafton_log(  array( 'message' => "Invalid html string. PHP resolved this error: " . display_xml_error( $error, $xml ) . " Validate html called by {$this->trace[1]['class']} :: {$this->trace[1]['function']}" ) );
		    }

		    libxml_clear_errors();
		}
	}
	/**
	 * Checks if a given api key is valid.
	 */
	protected function validate_api(){
		if(preg_match("/^[a-zA-Z0-9]+$/", $this->value ) == 1) {
			$this->valid = true; 
		} else {
			if( $this->log )
				brafton_log( array( 'message' => 'Provided api key is invalid: ' . $this->value . " Api tokens only include: numbers 0-9, lower and uppercase letters, and hyphens" ) );
		}
	}
	/**
	 * Checks if a given display name matches any registered wordpress users.
	 * 
	 * returns $user_id on successs and false on failure to find user. 
	 * @param String $byline
	 * @return Mixed $user_id
	 */
	protected function validate_user( ){
		//find this blog's users who have authorship rights.
		$blog_id = get_current_blog_id();
	    $args = array(  'blog_id' => $blog_id, 
	                    'orderby' => 'display_name',
	                    'who' => 'authors',
	        );
	    $blogusers = get_users( $args );
	    //Byline field contains only first or last name. 
	    // compare each user with byLine field.
		foreach ( $blogusers as $user ) {
	    	$first_or_last = stripos( $user->display_name, $this->value );

			//we have a direct match.
			if( $user->display_name === $this->value ) { 
				$user_id = $user->ID; 
				$author_set = true; 
				if( $this->log )
					brafton_log( array( 'message' => $this->value . " is a registered " .  get_bloginfo() .  " blog user" ) );
				$this->valid = true; 
			} 
	        //the byLine is just first or last name and this 
	        //substring is found in User display name
			elseif( gettype( $first_or_last ) == 'integer' )
			{
				$user_id = $user->ID; 
				if( $this->log )
					brafton_log( array( 'message' => $this->value . " is a registered " .  get_bloginfo() .  " blog user" ) );
				$this->valid = true; 
			}
		}
		//Return false and log result if logger is not disabled.
		if( ! $this->valid ) { 
			if( $this->log )
				brafton_log( array( 'message' => $this->value . ' display name does not match any registered wordpress users for this blog: ' . get_bloginfo() ) );
			return false;
		}
		return $user_id;
	}
	/**
	 * Checks if post status is draft or live on feed. 
	 */ 
	protected function validate_state(){
		$link = $this->brafton_options->get_article_link( $brafton_id );
		brafton_log( array( 'message' => 'State ' . $this->value . sprintf( ' exists on the <a href="%s" target="_blank">feed</a>', $link ) ) );
	}
}
?>