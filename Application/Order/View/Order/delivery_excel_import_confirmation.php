<h1>物流信息表导入确定</h1>
<form action="<?php echo U('Order/Order/delivery_excel_import')?>" method="post">
    <input type="hidden" name="action" value="confirm">
<table class="table table-bordered">
    <tr>
        <th>序号</th>
        <th>完整性</th>
        <th>订单号</th>
        <th>发货日期</th>
        <th>货运方式</th>
        <th>转单号</th>
        <th>货运单号</th>
        <th>重量(Kg)</th>
        <th>订单产品数<br>(不含赠品)</th>
        <th>赠品数量</th>
        <th>其它备注</th>
        <th>转变状态</th>
    </tr>
<?php
$i = 0;
foreach ($delivery_data as $k=>$entry){
    $i++;
    $order_status_remark_checked = $entry['order_status_remark'];
?>
    <tr>
        <td><?php echo $i?></td>
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
        <td><input type="hidden" name="site_id[]" value="<?php echo $entry['site_id'] ?>">
            <input type="hidden" name="orders_id[]" value="<?php echo $entry['orders_id'] ?>">
            <?php echo $entry['order_no'] ?></td>
        <td><?php echo $entry['delivery_date'] ?><input type="hidden" name="delivery_date[]" value="<?php echo $entry['delivery_date'] ?>"></td>
        <td><?php echo $entry['delivery_type'] ?><input type="hidden" name="delivery_type[]" value="<?php echo $entry['delivery_type'] ?>"></td>
        <td><?php echo $entry['delivery_forward_no'] ?><input type="hidden" name="delivery_forward_no[]" value="<?php echo $entry['delivery_forward_no'] ?>"></td>
        <td><?php echo $entry['delivery_tracking_no'] ?><input type="hidden" name="delivery_tracking_no[]" value="<?php echo $entry['delivery_tracking_no'] ?>"></td>
        <td><?php echo round($entry['delivery_weight'], 2) ?><input type="hidden" name="delivery_weight[]" value="<?php echo round($entry['delivery_weight'], 2) ?>"></td>
        <td><?php echo $entry['delivery_quanlity'] ?><input type="hidden" name="delivery_quanlity[]" value="<?php echo $entry['delivery_quanlity'] ?>"></td>
        <td><?php echo $entry['delivery_gift_quanlity'] ?><input type="hidden" name="delivery_gift_quanlity[]" value="<?php echo $entry['delivery_gift_quanlity'] ?>"></td>
        <td><?php echo $entry['delivery_remark'] ?><input type="hidden" name="delivery_remark[]" value="<?php echo $entry['delivery_remark'] ?>"></td>
        <td>
            <tagLib name="html" />
            <html:select options="order_status_remark" name="order_status_remark[]" selected="order_status_remark_checked" style="form-control" />
        </td>
    </tr>
<?php    
}
?>
</table>
    <button class="btn btn-default" type="submit">确认导入</button>
</form>