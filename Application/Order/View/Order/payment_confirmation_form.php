<h1>付款状态表导入确认</h1>
<?php 
if($has_error){
?>
<p class="bg-danger">导入的数据中存在无效记录，请确定对应的订单是否存在系统中,你可以先下载订单再重新导入。如果问题还是存在，请向技术部门反馈！</p>
<?php
}
?>
<form action="<?php echo U('Order/Order/payment_confirmation/action/confirm')?>" method="post">
<input type="hidden" name="excels" value="<?php echo $excels;?>">
<table class="table table-bordered f08">
    <tr>
        <th>序号</th>
        <th>数据有效性</th>
        <th>网站</th>
        <th>交易流水号(可空)</th>
        <th>订单号</th>
        <th>金额</th>
        <th>货币</th>
        <th>接口</th>
        <th>支付状态</th>
        <th>下单时间</th>
        <th>支付时间</th>
        <th>备注</th>
        <th>当前订单状态</th>
        <th>确认后订单状态</th>
    </tr>
<?php
foreach ($data as $k=>$entry){
    $order_status_remark_checked = $entry['to_order_status_remark'];
?>
    <tr <?php if($entry['payment_status']=='交易成功') echo 'class="bg-success"'?>>
        <td><?php echo $entry['line']?></td>
        <td>
            <?php
            if(isset($entry['error'])){
            ?>
            <span class="glyphicon glyphicon-remove" data-toggle="tooltip" data-placement="top" title="<?php echo $entry['error']?>"></span>
            <input type="hidden" name="error[]" value="1">
            <?php
            }else{
            ?>
            <span class="glyphicon glyphicon-ok"></span>
            <input type="hidden" name="error[]" value="0">
            <?php
            }
            ?>
            
            
        </td>
        <td><?php echo $entry['site_name']?></td>
        <td><input type="hidden" name="site_id[]" value="<?php echo $entry['site_id'] ?>">
            <input type="hidden" name="orders_id[]" value="<?php echo $entry['orders_id'] ?>">
            <?php echo $entry['rp_no'] ?></td>
        <td><?php echo $entry['order_no'] ?></td>
        <td><?php echo round($entry['amount'],2) ?></td>
        <td><?php echo $entry['currency'] ?></td>
        <td><?php echo $entry['payment_code'] ?><input type="hidden" name="payment_code[]" value="<?php echo $entry['payment_code'] ?>"></td>
        <td><?php echo $entry['payment_status'] ?><input type="hidden" name="payment_status[]" value="<?php echo $entry['payment_status'] ?>"></td>
        <td><?php echo $entry['date_purchased'] ?></td>
        <td><?php echo $entry['date_paid'] ?></td>
        <td><?php echo $entry['paid_remark'] ?><input type="hidden" name="date_paid[]" value="<?php echo $entry['date_paid'] ?>"></td>
        <td><?php echo $entry['cur_order_status_remark'] ?></td>
        <td>
            <?php
            if($entry['cur_order_status_remark']!=$entry['to_order_status_remark']){
            ?>
            <tagLib name="html" />
            <html:select options="order_status_remark" name="order_status_remark[]" selected="order_status_remark_checked" style="form-control" />
            <?php
            }else{
            ?>
            <input type="text" class="form-control" name="order_status_remark[]" value="<?php echo $order_status_remark_checked?>" readonly="readonly" />
            <?php
            }
            ?>
        </td>
    </tr>
<?php    
}
?>
</table>
    <button class="btn btn-default" type="submit">确认</button>
</form>