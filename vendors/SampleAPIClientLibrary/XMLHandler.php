<?php
/**
 * @package SamplePHPApi
 */
/**
 * class XMLHandler is a helper class to parse the XML feed data
 * @package SamplePHPApi
 */

include_once( plugin_dir_path( __FILE__ ) . '../../src/brafton_errors.php' );
class XMLHandler {
	/** @var Document */
	private $doc;

  	static $ch;
  	public $count = 0;
	

		/**
	 * @param String $url
	 * @return XMLHandler
	 */
	function __construct($url){
		$this->count++;

		if(!preg_match('/^http:\/\//', $url)){
	      $url = 'file://' . $url;
	    }
		$this->doc = new DOMDocument();
	  
	  	//we need curl to execute temp file requests on archive upload. 
	  	if( function_exists('curl_init') ){
	  		if(!isset($ch)){
		      $ch = curl_init();
		    }
		    curl_setopt ($ch, CURLOPT_URL, $url);
		    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 60);
		    $feed_string = curl_exec($ch);
		    $error = curl_error( $ch );
		 
		    if( $error )
					brafton_log( array( 'message' => 'Failed to execute external web rquest: ' . $url . '. cURL returned error: ' . $error ) );
			else
				brafton_log( array( 'message' => 'Successfully executed external web request: ' . $url ) );

	  	}
	  	else {
	  		//load wp_http class   
			if( !class_exists( 'WP_Http' ) )
			  	include_once( ABSPATH . WPINC . '/class-http.php' );

		    $request = new WP_Http; 
		    $result = $request->request( $url );

		    $error = $result->get_error_message();
		    if( $error )
				brafton_log( array( 'message' => 'Failed to execute external web rquest: ' . $url . '. WP_HTTP returned error: ' . $error ) );
			else
				brafton_log( array( 'message' => 'Successfully executed external web request: ' . $url ) );

		    $feed_string = $result['body'];

	  	}  
    
		if(!$this->doc->loadXML($feed_string)) {
			throw new XMLLoadException($url);
			echo 'doc isnot xml file';
		}
		
	}
  
  /*
  public static function CURL_pull($url,$timeout) { // use CURL library to fetch remote file
    print 'called getfile '; flush();

    print ' execced curl ';
    if ( curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200 ) {
      throw new Exception('Return Status: '.curl_getinfo($ch,CURLINFO_HTTP_CODE).', please try again after a while, could not load URL :'.$url);
          return false;
    } else { return $file_contents; }
  }*/

	/**
	 * @param String $element
	 * @return String
	 */
	function getValue($element){
		$result = $this->doc->getElementsByTagName($element);
		if($result->length != null) return $this->doc->getElementsByTagName($element)->item(0)->nodeValue;
		else return null;
	}

	/**
	 * @param String $element
	 * @return String
	 */
	function getHrefValue($element){
		if( $element == "news" && $this->count == 1 && $this->doc->getElementsByTagName($element)->length == 0  ) {
			brafton_log( array( 'message' => "Your brafton feed doesn't appear to have news articles. Make sure your Api key and product are valid" ) );
			$log = get_option('brafton_error_log' );
			echo "Something went wrong. Try enabling brafton errors.";
			exit;
		}


		return $this->doc->getElementsByTagName($element)->item(0)->getAttribute('href');
	}

	/**
	 * @param String $element
	 * @param String $attribute
	 * @return String
	 */
	function getAttributeValue($element, $attribute){
		return $this->doc->getElementsByTagName($element)->item(0)->getAttribute($attribute);
	}

	/**
	 * @param String $element
	 * @return DOMNodeList
	 */
	function getNodes($element){
		return $this->doc->getElementsByTagName($element);
	}

	/**
	 * @param String $element
	 * @return String
	 */
	public static function getSetting($element){
		$xh = new XMLHandler("../Classes/settings.xml");
		return $xh->getValue($element);
	}
}

/**
 * Custom Exception XMLException
 * @package SamplePHPApi
 */
class XMLException extends Exception{}

/**
 * Custom Exception XMLLoadException thrown if an XML source file is not found
 * @package SamplePHPApi
 */
class XMLLoadException extends XMLException{
	function __construct($message, $code=""){
		$this->message = "Could not load URL: " . $message;
	}
}

/**
 * Custom Exception XMLNodeException thrown if a required XML element is not found
 * @package SamplePHPApi
 */
class XMLNodeException extends XMLException{
	function __construct($message, $code=""){
		$this->message = "Could not find XMLNode: " . $message;
	}
}
?>
