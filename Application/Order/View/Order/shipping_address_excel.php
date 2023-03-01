<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">E邮宝表格下载(未发货订单客户地址Excel列表)</h4>
</div>
<div class="modal-body">
    <form action="<?php echo U('Order/Order/shipping_address_excel') ?>" method="post" target="__blank">
        <input type="hidden" name="action" value="">
        <div class="form-inline mb5">
            <div class="form-group">
                <label>网站</label>
                <select name="site_id" class="form-control">
                    <option value="-1">--网站--</option>
                    <?php
                    foreach ($options_site_name as $siteid => $sitename) {
                        if ($site_id_select == $siteid) {
                            $sel = 'selected="selected"';
                        } else {
                            $sel = '';
                        }
                        echo '<option value="' . $siteid . '" ' . $sel . '>' . $sitename . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>邮箱</label>
                <input class="form-control" type="text" name="customers_email_address" id="customer-email" value="<?php echo I('customers_email_address', '') ?>">
            </div>

            <div class="form-group">
                <label>订单前缀</label>
                <input class="form-control" type="text" name="order_no_prefix" id="order_no_prefix" value="<?php echo I('order_no_prefix', '') ?>">
            </div>      
        </div>
        <div class="form-inline mb5">
            <div class="form-group">
                <label>订单区间</label>
                <input class="form-control" type="text" name="from_orders_id" value="<?php echo I('from_orders_id', '') ?>" placeholder="起始单号">
                <input class="form-control" type="text" name="to_orders_id" value="<?php echo I('to_orders_id', '') ?>" placeholder="结束单号">
            </div>
            <div class="form-group">
                <label>支付方式</label>
                <select name="payment_module_code" class="form-control">
                    <option value="">--付款方式--</option>
                    <?php
                    $pays = array('westernunion', 'mycheckout','tpo', 'security_stripe','stripepay','cp_pay', 'fortune_pay');
                    foreach ($pays as $t) {
                        if ($t == I('payment_module_code')) {
                            $s = 'selected="selected"';
                        } else {
                            $s = '';
                        }
                        echo '<option value="' . $t . '" ' . $s . '>' . $t . '</option>';
                    }
                    ?>

                </select>
            </div>     
        </div>
        <div class="form-inline mb5">
            <label>下单时间</label>
            <input class="form-control" type="text" name="order_time_start" value="<?php echo I('order_time_start', '') ?>" placeholder="起始日期">
            <input class="form-control" type="text" name="order_time_end" value="<?php echo I('order_time_end', '') ?>" placeholder="结束日期">
        </div>    
        <div class="row">
            <div class="col-lg-12 center">
                <button class="btn btn-default" type="submit">导出excel表格</button>
                <p class="text-danger">*按筛选条件从系统中未发货的订单中导出客户的收货地址等信息</p>
                <p class="text-danger">*未发货状态的订单包括：待订货、已订货、待发货以及部分发货等状态的订单</p>
            </div>
        </div>
    </form>

</div>
