<?php

namespace Crontab\Model;

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
    }

}
