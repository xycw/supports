<?php
namespace Server\Model;
use Think\Model\RelationModel;

class OrderProductModel extends RelationModel {
	protected $tableName = 'orders_products';
	
	protected $_link = array(
			'attribute'=>array(
					'mapping_type' => self::HAS_MANY,
					'class_name' => 'orders_products_attributes',
					'foreign_key' => array('orders_products_id'),
					'mapping_key'=>		array('orders_products_id'),
					'mapping_name' => 'attribute',
			)
	);
}