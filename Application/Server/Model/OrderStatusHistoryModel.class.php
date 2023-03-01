<?php
namespace Server\Model;
use Think\Model\RelationModel;

class OrderStatusHistoryModel extends RelationModel {
	protected $tableName = 'orders_status_history';
	
	protected $_link = array(
		'status_name'=>array(
				'mapping_type' 	=> 	self::HAS_ONE,
				'class_name' 		=> 	'orders_status',
				'foreign_key' 	=> 	array('orders_status_id'),
				'mapping_key'		=>	array('orders_status_id'),
				'mapping_name' 	=> 	'status_name',
				'as_fields'			=>	'orders_status_name',
				'mapping_fields'=>	'orders_status_name',
				'condition'			=>	'language_id=1',
		),		
	);
	
}