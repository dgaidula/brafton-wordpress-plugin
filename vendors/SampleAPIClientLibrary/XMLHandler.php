<?php
/**
 * @package SamplePHPApi
 */
/**
 * class XMLHandler is a helper class to parse the XML feed data
 * @package SamplePHPApi
 */
include_once( plugin_dir_path( __FILE__ ) . '../../src/brafton_errors.php' );
include_once( plugin_dir_path( __FILE__ ) . '../../src/brafton_article_validator.php' );
class XMLHandler {
	/** @var Document */
	private $doc;
  	static $ch;
  	public $count = 0;
	public $validate;
		/**
	 * @param String $url
	 * @return XMLHandler
	 */
	function __construct($url){

		$this->validate = new Brafton_Article_Validator();
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
	function getValue( $element ){

		$result = $this->doc->getElementsByTagName( $element );
		$value = '';
		if( $result->length != null ){
			$value = $this->doc->getElementsByTagName( $element )->item( 0 )->nodeValue;
		} 
		#brafton_log( array( 'message' => 'Article '  . $element . ' : ' . $value ) );

		//Add some helpful error reporting with validator class.
		#$this->validate->is_found( $value, $element );

		if( $result->length == null )
			return null;
		else
			return $this->doc->getElementsByTagName( $element )->item( 0 )->nodeValue;
	}
	/**
	 * @param String $element
	 * @return String
	 */
	function getHrefValue( $element ){
		if( $element == "news" && $this->count == 1 && $this->doc->getElementsByTagName($element)->length == 0  ) {
			#brafton_log( array( 'message' => "Your brafton feed doesn't appear to have news articles. Make sure your Api key and product are valid" ) );
			echo "Either provided XML file or api key  invalid. Try enabling brafton errors.";
			exit;
		}
		$value = $this->doc->getElementsByTagName($element)->item(0)->getAttribute('href');
		brafton_log( array( 'message' => "Href value found on feed : " . $element . " value: "  . $value ) );
		return $value; 
	}
	/**
	 * @param String $element
	 * @param String $attribute
	 * @return String
	 */
	function getAttributeValue($element, $attribute){
		$value = $this->doc->getElementsByTagName($element)->item(0)->getAttribute($attribute);
		
		brafton_log( array( 'message' => 'Attribute found on feed '  . $attribute . ' : value - ' . $value  . ' element : ' . $element ) );
		return $value; 
	}
	/**
	 * @param String $element
	 * @return DOMNodeList
	 */
	function getNodes($element){
		return $this->doc->getElementsByTagName($element);
		#$this->validate->is_found( $value, $element );
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