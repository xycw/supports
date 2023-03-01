<?php
namespace Customers\Model;
use Think\Model\RelationModel;
class CustomersModel extends RelationModel {
    protected $tableName = 'customers';
    protected $_link = array(
        'customers_basket' => array(
            'mapping_type' => self::HAS_MANY,
            'mapping_key' => array('customers_id', 'site_id'),
            'class_name' => 'CustomersBasket',
            'foreign_key' => array('customers_id', 'site_id'),
            'mapping_name' => 'customers_basket',
            'mapping_order' => 'status desc,customers_basket_date_added desc'
        ),
        'customers_remark' => array(
            'mapping_type' => self::HAS_ONE,
            'mapping_key' => array('customers_id', 'site_id'),
            'class_name' => 'customers_remark',
            'foreign_key' => array('customers_id', 'site_id'),
            'mapping_name' => 'customers_remark',
            'mapping_fields' => 'num_notify_cart,last_notify_cart',
            'as_fields' => 'num_notify_cart,last_notify_cart'
        ),
        'site' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'site',
            'foreign_key' => 'site_id',
            'mapping_key' => 'site_id',
            'mapping_name' => 'site',
            'as_fields' => 'site_name,site_index'
        ),
        'address_book' => array(
            'mapping_type' => self::HAS_MANY,
            'mapping_key' => array('site_id','customers_id'),
            'class_name' => 'Server/AddressBook',
            'foreign_key' => array('site_id','customers_id'),
            'mapping_name' => 'address_book',
            'mapping_order' => 'address_book_id'
        ),
        'default_address' => array(
            'mapping_type' => self::HAS_ONE,
            'mapping_key' => array('site_id','customers_id'),
            'class_name' => 'address_book',
            'foreign_key' => array('site_id','customers_id'),
            'as_fields' => 'entry_firstname,entry_lastname,entry_street_address,entry_postcode,entry_city,entry_state,entry_country',
        ),        
    );

    protected function _after_find(&$result, $options) {
        parent::_after_find($result, $options);

        $num_valid = 0;
        if (is_null($result['customers_basket']) == false) {
            foreach ($result['customers_basket'] as $customers_basket_entry) {
                $num_valid += $customers_basket_entry['status'];
            }
        }
        $result['num_basket_vaild'] = $num_valid;
    }

    // 查询数据集成功后的回调方法
    protected function _after_select(&$result,$options) {
        parent::_after_select($result, $options);
        foreach ($resultSet as $k => $customers_entry) {
            if (empty($customers_entry['customers_basket']) == false) {
                $num_valid = 0;
                foreach ($customers_entry['customers_basket'] as $customers_basket_entry) {
                    $num_valid += $customers_basket_entry['status'];
                }
                $resultSet[$k]['num_basket_vaild'] = $num_valid;
            }
        }        
    }
    
    protected function _before_insert(&$data,$options) {
        if($data['customers_basket']!=null){
            D('customers_basket')->where(array(array('site_id'=>$data['site_id'], 'customers_id'=>$data['customers_id'])))->save(array('status'=>0));
        }
    }
}
