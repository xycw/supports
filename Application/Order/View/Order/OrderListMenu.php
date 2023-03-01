<div class="nav-search">
    <ul class="list-inline">
        <li><a <?php if (CONTROLLER_NAME != 'ExpressDelivery' && I('order_status_remark', '') == '' && I('is_received', '') === '' && I('is_rush', '') == '' && I('order_status_press', '') == '') echo 'class="seleted" ' ?> href="<?php echo U('Order/Order/list') ?>">所有订单</a></li>
        <li><a <?php if (I('order_status_remark', '') == '待处理') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('order_status_remark' => '待处理')) ?>">待处理<span class="badge"><?php echo $num_dcl ?></span></a></li>
        <li><a <?php if (I('order_status_remark', '') == '付款失败or未付款') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('order_status_remark' => '付款失败or未付款')) ?>">付款失败/未付款<span class="badge"><?php echo $num_fksb ?></span></a></li>
        <li><a <?php if (I('order_status_remark', '') == '付款确认中') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('order_status_remark' => '付款确认中')) ?>">付款确认中<span class="badge"><?php echo $num_fkqrz ?></span></a></li>
        <li><a <?php if (I('order_status_remark', '') == '已确认付款') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('order_status_remark' => '已确认付款')) ?>">已确认付款<span class="badge"><?php echo $num_yqrfk ?></span></a></li>
        <li><a <?php if (I('order_status_remark', '') == '待订货') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('order_status_remark' => '待订货')) ?>">待订货<span class="badge"><?php echo $num_ddh ?></span></a></li>
        <li><a <?php if (I('order_status_remark', '') == '已订货') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('order_status_remark' => '已订货')) ?>">已订货<span class="badge"><?php echo $num_ydh ?></span></a></li>
        <li><a <?php if (I('order_status_remark', '') == '待发货') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('order_status_remark' => '待发货')) ?>">待发货<span class="badge"><?php echo $num_dfh ?></span></a></li>
        <li><a <?php if (I('order_status_remark', '') == '部分发货') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('order_status_remark' => '部分发货')) ?>">部分发货<span class="badge"><?php echo $num_bffh ?></span></a></li>
        <li><a <?php if (I('order_status_remark', '') == '已发货') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('order_status_remark' => '已发货')) ?>">已发货(完单)<span class="badge"><?php echo $num_yfh ?></span></a></li>
        <li><a <?php if (I('is_paid', null) === '1') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('is_paid' => '1')) ?>">已付款订单<span class="badge"><?php echo $num_is_paid ?></span></a></li>
        <li><a <?php if (CONTROLLER_NAME == 'ExpressDelivery') echo 'class="seleted" ' ?>href="<?php echo U('Order/ExpressDelivery/list', array('is_received' => '0')) ?>">未签收<span class="badge"><?php echo $num_wqs ?></span></a></li>
        <li><a <?php if (I('is_rush', '') == '1') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('is_rush' => '1')) ?>">急单<span class="badge"><?php echo $num_jd ?></span></a></li>
        <li><a <?php if (I('customer_feedback', '') == '拒付') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('customer_feedback' => '拒付')) ?>">拒付<span class="badge"><?php echo $num_jf ?></span></a></li>
        <li><a <?php if (I('order_status_press', '') == '支付失败需催款') echo 'class="seleted" ' ?>href="<?php echo U('Order/Order/list', array('order_status_press' => '支付失败需催款')) ?>">支付失败需催款</a></li>
    </ul>
</div>