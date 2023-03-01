<?php

namespace Crontab\Model;

use Think\Model\RelationModel;

class OrderDeliveryModel extends RelationModel {

    protected $tableName = 'orders_delivery';

    protected function _options_filter(&$options) {
        parent::_options_filter($options);
    }

}
