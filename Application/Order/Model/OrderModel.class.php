<?php

namespace Order\Model;

use Think\Model\RelationModel;

class OrderModel extends RelationModel {

    protected $tableName = 'orders';
    protected $_link = array(
        'product' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'Order/OrderProduct',
            'foreign_key' => array('site_id', 'orders_id'),
            'mapping_key' => array('site_id', 'orders_id'),
            'mapping_name' => 'product',
            'relation_deep' => true,			
            'mapping_order'=>'orders_products_id asc',
        ),  
        'order_remark' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'Order/OrderRemark',
            'foreign_key' => array('site_id', 'orders_id'),
            'mapping_key' => array('site_id', 'orders_id'),
            'mapping_name' => 'order_remark',
            'as_fields' => 'orders_remark_id,order_no,order_pay,order_pay_rmb,internal_shipping_cost,express_cost,shipping_cost,rp_no,other_cost,other_cost_remark,shipping_no,shipping_status,express_no,order_remark,order_status_remark,num_zhuidan,num_fahuotongzhi,customer_feedback,date_require,date_expected_supplier_send,date_send,sms_payment_notice,manufacturers_id,is_send_from_manufacturer,is_rush_order,num_queren,email_logs,payment_status,last_modify,username,chinese_name,send_status,logistics_remark,new_delivery_address',
            'relation_deep' => true,
        ),
        'site' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'site',
            'foreign_key' => 'site_id',
            'mapping_key' => 'site_id',
            'mapping_name' => 'site',
            'as_fields' => 'site_name,site_index,img_url,order_no_prefix,site_index_spare,email_data,customer_service_name,is_sale,system_area,system_depart,type,system_cms'
        ),
        'history' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'orders_status_history',
            'foreign_key' => array('site_id', 'orders_id'),
            'mapping_key' => array('site_id', 'orders_id'),
            'mapping_name' => 'history',
            'mapping_order' => 'date_added asc'
        ),
        'delivery' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'Order/OrderDelivery',
            'foreign_key' => array('site_id', 'orders_id'),
            'mapping_key' => array('site_id', 'orders_id'),
            'mapping_name' => 'delivery',
            'as_fields' => '*'
        ),
    );

    protected function _options_filter(&$options) {
        parent::_options_filter($options);
        //非管理员 限制其业务权限
        if(!in_array(session(C('USER_INFO').'.profile_id'), array(1,4,6))){
            $sql = D('users_to_site')->where(array('user_id' => session(C('USER_INFO') . '.user_id')))->field('site_id')->select(false);
            $table_name = (empty($options['alias'])==false?$options['alias']:$this->getTableName()).'.';
            if (isset($options['where'])) {
                if (isset($options['where'][$table_name.'site_id'])) {
                    if (isset($options['where']['_complex'])) {
                            $options['where']['_complex'] = array(
                                $options['where']['_complex'],
                                '_complex' => array(
                                    'site_id' => $options['where'][$table_name.'site_id'],
                                    '_complex' => array(
                                        $table_name.'site_id' => array('IN', $sql, 'exp'),
                                    ),
                                )
                            );
                    } else{
                        $options['where']['_complex'] = array(
                            '_logic' => 'AND',
                            $table_name.'site_id' => $options['where'][$table_name.'site_id'],
                            '_complex' => array(
                                $table_name.'site_id' => array('IN', $sql, 'exp'),
                            ),
                        );
                    }
                    unset($options['where']['site_id']);
                } else
                    $options['where'] = array_merge($options['where'], array($table_name.'site_id' => array('IN', $sql, 'exp')));
            }else {
                $options['where'] = array($table_name.'site_id' => array('IN', $sql, 'exp'));
            }
        }
    }    
    
    protected function _after_select(&$result,$options) {
        parent::_after_select($result,$options);
        foreach($result as $i=>$entry){
            if(!empty($entry['new_delivery_address'])){
                $new_delivery_address = json_decode($entry['new_delivery_address'], true);
                foreach($new_delivery_address as $k=>$v){
                    $result[$i][$k] = $v;
                }
            }
        }
    }
    
    protected function _after_find(&$result,$options) {
        parent::_after_find($result,$options);
        if(!empty($result['new_delivery_address'])){
            $new_delivery_address = json_decode($result['new_delivery_address'], true);
            foreach($new_delivery_address as $k=>$v){
                $result[$k] = $v;
            }
        }
    }    
}
