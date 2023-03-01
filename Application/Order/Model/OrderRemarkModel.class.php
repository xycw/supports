<?php

namespace Order\Model;

use Think\Model\RelationModel;

class OrderRemarkModel extends RelationModel {

    protected $tableName = 'orders_remark';
    
    protected $_link = array(
        'last_operator' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'users',
            'foreign_key' => 'user_id',
            'mapping_key' => 'last_operator',
            'mapping_name' => 'user',
            'as_fields' => 'username,chinese_name'
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
                    } else
                        $options['where']['_complex'] = array(
                            '_logic' => 'AND',
                            $table_name.'site_id' => $options[$table_name.'where']['site_id'],
                            '_complex' => array(
                                $table_name.'site_id' => array('IN', $sql, 'exp'),
                            ),
                        );
                    unset($options['where']['site_id']);
                } else
                    $options['where'] = array_merge($options['where'], array($table_name.'site_id' => array('IN', $sql, 'exp')));
            }else {
                $options['where'] = array($table_name.'site_id' => array('IN', $sql, 'exp'));
            }
        }
    }

}
