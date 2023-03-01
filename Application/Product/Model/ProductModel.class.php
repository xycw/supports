<?php
namespace Product\Model;

use Think\Model\RelationModel;

class ProductModel extends RelationModel {
    
    protected $tableName = 'products';
    
    protected $_link = array(
        'detail' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'Product/ProductDetail',
            'foreign_key' => array('product_id'),
            'mapping_key' => array('product_id'),
            'mapping_name' => 'detail',		
            'mapping_order'=>'language_code asc',
        )
    );
    
    public function __construct($name='',$tablePrefix='',$connection='') {
        parent::__construct($name, $tablePrefix, $connection);
        if(I('language_code', false)){
            $language_code = explode(',', I('language_code'));
            $code = array();
            foreach($language_code as $entry){
                if(preg_match('~^\w+$~', $entry)){
                    $code[] = $entry;
                }
            }
            if(sizeof($code))
                $this->_link['detail']['condition'] .= ' language_code in(\''. implode('\',\'', $code).'\')';
        }  
    }
}

