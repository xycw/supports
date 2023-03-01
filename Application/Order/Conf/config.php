<?php

return array(
    //'配置项'=>'配置值'
    'LOAD_EXT_FILE' => 'define',
    'order_status' => array(
    	 '未支付' => '未支付',
    	 '支付成功' => '支付成功',
    	 '支付失败' => '支付失败',
         'Success' => 'Success',
         /* 'approved' => 'approved',
         'Processing' => 'Processing',
         'Pay_processing' => 'Pay_processing',
         'Pay_success' => 'Pay_success',
         'rpapproved' => 'rpapproved',
         'abnormal' => 'abnormal',
         'Declined' => 'Declined',
         'Delivered' => 'Delivered',
         'error' => 'error', */
         'Failure' => 'Failure',
         /* 'fraud' => 'fraud',
         'Pay_fail' => 'Pay_fail',
         'Pay_failure' => 'Pay_failure',
         'Pay_pending' => 'Pay_pending',
         'Payment_failure' => 'Payment_failure',
         'Payment_processing' => 'Payment_processing',
         'Payment_success' => 'Payment_success', */
         'Pending' => 'Pending',
         /* 'Preparing' => 'Preparing',
         'Refused' => 'Refused',
         'rpcanceled' => 'rpcanceled',
         'rpchargeback' => 'rpchargeback',
         'Settled' => 'Settled',
         'Stripe - CVV Failure' => 'Stripe - CVV Failure',
         'unpaid' => 'unpaid',
         'Update' => 'Update',
         'Pay_fail [fashionpay Payment]' => 'Pay_fail [fashionpay Payment]',
         'Pay_success [fashionpay Payment]' => 'Pay_success [fashionpay Payment]',
         'Processing [fashionpay Payment]' => 'Processing [fashionpay Payment]', */
    ),
    'order_status_remark' => array(
        '待处理' => '待处理',
        '付款失败or未付款' => '付款失败/未付款',
        '付款确认中' => '付款确认中',
        '已确认付款' => '已确认付款',
        '待订货' => '待订货',
        '已订货' => '已订货',
        '待发货' => '待发货',
        '部分发货' => '部分发货',
        '已发货' => '已发货',
        '订单取消' => '订单取消',
        '老订单' => '老订单',
        '拒付' => '拒付',
    ),
    'logistics_status' => array(
        '1' => '暂无记录',
        '2' => '在途中',
        '3' => '派送中',
        '4' => '已签收',
        '5' => '拒收',
        '6' => '疑难件',
        '7' => '退回'
    ),
    'customer_feedback' => array(
        '' => '无反馈',
        '满意' => '满意',
        '不满意' => '不满意',
        '部分退款' => '部分退款',
        '全额退款' => '全额退款',
        '拒付' => '拒付',
    ),
    'express_delivery' => array(
        'shunfeng' => '顺丰',
        'shentong' => '申通',
        'guotong' => '国通'
    ),
    'data_sms_payment_notice' => array(
        '0' => '未发送',
        '1' => '已发送',
    ),
    'payment_methods' => array(
        'Cash:moneytransfers' => 'Cash:moneytransfers',
        'Credit Card:security_pingpong' => 'Credit Card:乒乓',
        'Credit Card:xborderpay' => 'Credit Card:xborderpay',
        'Credit Card:AWX' => 'Credit Card:AWX',
        'Credit Card:pacypay' => 'Credit Card:Pacypay',
        'Credit Card:gateway' => 'Credit Card:Gateway',
        'Credit Card:cardpay' => 'Credit Card:Cardpay',
        'Credit Card:deepsea' => 'Credit Card:Deep Sea'
    ),
    'data_is_send_from_manufacturer' => array(
        0 => '赣州发国外',
        1 => '厂家发国外'
    ),
    'shipping_status' => array(
        '在途中' => '在途中',
        '派送中' => '派送中',
        '已签收' => '已签收',
        '拒收' => '拒收',
        '疑难件' => '疑难件',
        '退回' => '退回'
    ),
    'delivery_type' => array(
        'EMS' => 'EMS',
        'DHL' => 'DHL',
        'UPS' => 'UPS',
        'TNT' => 'TNT',
        'FEDEX' => 'FEDEX',
        'DMS' => 'DMS',
        'CHINAPOST' => 'CHINAPOST',
        'HKPOST' => 'HKPOST',
        'USPS' => 'USPS',
        'ZDZX' => 'ZDZX',
        'ROYALMAIL' => 'ROYALMAIL',
        'YODEL' => 'YODEL',
        'POSTNL' => 'POSTNL',
        'OTHER' => 'OTHER',


    ),
    'status_switch' => array(
        '~approved~i' => '付款确认中',
        '~success~i' => '付款确认中',
        '~^pending~i' => '付款确认中',
        '~processing~i' => '付款确认中',  //原：待订货
    )
);
