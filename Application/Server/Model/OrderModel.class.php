<?php
namespace Server\Model;
use Think\Model\RelationModel;

class OrderModel extends RelationModel {
	protected $tableName = 'orders';
	
	protected $_link = array(
		'product'=>array(
				'mapping_type' => self::HAS_MANY,
				'class_name' => 'Server/OrderProduct',
				'foreign_key' => array('orders_id'),
				'mapping_key'=>		array('orders_id'),
				'mapping_name' => 'product',
				'relation_deep'=> true,
		),
		'history'=>array(
				'mapping_type' => self::HAS_MANY,
				'class_name' => 'Server/OrderStatusHistory',
				'foreign_key' => array('orders_id'),
				'mapping_key'=>		array('orders_id'),
				'mapping_name' => 'history',
				'relation_deep'=> true,
				'mapping_order'=>'date_added asc'
		),
		'status'=>array(
				'mapping_type' 	=> 	self::HAS_ONE,
				'class_name' 		=> 	'orders_status',
				'foreign_key' 	=> 	array('orders_status_id'),
				'mapping_key'		=>	array('orders_status'),
				'mapping_name' 	=> 	'status',
				'as_fields'			=>	'orders_status_name',
				'mapping_fields'=>	'orders_status_name',
				'condition'			=>	'language_id=1',
		),		
	);
	
}