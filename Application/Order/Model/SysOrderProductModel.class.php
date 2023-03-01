<?php
namespace Order\Model;

use Think\Model\RelationModel;

class SysOrderProductModel extends RelationModel {

    protected $tableName = 'sys_orders_products';
    protected $_link = array(
        'orders_products_remark' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'sys_orders_products_remark',
            'foreign_key' => array('site_id', 'orders_products_id'),
            'mapping_key' => array('site_id', 'orders_products_id'),
            'mapping_name' => 'orders_products_remark'
        ),
        'attribute' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'sys_orders_products_attributes',
            'foreign_key' => array('site_id', 'orders_products_id','orders_id'),
            'mapping_key' => array('site_id', 'orders_products_id','orders_id'),
            'mapping_fields' => array('products_options', 'products_options_values'),
            'mapping_name' => 'attribute',
        ),
    );
}
