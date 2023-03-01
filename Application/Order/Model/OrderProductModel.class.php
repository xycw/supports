<?php
namespace Order\Model;

use Think\Model\RelationModel;

class OrderProductModel extends RelationModel {

    protected $tableName = 'orders_products';
    protected $_link = array(
        'orders_products_remark' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'orders_products_remark',
            'foreign_key' => array('site_id', 'orders_products_id'),
            'mapping_key' => array('site_id', 'orders_products_id'),
            'mapping_name' => 'orders_products_remark'
        ),
        'attribute' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'orders_products_attributes',
            'foreign_key' => array('site_id', 'orders_products_id','orders_id'),
            'mapping_key' => array('site_id', 'orders_products_id','orders_id'),
            'mapping_fields' => array('orders_products_attributes_id','products_options', 'products_options_values'),
            'mapping_name' => 'attribute',
        ),
    );
}
