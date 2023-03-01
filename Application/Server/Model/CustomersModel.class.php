<?php
namespace Server\Model;
use Think\Model\RelationModel;

class CustomersModel extends RelationModel {
	protected $tableName = 'customers';
	
	protected $_link = array(
		'customers_basket'=>array(
		      'mapping_type'  => self::HAS_MANY,
		      'mapping_key'   =>		array('customers_id'),
		      'class_name'    => 'Server/CustomersBasket',
    		  'foreign_key'   => array('customers_id'),
			  'mapping_name'  => 'customers_basket',
			  'relation_deep' => true,
		),
	);
	
}