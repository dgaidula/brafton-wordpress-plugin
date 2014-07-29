<?php
/**
 * @package SamplePHPApi
 */
include_once( plugin_dir_path( __FILE__ ) . '../../src/brafton_article_validator.php' );

/**
 * class NewsCategory models a category object and has a static method to parse 
 * a set of categories and return them as a collection of category objects
 * @package SamplePHPApi
 */
class NewsCategory {
    
    /**
     * @var int
     */
    private $id;
    /**
     * @var String
     */
    private $name;
    private $validator;

    public $brafton_id; 
    function __construct(){
        $this->validator = new Brafton_Article_Validator();
    }
    /**
     * @param String $url
     * @param String $brafton_id
     * @return array[int]Category
     */
    public static function getCategories($url, $brafton_id = null ){
        $xh = new XMLHandler($url);
        $nl = $xh->getNodes("category");
        $catList = array();
        foreach ($nl as $n) {
            $c = new NewsCategory();
            $c->brafton_id = ( isset( $brafton_id ) ) ? $brafton_id : null; 

            $c->id = $n->getElementsByTagName("id")->item(0)->textContent;
            $c->name = $n->getElementsByTagName("name")->item(0)->textContent;
            $c->validator->is_found( $c->name, 'categories', null, $c->brafton_id  );

            $catList[]=$c;
        }
        
        // if $caList is empty article has no categories
        return $catList;
    }
    
    public function getName(){
        //validate
    	return $this->name;
    }
    
    public function getID(){
    	return $this->id;
    }
}
?>