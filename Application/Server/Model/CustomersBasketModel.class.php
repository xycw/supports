<?php
namespace Server\Model;
use Think\Model\RelationModel;

class CustomersModel extends RelationModel {
	protected $tableName = 'customers_basket_attributes';
	
	protected $_link = array(
		'customers_basket_attributes'=>array(
		      'mapping_type' => self::HAS_MANY,
		      'class_name'   => 'Server/CustomersBasketAttributes',
              'mapping_key'  => array('customers_id,products_id'),
		      'foreign_key'  => array('customers_id,products_id'),
              'mapping_name' => 'customers_basket_attributes',
			  'relation_deep'=> true,
		),
	);
	
}